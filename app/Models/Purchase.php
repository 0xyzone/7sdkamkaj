<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'invoice_number',
        'vendor_id',
        'purchase_date',
        'status',
        'payment_status',
        'total_amount',
        'paid_amount',
        'due_amount',
        'items',
        'payments',
        'bill_images',
        'product_images',
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
            'purchase_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'items' => 'array',
            'payments' => 'array',
            'bill_images' => 'array',
            'product_images' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Purchase $purchase) {
            if (blank($purchase->purchase_number)) {
                $prefix = 'PO-'.now()->format('Ym').'-';
                $latest = static::where('purchase_number', 'like', "{$prefix}%")
                    ->latest('id')
                    ->first();
                $nextSeq = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->purchase_number, $matches)) {
                    $nextSeq = ((int) $matches[1]) + 1;
                }
                $purchase->purchase_number = $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (Purchase $purchase) {
            // Auto calculate total from line items if present
            if (is_array($purchase->items)) {
                $calcTotal = 0;
                foreach ($purchase->items as $item) {
                    $qty = (float) ($item['quantity'] ?? 0);
                    $price = (float) ($item['unit_price'] ?? 0);
                    $calcTotal += ($qty * $price);
                }
                if ($calcTotal > 0 || blank($purchase->total_amount)) {
                    $purchase->total_amount = round($calcTotal, 2);
                }
            }

            // Auto calculate paid amount from payments if present
            if (is_array($purchase->payments)) {
                $calcPaid = 0;
                foreach ($purchase->payments as $payment) {
                    $calcPaid += (float) ($payment['amount'] ?? 0);
                }
                $purchase->paid_amount = round($calcPaid, 2);
            }

            // Calculate due amount
            $purchase->due_amount = max(0, round((float) $purchase->total_amount - (float) $purchase->paid_amount, 2));

            // Auto determine payment status
            if ((float) $purchase->total_amount > 0 && (float) $purchase->paid_amount >= (float) $purchase->total_amount) {
                $purchase->payment_status = 'paid';
            } elseif ((float) $purchase->paid_amount > 0) {
                $purchase->payment_status = 'partial';
            } else {
                $purchase->payment_status = 'unpaid';
            }
        });

        static::updating(function (Purchase $purchase): void {
            if ($purchase->isDirty('bill_images')) {
                $old = (array) ($purchase->getOriginal('bill_images') ?? []);
                $new = (array) ($purchase->bill_images ?? []);
                $removed = array_diff($old, $new);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }

            if ($purchase->isDirty('product_images')) {
                $old = (array) ($purchase->getOriginal('product_images') ?? []);
                $new = (array) ($purchase->product_images ?? []);
                $removed = array_diff($old, $new);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }

            if ($purchase->isDirty('payments')) {
                $oldReceipts = collect((array) ($purchase->getOriginal('payments') ?? []))->pluck('receipt_image')->filter()->all();
                $newReceipts = collect((array) ($purchase->payments ?? []))->pluck('receipt_image')->filter()->all();
                $removed = array_diff($oldReceipts, $newReceipts);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        });

        static::deleted(function (Purchase $purchase): void {
            foreach ((array) ($purchase->bill_images ?? []) as $file) {
                if (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
            foreach ((array) ($purchase->product_images ?? []) as $file) {
                if (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
            foreach ((array) ($purchase->payments ?? []) as $payment) {
                if (! empty($payment['receipt_image'])) {
                    Storage::disk('public')->delete($payment['receipt_image']);
                }
            }
        });
    }

    /**
     * Get the vendor associated with this purchase.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
