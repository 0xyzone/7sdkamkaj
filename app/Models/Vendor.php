<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'address',
        'pan_vat_number',
        'website',
        'contacts',
        'qrs',
        'bank_accounts',
        'notes',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contacts' => 'array',
            'qrs' => 'array',
            'bank_accounts' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Vendor $vendor): void {
            if ($vendor->isDirty('qrs')) {
                $oldQrs = collect((array) ($vendor->getOriginal('qrs') ?? []))->pluck('qr_image')->filter()->all();
                $newQrs = collect((array) ($vendor->qrs ?? []))->pluck('qr_image')->filter()->all();
                $removed = array_diff($oldQrs, $newQrs);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        });

        static::deleted(function (Vendor $vendor): void {
            foreach ((array) ($vendor->qrs ?? []) as $qr) {
                if (! empty($qr['qr_image']) && is_string($qr['qr_image'])) {
                    Storage::disk('public')->delete($qr['qr_image']);
                }
            }
        });
    }

    /**
     * Get the purchase items associated with this vendor.
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
