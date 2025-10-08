<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'business_name',
        'phone_number',
        'business_type',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
