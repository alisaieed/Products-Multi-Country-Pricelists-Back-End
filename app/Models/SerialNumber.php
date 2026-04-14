<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;

class SerialNumber extends Model
{
    protected $table = 'serial_numbers';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'batch_id',
        'serial_number',
        'warehouse_location_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
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
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Batch relationship (optional).
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Warehouse location relationship (optional).
     */
    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    /**
     * Scope: filter available serials.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: filter allocated serials.
     */
    public function scopeAllocated($query)
    {
        return $query->where('status', 'allocated');
    }
}
