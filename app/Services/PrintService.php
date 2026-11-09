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
        if (preg_match_all('/printer\s+([^\s]+)\s+/i', $output, $matches)) {
            foreach ($matches[1] as $queueName) {
                $printers[$queueName] = $queueName;
            }
        }

        return $printers;
    }

    /**
     * Get active print jobs across all or a specific printer queue.
     * Returns an array of job records with id, printer, user, size, date, status.
     */
    public static function getPrintQueue(?string $printerQueue = null): array
    {
        $cmd = 'lpstat -o ' . ($printerQueue ? escapeshellarg($printerQueue) : '') . ' 2>&1';
        $output = @shell_exec($cmd);

        if (! $output || str_contains(strtolower($output), 'no system default')) {
            return [];
        }

        $jobs = [];
        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_contains($line, 'No destinations added')) {
                continue;
            }

            // Standard CUPS output format:
            // Deli_886BW-58           oxyzone           1024   Mon Nov  9 13:40:27 2026
            if (preg_match('/^([^\s]+)\s+([^\s]+)\s+([0-9]+)\s+(.+)$/', $line, $matches)) {
                $jobId = $matches[1];
                $user = $matches[2];
                $bytes = (int) $matches[3];
                $time = $matches[4];

                $parts = explode('-', $jobId);
                $printerName = $parts[0] ?? 'Unknown';

                $sizeFormatted = $bytes >= 1024 * 1024
                    ? number_format($bytes / (1024 * 1024), 1) . ' MB'
                    : number_format($bytes / 1024, 1) . ' KB';

                $jobs[] = [
                    'id' => $jobId,
                    'printer' => $printerName,
                    'user' => $user,
                    'bytes' => $bytes,
                    'size' => $sizeFormatted,
                    'created_at' => $time,
                ];
            }
        }

        return $jobs;
    }

    /**
     * Cancel a specific print job or all jobs on a printer queue.
     */
    public static function cancelPrintJob(?string $jobId = null, bool $all = false): array
    {
        if ($all) {
            $cmd = 'cancel -a 2>&1';
            $output = @shell_exec($cmd);

            return [
                'success' => true,
                'message' => 'All pending print jobs have been terminated.',
                'output' => trim($output ?? ''),
            ];
        }

        if (! $jobId) {
            return [
                'success' => false,
                'message' => 'No job ID specified to cancel.',
            ];
        }

        $cmd = 'cancel ' . escapeshellarg($jobId) . ' 2>&1';
        $output = @shell_exec($cmd);

        if ($output && str_contains(strtolower($output), 'error')) {
            return [
                'success' => false,
                'message' => "Failed to cancel job {$jobId}: " . trim($output),
                'output' => trim($output),
            ];
        }

        return [
            'success' => true,
            'message' => "Job {$jobId} was successfully cancelled.",
            'output' => trim($output ?? ''),
        ];
    }

    /**
     * Convert an image binary string (or data URI) into 1-bit monochrome TSPL BITMAP command.
     * This provides WYSIWYG printing for any graphics, custom fonts, logos, icons, and text drawn on the canvas.
     */
    public static function convertImageToTspl(
        string $imageData,
        float $widthMm,
        float $heightMm,
        float $gapMm = 2.0,
        int $direction = 1,
        int $copies = 1,
        int $densityDotsPerMm = 8 // 203 DPI = 8 dots/mm
    ): string {
        // Strip data URI scheme if present
        if (str_starts_with($imageData, 'data:image')) {
            $parts = explode(',', $imageData);
            $imageData = base64_decode($parts[1] ?? '');
        }

        $srcImage = @imagecreatefromstring($imageData);
        if (! $srcImage) {
            throw new \InvalidArgumentException('Invalid image data provided for TSPL conversion.');
        }

        $targetWidthDots = (int) round($widthMm * $densityDotsPerMm);
        $targetHeightDots = (int) round($heightMm * $densityDotsPerMm);
        $widthBytes = (int) ceil($targetWidthDots / 8);

        // Resample/render onto exact target canvas dimensions with pure white background
        $canvas = imagecreatetruecolor($targetWidthDots, $targetHeightDots);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetWidthDots, $targetHeightDots, $white);

        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);
        imagecopyresampled($canvas, $srcImage, 0, 0, 0, 0, $targetWidthDots, $targetHeightDots, $srcW, $srcH);
        imagedestroy($srcImage);

        // Convert to 1-bit monochrome bitmap stream (TSPL BITMAP mode 0: 0 = black pixel, 1 = white pixel)
        $bitmapBytes = '';
        for ($y = 0; $y < $targetHeightDots; $y++) {
            for ($xByte = 0; $xByte < $widthBytes; $xByte++) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $x = $xByte * 8 + $bit;
                    if ($x < $targetWidthDots) {
                        $rgb = imagecolorat($canvas, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        // Luminance formula
                        $gray = ($r * 299 + $g * 587 + $b * 114) / 1000;
                        if ($gray > 140) { // White threshold
                            $byte |= (1 << (7 - $bit));
                        }
                    } else {
                        $byte |= (1 << (7 - $bit));
                    }
                }
                $bitmapBytes .= chr($byte);
            }
        }
        imagedestroy($canvas);

        $header = sprintf("SIZE %s mm, %s mm\n", number_format($widthMm, 1, '.', ''), number_format($heightMm, 1, '.', ''));
        $header .= sprintf("GAP %s mm, 0 mm\n", number_format($gapMm, 1, '.', ''));
        $header .= sprintf("DIRECTION %d\n", $direction);
        $header .= "CLS\n";
        $bitmapCmd = sprintf("BITMAP 0,0,%d,%d,0,", $widthBytes, $targetHeightDots) . $bitmapBytes . "\n";
        $footer = sprintf("PRINT %d\n", max(1, $copies));

        return $header . $bitmapCmd . $footer;
    }

    /**
     * Send raw data (TSPL/ZPL/ESC-POS or binary bitmap stream) to a specific Printer model instance.
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
