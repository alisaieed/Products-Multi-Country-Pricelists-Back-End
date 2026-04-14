<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouse_locations';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class, 'warehouse_location_id');
    }
}
