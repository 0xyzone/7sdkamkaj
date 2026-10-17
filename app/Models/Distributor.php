<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'pan_vat_number',
        'address',
        'website',
        'contacts',
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
            'bank_accounts' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all sales recorded for this distributor.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(SaleRecord::class);
    }
}
