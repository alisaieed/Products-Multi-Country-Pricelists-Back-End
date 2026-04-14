<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();

            // Tenant boundary
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');

            // Short code for location (e.g. A1, BIN-001)
            $table->string('code')->nullable();

            // Human friendly name
            $table->string('name');

            // Optional JSON description/metadata
            $table->json('description')->nullable();

            // Optional location type (rack, bin, floor, zone)
            $table->string('type')->nullable();

            // Operational toggle
            $table->boolean('is_active')->default(true);

            if (function_exists('appTimestamps')) {
                appTimestamps($table, true, true);
            } else {
                $table->timestamps();
                $table->softDeletes();
            }

            // Uniqueness and indexes
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_locations');
    }
};
