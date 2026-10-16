<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExpenseRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'expense_type_id',
        'title',
        'expense_date',
        'amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'invoice_number',
        'quantity',
        'unit',
        'recipient_or_vendor',
        'invoice_images',
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
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'quantity' => 'decimal:2',
            'invoice_images' => 'array',
            'item_images' => 'array',
            'payments' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ExpenseRecord $expense) {
            if (blank($expense->expense_number)) {
                $prefix = 'EXP-'.now()->format('Ym').'-';
                $latest = static::where('expense_number', 'like', "{$prefix}%")
                    ->latest('id')
                    ->first();
                $nextSeq = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->expense_number, $matches)) {
                    $nextSeq = ((int) $matches[1]) + 1;
                }
                $expense->expense_number = $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (ExpenseRecord $expense) {
            // Auto calculate paid amount from payments if present
            if (is_array($expense->payments)) {
                $calcPaid = 0;
                foreach ($expense->payments as $payment) {
                    $calcPaid += (float) ($payment['amount'] ?? 0);
                }
                $expense->paid_amount = round($calcPaid, 2);
            } else {
                $expense->paid_amount = 0;
            }

            // Calculate due amount
            $total = (float) ($expense->amount ?? 0);
            $paid = (float) ($expense->paid_amount ?? 0);
            $expense->due_amount = max(0, round($total - $paid, 2));

            // Auto determine payment status
            if ($total > 0 && $paid >= $total) {
                $expense->payment_status = 'paid';
            } elseif ($paid > 0) {
                $expense->payment_status = 'partial';
            } else {
                $expense->payment_status = 'unpaid';
            }
        });

        static::updating(function (ExpenseRecord $expense): void {
            if ($expense->isDirty('invoice_images')) {
                $old = (array) ($expense->getOriginal('invoice_images') ?? []);
                $new = (array) ($expense->invoice_images ?? []);
                $removed = array_diff($old, $new);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }

            if ($expense->isDirty('item_images')) {
                $old = (array) ($expense->getOriginal('item_images') ?? []);
                $new = (array) ($expense->item_images ?? []);
                $removed = array_diff($old, $new);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }

            if ($expense->isDirty('payments')) {
                $oldReceipts = collect((array) ($expense->getOriginal('payments') ?? []))->pluck('receipt_image')->filter()->all();
                $newReceipts = collect((array) ($expense->payments ?? []))->pluck('receipt_image')->filter()->all();
                $removed = array_diff($oldReceipts, $newReceipts);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        });

        static::deleted(function (ExpenseRecord $expense): void {
            foreach ((array) ($expense->invoice_images ?? []) as $file) {
                if (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
            foreach ((array) ($expense->item_images ?? []) as $file) {
                if (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
            foreach ((array) ($expense->payments ?? []) as $payment) {
                if (! empty($payment['receipt_image'])) {
                    Storage::disk('public')->delete($payment['receipt_image']);
                }
            }
        });
    }

    /**
     * Get the expense type associated with this expense record.
     */
    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
