<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'width_mm',
        'height_mm',
        'gap_mm',
        'direction',
        'density',
        'command_language',
        'elements',
        'raw_template',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'width_mm' => 'decimal:2',
        'height_mm' => 'decimal:2',
        'gap_mm' => 'decimal:2',
        'direction' => 'integer',
        'density' => 'integer',
        'elements' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Compile TSPL commands from visual elements or fallback to raw template.
     */
    public function compileTspl(array $data = [], int $copies = 1): string
    {
        $output = [];
        $output[] = sprintf("SIZE %s mm, %s mm", $this->width_mm, $this->height_mm);
        $output[] = sprintf("GAP %s mm, 0 mm", $this->gap_mm ?? 2);
        $output[] = sprintf("DIRECTION %d", $this->direction ?? 1);
        $output[] = "CLS";

        if (! empty($this->elements) && is_array($this->elements)) {
            foreach ($this->elements as $element) {
                $type = $element['type'] ?? 'text';
                $x = (int) ($element['x'] ?? 10);
                $y = (int) ($element['y'] ?? 10);
                
                if ($type === 'text') {
                    $font = $element['font'] ?? '3'; // standard TSPL font
                    $xMul = (int) ($element['x_mul'] ?? 1);
                    $yMul = (int) ($element['y_mul'] ?? 1);
                    $text = $element['content'] ?? '';
                    // Replace variables like {{item_name}}, {{size}}, {{price}}, {{brand}}
                    foreach ($data as $k => $v) {
                        $text = str_replace('{{' . $k . '}}', (string) $v, $text);
                    }
                    $output[] = sprintf('TEXT %d,%d,"%s",0,%d,%d,"%s"', $x, $y, $font, $xMul, $yMul, addcslashes($text, '"'));
                } elseif ($type === 'barcode') {
                    $code = $element['content'] ?? '12345678';
                    foreach ($data as $k => $v) {
                        $code = str_replace('{{' . $k . '}}', (string) $v, $code);
                    }
                    $codeType = $element['barcode_type'] ?? '128';
                    $height = (int) ($element['height'] ?? 50);
                    $readable = (int) ($element['readable'] ?? 1); // 0 = no text, 1 = text below
                    $narrow = (int) ($element['narrow'] ?? 2);
                    $output[] = sprintf('BARCODE %d,%d,"%s",%d,%d,0,%d,%d,"%s"', $x, $y, $codeType, $height, $readable, $narrow, $narrow * 2, $code);
                } elseif ($type === 'qrcode') {
                    $qr = $element['content'] ?? '';
                    foreach ($data as $k => $v) {
                        $qr = str_replace('{{' . $k . '}}', (string) $v, $qr);
                    }
                    $cellWidth = (int) ($element['cell_width'] ?? 4);
                    $output[] = sprintf('QRCODE %d,%d,L,%d,A,0,"%s"', $x, $y, $cellWidth, addcslashes($qr, '"'));
                } elseif ($type === 'line' || $type === 'bar') {
                    $w = (int) ($element['width'] ?? 200);
                    $h = (int) ($element['height'] ?? 2);
                    $output[] = sprintf('BAR %d,%d,%d,%d', $x, $y, $w, $h);
                } elseif ($type === 'box') {
                    $xEnd = (int) ($element['x_end'] ?? ($x + 100));
                    $yEnd = (int) ($element['y_end'] ?? ($y + 50));
                    $thickness = (int) ($element['thickness'] ?? 2);
                    $output[] = sprintf('BOX %d,%d,%d,%d,%d', $x, $y, $xEnd, $yEnd, $thickness);
                }
            }
        } elseif (! empty($this->raw_template)) {
            $raw = $this->raw_template;
            foreach ($data as $k => $v) {
                $raw = str_replace('{{' . $k . '}}', (string) $v, $raw);
            }
            return trim($raw) . "\nPRINT " . max(1, $copies) . "\n";
        }

        $output[] = sprintf("PRINT %d", max(1, $copies));
        return implode("\n", $output) . "\n";
    }
}
