<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'cups_queue_name',
        'ip_address',
        'port',
        'protocol',
        'is_default',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'port' => 'integer',
    ];

    /**
     * Check current connection status dynamically.
     * Returns array with [status => 'online'|'idle'|'busy'|'offline', 'message' => string, 'color' => 'success'|'warning'|'danger']
     */
    public function getStatus(): array
    {
        if (! $this->is_active) {
            return [
                'status' => 'inactive',
                'message' => 'Disabled in system',
                'color' => 'gray',
            ];
        }

        if ($this->type === 'cups') {
            if (! $this->cups_queue_name) {
                return [
                    'status' => 'error',
                    'message' => 'No CUPS queue configured',
                    'color' => 'danger',
                ];
            }

            // Execute lpstat to check the printer status
            $output = @shell_exec("lpstat -p " . escapeshellarg($this->cups_queue_name) . " 2>&1");
            
            if ($output === null || str_contains($output, 'unknown') || str_contains($output, 'not exist')) {
                return [
                    'status' => 'offline',
                    'message' => 'Printer not found in CUPS',
                    'color' => 'danger',
                ];
            }

            if (str_contains($output, 'is idle')) {
                return [
                    'status' => 'idle',
                    'message' => 'Connected & Ready (Idle)',
                    'color' => 'success',
                ];
            }

            if (str_contains($output, 'printing') || str_contains($output, 'processing')) {
                return [
                    'status' => 'busy',
                    'message' => 'Printing / Processing',
                    'color' => 'warning',
                ];
            }

            if (str_contains($output, 'disabled') || str_contains($output, 'offline')) {
                return [
                    'status' => 'offline',
                    'message' => 'Offline / Disabled',
                    'color' => 'danger',
                ];
            }

            return [
                'status' => 'connected',
                'message' => trim($output),
                'color' => 'info',
            ];
        }

        if ($this->type === 'network') {
            if (! $this->ip_address) {
                return [
                    'status' => 'error',
                    'message' => 'IP Address missing',
                    'color' => 'danger',
                ];
            }

            $connection = @fsockopen($this->ip_address, $this->port ?: 9100, $errno, $errstr, 1.5);
            if (is_resource($connection)) {
                fclose($connection);
                return [
                    'status' => 'online',
                    'message' => 'Network Port Open (Ready)',
                    'color' => 'success',
                ];
            }

            return [
                'status' => 'offline',
                'message' => 'Unreachable (' . ($errstr ?: 'Connection refused/timed out') . ')',
                'color' => 'danger',
            ];
        }

        return [
            'status' => 'unknown',
            'message' => 'Unknown printer type',
            'color' => 'gray',
        ];
    }
}
