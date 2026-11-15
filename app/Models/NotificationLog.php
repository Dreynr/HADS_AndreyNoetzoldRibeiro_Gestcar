<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Uma tentativa de avisar o cliente, por um canal, sobre um fato (B-08).
 *
 * A linha e gravada tanto no sucesso quanto na falha: o que interessa e saber
 * que a tentativa existiu e o que ela respondeu.
 */
class NotificationLog extends Model
{
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    /**
     * A tabela so tem `created_at`: um aviso enviado nao e atualizado depois,
     * entao `updated_at` seria uma coluna sempre igual a outra.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'appointment_id',
        'user_id',
        'channel',
        'type',
        'status',
        'error',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
