<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    /** Provider yang didukung relay. */
    public const PROVIDERS = ['midtrans', 'xendit', 'doku'];

    /**
     * Karakter alias: huruf besar & angka, tanpa yang mudah tertukar
     * (0/O, 1/I/L) karena alias ini disalin manusia ke kode aplikasi.
     */
    public const ALIAS_CHARS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public const ALIAS_LENGTH = 3;

    protected $fillable = [
        'name', 'domain', 'alias', 'provider', 'target_url', 'secret_key', 'is_active', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Alias acak yang belum terpakai. Diulang sampai dapat yang unik —
     * ruangnya 31^3 (29.791) jadi praktis tidak pernah berputar lama.
     */
    public static function generateAlias(): string
    {
        do {
            $alias = '';

            for ($i = 0; $i < self::ALIAS_LENGTH; $i++) {
                $alias .= self::ALIAS_CHARS[random_int(0, strlen(self::ALIAS_CHARS) - 1)];
            }
        } while (static::where('alias', $alias)->exists());

        return $alias;
    }
}
