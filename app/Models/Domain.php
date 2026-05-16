<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'name', 'domain', 'provider', 'target_url', 'secret_key', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }
}
