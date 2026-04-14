<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

            // Unique tenant key (short identifier, used in configs / provisioning)
            $table->string('key')->unique();

            // Human-friendly tenant name
            $table->string('name');

            // Optional JSON metadata (settings, billing ids, etc)
            $table->json('metadata')->nullable();

            // Operational toggle
            $table->boolean('is_active')->default(true);

            // Use project helper for timestamps / audit columns (keeps consistency with other migrations)
            if (function_exists('appTimestamps')) {
                appTimestamps($table, true, true);
            } else {
                $table->timestamps();
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
