<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'plan',
    ];

    // Add this relationship
    public function businesses()
    {
        return $this->hasMany(\App\Models\Business::class, 'client_id');
    }
}