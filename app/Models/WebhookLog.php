<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'domain_id', 'provider', 'event_type', 'custom_field1',
        'payload', 'response_code', 'duration_ms', 'status', 'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
