const express = require('express');
const bodyParser = require('body-parser');
const { Client, LocalAuth } = require('whatsapp-web.js');
const { Pool } = require('pg');
const QRCode = require('qrcode');

const app = express();
app.use(bodyParser.json());

// PostgreSQL connection setup
const db = new Pool({
    host: 'localhost',
    user: 'nithra',
    password: 'nithra',
    database: 'ownsaas_app',
    port: 5432
});



// Store WhatsApp clients per business
const waClients = {};
// Store QR codes per business
const waQRCodes = {};

// Create WhatsApp session and return QR code
app.post('/api/session/create', async (req, res) => {
    const { business_id, phone_number } = req.body;
    const client = waClients[business_id];
    // Only re-initialize if client is missing or not ready
    if (client && client.info && client.info.wid) {
        return res.json({ status: 'already_active' });
    }
    if (client && !(client.info && client.info.wid)) {
        delete waClients[business_id];
    }
    const newClient = new Client({
        authStrategy: new LocalAuth({ clientId: business_id }),
        puppeteer: { headless: true }
    });
    waClients[business_id] = newClient;

    newClient.on('qr', async qr => {
        // Save session to DB as pending
        await db.query(
            'INSERT INTO whatsapp_sessions (business_id, phone_number, status) VALUES ($1, $2, $3) ON CONFLICT (business_id) DO UPDATE SET phone_number = $2, status = $3',
            [business_id, phone_number, 'pending']
        );
        waQRCodes[business_id] = qr; // Store QR code in memory
        // Don't send response here, let client poll for QR code
    });

    newClient.on('ready', async () => {
        await db.query(
            'UPDATE whatsapp_sessions SET status = $1 WHERE business_id = $2',
            ['active', business_id]
        );
        waQRCodes[business_id] = null; // Clear QR code when ready
    });

    newClient.on('disconnected', async (reason) => {
        console.log(`WhatsApp client for business ${business_id} disconnected: ${reason}`);
        waQRCodes[business_id] = null;
        delete waClients[business_id];
        await db.query(
            'UPDATE whatsapp_sessions SET status = $1 WHERE business_id = $2',
            ['disconnected', business_id]
        );
        // Optionally, auto-restart session
        // setTimeout(() => {
        //     // Re-initialize client if needed
        // }, 5000);
    });

    newClient.on('auth_failure', async (msg) => {
        console.log(`WhatsApp client for business ${business_id} auth failure: ${msg}`);
        waQRCodes[business_id] = null;
        delete waClients[business_id];
        await db.query(
            'UPDATE whatsapp_sessions SET status = $1 WHERE business_id = $2',
            ['auth_failure', business_id]
        );
    });

    newClient.initialize();
    res.json({ status: 'initializing' }); // Inform client to poll for QR code
});

// Endpoint to get QR code status
app.get('/api/session/qr-status', async (req, res) => {
    const { business_id } = req.query;
    const qr = waQRCodes[business_id];
    const client = waClients[business_id];
    if (qr) {
        // Encode QR string as PNG Data URL
        const qrDataUrl = await QRCode.toDataURL(qr);
        res.json({ status: 'pending', qr, qr_image: qrDataUrl });
    } else if (client && client.info && client.info.wid) {
        res.json({ status: 'active' });
    } else if (client) {
        res.json({ status: 'not_ready' });
    } else {
        res.json({ status: 'not_initialized' });
    }
});

// Send WhatsApp message
app.post('/api/message/send', async (req, res) => {
    const { business_id, phone_number, message_text } = req.body;
    const client = waClients[business_id];
    if (!client) return res.status(400).json({ error: 'Session not active' });

    // Check if client is ready
    if (client.info && client.info.wid) {
        try {
            await client.sendMessage(phone_number + '@c.us', message_text);
            // Track message in DB
            await db.query(
                'INSERT INTO messages (business_id, phone_number, message_text, status, created_at) VALUES ($1, $2, $3, $4, NOW())',
                [business_id, phone_number, message_text, 'sent']
            );
            res.json({ status: 'sent' });
        } catch (err) {
            await db.query(
                'INSERT INTO messages (business_id, phone_number, message_text, status, error_message, created_at) VALUES ($1, $2, $3, $4, $5, NOW())',
                [business_id, phone_number, message_text, 'failed', err.message]
            );
            res.status(500).json({ error: err.message });
        }
    } else {
        // Remove client from memory if not ready
        delete waClients[business_id];
        res.status(400).json({ error: 'WhatsApp session closed. Please re-authenticate.' });
    }
});

// Get message status/history
app.get('/api/message/history', async (req, res) => {
    const { business_id } = req.query;
    const { rows } = await db.query(
        'SELECT * FROM messages WHERE business_id = $1 ORDER BY created_at DESC LIMIT 50',
        [business_id]
    );
    res.json(rows);
});

// Health check endpoint
app.get('/api/health', (req, res) => {
    res.json({ status: 'ok' });
});

app.listen(3001, () => {
    console.log('WhatsApp Session Server running on port 3001');
});
