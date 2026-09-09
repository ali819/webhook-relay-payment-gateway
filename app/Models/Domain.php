<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    /** Provider yang didukung relay. */
    public const PROVIDERS = ['midtrans', 'xendit', 'doku'];

    protected $fillable = [
        'name', 'domain', 'provider', 'target_url', 'secret_key', 'is_active', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }
}
