const express = require('express');
const bodyParser = require('body-parser');
const { Client, LocalAuth } = require('whatsapp-web.js');
const { Pool } = require('pg');

const app = express();
app.use(bodyParser.json());

// PostgreSQL connection setup
const db = new Pool({
    host: 'localhost',
    user: 'postgres',
    password: 'your_db_password',
    database: 'whatsapp_saas',
    port: 5432
});

// Store WhatsApp clients per business
const waClients = {};

// Create WhatsApp session and return QR code
app.post('/api/session/create', async (req, res) => {
    const { business_id, phone_number } = req.body;
    if (waClients[business_id]) {
        return res.json({ status: 'already_active' });
    }
    const client = new Client({
        authStrategy: new LocalAuth({ clientId: business_id }),
        puppeteer: { headless: true }
    });
    waClients[business_id] = client;

    client.on('qr', async qr => {
        // Save session to DB as pending
        await db.query(
            'INSERT INTO whatsapp_sessions (business_id, phone_number, status) VALUES ($1, $2, $3) ON CONFLICT (business_id) DO UPDATE SET phone_number = $2, status = $3',
            [business_id, phone_number, 'pending']
        );
        res.json({ qr });
    });

    client.on('ready', async () => {
        await db.query(
            'UPDATE whatsapp_sessions SET status = $1 WHERE business_id = $2',
            ['active', business_id]
        );
    });

    client.initialize();
});

// Send WhatsApp message
app.post('/api/message/send', async (req, res) => {
    const { business_id, phone_number, message_text } = req.body;
    const client = waClients[business_id];
    if (!client) return res.status(400).json({ error: 'Session not active' });

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

app.listen(3001, () => {
    console.log('WhatsApp Session Server running on port 3001');
});
