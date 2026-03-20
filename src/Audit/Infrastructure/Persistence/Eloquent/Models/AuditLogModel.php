<?php

declare(strict_types=1);

namespace Api\Audit\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AuditLog Model (Eloquent)
 * 
 * Modelo de persistencia para registros de auditoría.
 */
class AuditLogModel extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false; // Usamos solo created_at personalizado

    protected $fillable = [
        'user_id',
        'service',
        'method',
        'request_body',
        'response_code',
        'response_body',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            // Como ahora son LONGTEXT, Laravel los maneja como JSON automáticamente
            'request_body' => 'array',
            'response_body' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
