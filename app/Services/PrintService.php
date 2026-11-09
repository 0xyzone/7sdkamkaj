<?php

namespace App\Services;

use App\Models\Printer;

class PrintService
{
    /**
     * Get list of locally available CUPS printers on the server/Mac.
     */
    public static function getAvailableCupsPrinters(): array
    {
        $output = @shell_exec('lpstat -p 2>&1');
        if (! $output) {
            return [];
        }

        $printers = [];
        // Matches: "printer Deli_886BW is idle..."
        if (preg_match_all('/printer\s+([^\s]+)\s+/i', $output, $matches)) {
            foreach ($matches[1] as $queueName) {
                $printers[$queueName] = $queueName;
            }
        }

        return $printers;
    }

    /**
     * Send raw data (TSPL/ZPL/ESC-POS) to a specific Printer model instance.
     */
    public static function sendRawPrint(Printer $printer, string $rawCommands): array
    {
        if (! $printer->is_active) {
            return [
                'success' => false,
                'message' => "Printer '{$printer->name}' is marked as inactive.",
            ];
        }

        if ($printer->type === 'cups') {
            if (! $printer->cups_queue_name) {
                return [
                    'success' => false,
                    'message' => "No CUPS queue name specified for printer '{$printer->name}'.",
                ];
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'label_print_');
            file_put_contents($tempFile, $rawCommands);

            // Execute raw lp command
            $cmd = sprintf(
                'lp -d %s -o raw %s 2>&1',
                escapeshellarg($printer->cups_queue_name),
                escapeshellarg($tempFile)
            );

            $output = shell_exec($cmd);
            @unlink($tempFile);

            if ($output && (str_contains($output, 'request id is') || ! str_contains(strtolower($output), 'error'))) {
                return [
                    'success' => true,
                    'message' => "Print job dispatched successfully to {$printer->cups_queue_name} (" . trim($output) . ")",
                    'output' => trim($output),
                ];
            }

            return [
                'success' => false,
                'message' => "Failed sending print to {$printer->cups_queue_name}: " . trim($output ?? 'Unknown error'),
                'output' => trim($output ?? ''),
            ];
        }

        if ($printer->type === 'network') {
            if (! $printer->ip_address) {
                return [
                    'success' => false,
                    'message' => "No IP address configured for network printer '{$printer->name}'.",
                ];
            }

            $fp = @fsockopen($printer->ip_address, $printer->port ?: 9100, $errno, $errstr, 3);
            if (! $fp) {
                return [
                    'success' => false,
                    'message' => "Could not connect to {$printer->ip_address}:{$printer->port} - {$errstr} ({$errno})",
                ];
            }

            fwrite($fp, $rawCommands);
            fclose($fp);

            return [
                'success' => true,
                'message' => "Print data sent directly to {$printer->ip_address}:{$printer->port}",
            ];
        }

        return [
            'success' => false,
            'message' => "Unsupported printer connection type '{$printer->type}'.",
        ];
    }
}
