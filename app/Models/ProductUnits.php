<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;

class ProductUnit extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'product_units';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'product_id',
        'units_of_measure_id',
        'conversion_factor',
        'height',
        'width',
        'length',
        'weight',
        'volume',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'conversion_factor' => 'decimal:6',
        'height'            => 'decimal:4',
        'width'             => 'decimal:4',
        'length'            => 'decimal:4',
        'weight'            => 'decimal:4',
        'volume'            => 'decimal:4',
    ];

    /**
     * Tenant relationship.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Product relationship.
     * Each product unit belongs to a specific SKU.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Unit of measure relationship.
     * Defines which UOM this product unit represents.
     */
    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'units_of_measure_id');
    }

    /**
     * Scope to filter by product.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by unit of measure.
     */
    public function scopeForUnit($query, int $uomId)
    {
        return $query->where('units_of_measure_id', $uomId);
    }
}
