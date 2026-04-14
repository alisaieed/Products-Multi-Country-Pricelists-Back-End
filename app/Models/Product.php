<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\UnitOfMeasure;

class Product extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'category_id',
        'sub_category_id',
        'sku',
        'barcode',
        'name',
        'description',
        'img_url',
        'units_of_measure_id',
        'height',
        'width',
        'length',
        'weight',
        'volume',
        'is_assembly',
        'is_batch_tracked',
        'is_serial_tracked',
        'requires_expiry_control',
        'shelf_life_days',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'name'                  => 'array',
        'description'           => 'array',
        'is_assembly'           => 'boolean',
        'is_batch_tracked'      => 'boolean',
        'is_serial_tracked'     => 'boolean',
        'requires_expiry_control' => 'boolean',
        'is_active'             => 'boolean',
        'height'                => 'decimal:4',
        'width'                 => 'decimal:4',
        'length'                => 'decimal:4',
        'weight'                => 'decimal:4',
        'volume'                => 'decimal:4',
    ];

    // Many-to-many with Country through ProductPrice
    public function countries()
    {
        return $this->belongsToMany(Country::class, 'product_prices')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
    /**
     * Tenant relationship.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Root category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Sub-category relationship.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    /**
     * Unit of measure relationship.
     */
    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'units_of_measure_id');
    }

    /**
     * Scope to filter active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter products by SKU.
     */
    public function scopeBySku($query, string $sku)
    {
        return $query->where('sku', $sku);
    }

    /**
     * Scope to filter products by barcode.
     */
    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }
}
