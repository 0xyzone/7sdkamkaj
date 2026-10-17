<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SaleRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'bill_number',
        'distributor_id',
        'sale_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'status',
        'bill_images',
        'item_images',
        'payments',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'bill_images' => 'array',
            'item_images' => 'array',
            'payments' => 'array',
        ];
    }

    /**
     * The distributor associated with this sale record.
     */
    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    protected static function booted(): void
    {
        static::creating(function (SaleRecord $sale) {
            if (blank($sale->sale_number)) {
                $prefix = 'SALE-'.now()->format('Ym').'-';
                $latest = static::where('sale_number', 'like', "{$prefix}%")
                    ->latest('id')
                    ->first();
                $nextSeq = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->sale_number, $matches)) {
                    $nextSeq = ((int) $matches[1]) + 1;
                }
                $sale->sale_number = $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (SaleRecord $sale) {
            // Auto calculate paid amount from payments if present
            if (is_array($sale->payments)) {
                $calcPaid = 0;
                foreach ($sale->payments as $payment) {
                    $calcPaid += (float) ($payment['amount'] ?? 0);
                }
                $sale->paid_amount = round($calcPaid, 2);
            } else {
                $sale->paid_amount = 0;
            }

            // Calculate due amount
            $total = (float) ($sale->total_amount ?? 0);
            $paid = (float) ($sale->paid_amount ?? 0);
            $sale->due_amount = max(0, round($total - $paid, 2));

            // Auto determine payment status
            if ($total > 0 && $paid >= $total) {
                $sale->payment_status = 'paid';
            } elseif ($paid > 0) {
                $sale->payment_status = 'partial';
            } else {
                $sale->payment_status = 'unpaid';
            }
        });

        static::updating(function (SaleRecord $sale): void {
            if ($sale->isDirty('bill_images')) {
                $old = (array) ($sale->getOriginal('bill_images') ?? []);
                $new = (array) ($sale->bill_images ?? []);
                $removed = array_diff($old, $new);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }

            if ($sale->isDirty('item_images')) {
                $old = (array) ($sale->getOriginal('item_images') ?? []);
                $new = (array) ($sale->item_images ?? []);
                $removed = array_diff($old, $new);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }

            if ($sale->isDirty('payments')) {
                $oldReceipts = collect((array) ($sale->getOriginal('payments') ?? []))->pluck('receipt_image')->filter()->all();
                $newReceipts = collect((array) ($sale->payments ?? []))->pluck('receipt_image')->filter()->all();
                $removedReceipts = array_diff($oldReceipts, $newReceipts);
                foreach ($removedReceipts as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        });

        static::deleted(function (SaleRecord $sale): void {
            foreach ((array) ($sale->bill_images ?? []) as $file) {
                if (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            foreach ((array) ($sale->item_images ?? []) as $file) {
                if (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            foreach ((array) ($sale->payments ?? []) as $payment) {
                if (! empty($payment['receipt_image']) && is_string($payment['receipt_image'])) {
                    Storage::disk('public')->delete($payment['receipt_image']);
                }
            }
        });
    }
}
