<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'unit_of_measurement',
        'cost_price',
        'vendor_id',
        'description',
        'images',
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
            'images' => 'array',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (PurchaseItem $item): void {
            if ($item->isDirty('images')) {
                $old = (array) ($item->getOriginal('images') ?? []);
                $new = (array) ($item->images ?? []);
                $removed = array_diff($old, $new);
                foreach ($removed as $file) {
                    if (is_string($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        });

        static::deleted(function (PurchaseItem $item): void {
            foreach ((array) ($item->images ?? []) as $file) {
                if (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
        });
    }

    /**
     * Get the preferred vendor for this item.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
