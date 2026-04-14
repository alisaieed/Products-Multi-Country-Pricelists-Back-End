<?php

use App\Enums\SerialNumberStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Helper to add application-standard timestamp/blameable columns inside migrations.
 *
 * Signature kept intentionally simple to match existing calls:
 *   appTimestamps(Blueprint $table, bool $withSoftDeletes = false, bool $withBlameable = false)
 *
 * - $withSoftDeletes: when true, adds softDeletes() column.
 * - $withBlameable: when true, adds nullable created_by/updated_by bigint columns.
 */
if (! function_exists('appTimestamps')) {
    function appTimestamps(Blueprint $table, bool $withSoftDeletes = false, bool $withBlameable = false): void
    {
        $table->timestamps();
        if ($withSoftDeletes) {
            $table->softDeletes();
        }
        if ($withBlameable) {
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        }
    }
}

return new class extends Migration
{
    /**
     * Product Catalog & Traceability (Core)
     * Table: categories, units_of_measures, products, product_units, batches, serial_numbers
     * Phase: 1 (Core master data) + Phase 2 readiness (serial/approvals/metadata extensions)
     * ============================
     *
     * What this migration provides (human-readable):
     * - Category tree (Category → Sub-Category) used as mandatory taxonomy for every SKU. [file:30]
     * - SKU master data including barcode, base unit, and traceability flags (batch/serial/expiry). [file:30]
     * - Product-specific UOM conversions (e.g., 1 CTN = 12 EA) + optional pack-level dimensions. [file:30]
     * - Lot/Batch master (production/expiry dates) and serial registry (per-item traceability). [file:30]
     *
     * SRS Coverage (IDs + Phase):
     * - FR-PC-001 Categories/Sub-categories hierarchy (Phase 1). [file:30]
     * - FR-PC-002 Multi-UOM per product with conversion factors (Phase 1). [file:30]
     * - FR-PC-003 Batch tracking with production/expiry dates (Phase 1). [file:30]
     * - FR-PC-004 Serial tracking for individual item traceability (Phase 2). [file:30]
     * - FR-PC-005 Shelf life + FEFO readiness (Phase 1). [file:30]
     * - FR-PC-010 Mandatory taxonomy Category → Sub-Category → SKU (Phase 1). [file:30]
     * - FR-PC-011/012 Barcode policy + tenant-level uniqueness (Phase 1). [file:30]
     * - FR-PC-013 Metadata-driven product attributes (Phase 2) (extension required; see notes). [file:30]
     * - FR-PC-014 Category management CRUD + uniqueness + audit trail intent (Phase 1). [file:30]
     * - FR-PC-015 UOM management CRUD + conversion factors + restrictions when in-use intent (Phase 1). [file:30]
     * - FR-PC-016 Tenant-configurable validation rules with versioning/publish/rollback (Phase 2) (extension required). [file:30]
     * - FR-PC-017 Conditional validation constraints (Phase 2) (extension required). [file:30]
     *
     * Global rules enforced / expected here:
     * - Multi-tenancy: every row belongs to a tenant_id (Phase 1). [file:30]
     * - Uniqueness rules are typically per tenant (e.g., unique SKU + barcode per tenant). [file:30]
     * - Soft deletes are recommended/used for master data to preserve history and avoid breaking references. [file:30]
     * - Inventory is transaction-based (no silent edits); product/lot/serial tables are referenced by inventory snapshots
     *   and inventory transactions for chain-of-custody. [file:30]
     *
     * --------------------------------------------------------------------
     * FR-PC coverage note (what is NOT fully implemented by this migration)
     *
     * The tables in this migration implement the core Product Catalog data model:
     * Categories, Products, UOMs, Product UOM conversions, Batches, and Serial registry. [file:30]
     * However, the following FR-PC requirements require additional tables and/or application-layer workflows beyond schema:
     *
     * - FR-PC-006 (Phase 1): Multiple controlled input methods (manual UI, barcode scanning, bulk import) require APIs,
     *   mobile scan flows, and bulk-import job pipelines; schema alone cannot deliver it. [file:30]
     *
     * - FR-PC-007 (Phase 1/2): Configurable validation rules enforced consistently across UI and bulk import require a
     *   versioned configuration mechanism (rule sets, publish/rollback, deterministic error reports). [file:30]
     *
     * - FR-PC-016 (Phase 2): Tenant-configurable validation rule sets with versioning, publish/rollback, and dry-run/test
     *   mode require dedicated tables (e.g., validation_rule_sets, validation_rules, tenant_config_versions) and enforcement
     *   logic in write paths and imports. [file:30]
     *
     * - FR-PC-017 (Phase 2): Conditional constraints (e.g., lot/expiry required only for flagged SKUs) require either
     *   (a) rule-engine-backed validation packs or (b) governed attribute schemas plus validation logic. [file:30]
     *
     * - FR-PC-008 (Phase 1): “Full audit trail” for product master changes requires a dedicated audit/event log subsystem
     *   (e.g., audit_logs) and instrumentation for every write operation; timestamps/soft-deletes are not sufficient. [file:30]
     *
     * - FR-PC-009 (Phase 2): Master-data approval mechanisms require approval workflow data structures (workflow definition,
     *   approval decisions/history) and integration into product/category mutation endpoints. [file:30]
     *
     * - FR-PC-011/012 (Phase 1): Barcode policy includes primary + secondary barcodes with tenant-level uniqueness and
     *   collision handling; enforcing only `products.barcode` uniqueness does not fully cover “secondary barcodes”.
     *   Typical extension: product_barcodes table with unique (tenant_id, barcode). [file:30]
     *
     * - FR-PC-013 (Phase 2): Metadata-driven product attributes require an attribute/value model (e.g., product_attributes
     *   + product_attribute_values) or a governed JSON attributes approach with validation/indexing rules. [file:30]
     *
     * - FR-PC-004 (Phase 2): Creating a serial registry satisfies “serial tracking”, but end-to-end traceability (chain of
     *   custody) typically depends on Inventory tables linking product/batch/serial/location and Inventory Transactions as
     *   Source of Truth for movements. [file:30]
     */
    public function up(): void
    {
        /**
         * Table: categories
         * Purpose:
         * - Stores a tenant-scoped taxonomy tree.
         * - Enforces the rule "SKU must belong to Category + Sub-Category" by allowing nesting. [file:30]
         *
         * Phase:
         * - Phase 1 (Core master data). [file:30]
         */
        Schema::create('categories', function (Blueprint $table) {
            /**
             * id
             * Surrogate primary key for internal relations.
             */
            $table->id();

            /**
             * tenant_id
             * Every category belongs to exactly one tenant (strict isolation).
             * Deleting a tenant deletes its categories (safe because tenant is the root boundary). [file:30]
             */
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');

            /**
             * parent_id
             * Self-referencing hierarchy:
             * - NULL => root category
             * - value => this row is a sub-category of parent_id
             *
             * Deleting a parent deletes children to avoid orphan sub-categories. [file:30]
             */
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');

            /**
             * name (JSON)
             * Localized display name.
             * Example: {"en":"Beverages","ar":"مشروبات"}.
             */
            $table->json('name');

            /**
             * description (JSON, nullable)
             * Optional localized description for UI/help/import templates.
             */
            $table->json('description')->nullable();

            /**
             * code
             * Human/admin/import-friendly unique code within a tenant.
             * Example: "BEV" or "CAT-001".
             *
             * Important:
             * - MUST NOT be nullable if we rely on unique(tenant_id, code) for integrity,
             *   because multiple NULL values can bypass uniqueness in MySQL.
             */
            $table->string('code')->index();

            /**
             * is_active
             * Operational toggle:
             * - false => SKU hidden/blocked for new operations (but history stays).
             */
            $table->boolean('is_active')->default(true);

            /**
             * Audit columns (your helper)
             * Expected to include created_at/updated_at and optionally created_by/updated_by. [file:30]
             */
            appTimestamps($table, true, true);

            /**
             * Uniqueness:
             * Prevent duplicate category codes inside same tenant.
             */
            $table->unique(['tenant_id', 'code']);

            /**
             * Indexing:
             * Optimizes tree queries (load children, filter by tenant).
             */
            $table->index(['tenant_id', 'parent_id']);
        });

        /**
         * Table: units_of_measures
         * Purpose:
         * - A tenant-scoped UOM dictionary (EA, KG, L, CTN, PLT...).
         * - Conversions are NOT stored here; they are stored per product in product_units. [file:30]
         *
         * Phase:
         * - Phase 1 (Core master data). [file:30]
         */
        Schema::create('units_of_measures', function (Blueprint $table) {
            $table->id();

            /**
             * tenant_id
             * Tenant boundary for the UOM dictionary.
             */
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');

            /**
             * abbreviation
             * Short unique identifier per tenant.
             * Examples: "EA", "KG", "L", "CTN".
             *
             * Used for:
             * - UI dropdowns
             * - Import templates
             * - Document printing/labels
             */
            $table->string('abbreviation');

            /**
             * name (JSON)
             * Localized UOM name.
             * Example: {"en":"Each","ar":"حبة"}
             */
            $table->json('name');

            /**
             * dimension_type (nullable)
             * Optional classification to prevent invalid conversions at business-layer:
             * Examples: "count", "weight", "volume", "length", "pack".
             *
             * Note:
             * - Not enforced as enum now because it may expand per tenant rules later (Phase 2 configs). [file:30]
             */
            $table->string('dimension_type')->nullable();

            /**
             * is_active
             * Operational toggle:
             * - false => SKU hidden/blocked for new operations (but history stays).
             */
            $table->boolean('is_active')->default(true);

            appTimestamps($table, true, true);


            /**
             * Uniqueness:
             * Abbreviation must be unique per tenant (EA can't repeat).
             */
            $table->unique(['tenant_id', 'abbreviation']);
            $table->index(['tenant_id']);
        });

        /**
         * Table: products
         * Purpose:
         * - SKU master data (catalog).
         * - Holds traceability flags (batch/serial/expiry) to drive flows such as FEFO. [file:30]
         *
         * Phase:
         * - Phase 1: SKU master, UOM base, barcode, batch/expiry readiness flags. [file:30]
         * - Phase 2: serial tracking flows rely on is_serial_tracked. [file:30]
         */
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            /**
             * tenant_id
             * Tenant-scoped SKU namespace.
             */
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');

            /**
             * category_id
             * Root category reference for reporting/filtering.
             *
             * Restrict delete:
             * - Prevent deleting a category that is already used by SKUs (integrity).
             */
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');

            /**
             * sub_category_id
             * Mandatory second-level taxonomy.
             * Same categories table; we keep a separate column to enforce the "Category + Sub-Category" rule. [file:30]
             */
            $table->foreignId('sub_category_id')->constrained('categories')->onDelete('restrict');

            /**
             * sku
             * Tenant-unique SKU code.
             * Examples: "SKU-10001", "MILK-1L".
             */
            $table->string('sku')->index();

            /**
             * barcode (nullable)
             * Primary barcode for scan-based flows.
             * - Must be unique per tenant when provided (policy requirement). [file:30]
             * - Nullable because not all SKUs have a barcode at creation time.
             */
            $table->string('barcode')->nullable()->index();

            /**
             * name (JSON)
             * Localized product name for UI and documents.
             */
            $table->json('name');

            /**
             * description (JSON, nullable)
             * Optional localized description.
             */
            $table->json('description')->nullable();

            /**
             * img_url (nullable)
             * Optional image URL for UI.
             */
            $table->string('img_url', 1000)->nullable();

            /**
             * units_of_measure_id
             * The product “stocking/base unit”.
             * Examples:
             * - If base is EA: inventory is counted in pieces.
             * - If base is KG: inventory is measured in kilograms.
             *
             * Restrict delete:
             * - Prevent deleting a UOM if products still reference it.
             */
            $table->foreignId('units_of_measure_id')
                ->constrained('units_of_measures')
                ->onDelete('restrict');

            /**
             * Base dimensions (nullable)
             * Dimensions/weight/volume per 1 base unit (units_of_measure_id).
             * Used later for:
             * - Slotting decisions
             * - Packing/cartonization
             * - Capacity planning
             */
            $table->decimal('height', 10, 4)->nullable(); // per base unit
            $table->decimal('width', 10, 4)->nullable();  // per base unit
            $table->decimal('length', 10, 4)->nullable(); // per base unit
            $table->decimal('weight', 10, 4)->nullable(); // per base unit
            $table->decimal('volume', 10, 4)->nullable(); // per base unit

            /**
             * is_assembly
             * True if product is a kit/BOM/assembly (future phases).
             * Phase 2/3 functionality typically uses this to expand components.
             */
            $table->boolean('is_assembly')->default(false);

            /**
             * is_batch_tracked
             * Enables lot/batch tracking for this SKU.
             * If true, inbound/receiving flows should capture batch_number and optionally production/expiry. [file:30]
             */
            $table->boolean('is_batch_tracked')->default(false);

            /**
             * is_serial_tracked
             * Enables per-item serial number tracking for this SKU (Phase 2). [file:30]
             */
            $table->boolean('is_serial_tracked')->default(false);

            /**
             * requires_expiry_control
             * Enables expiry-date rules (FEFO readiness).
             * If true, inbound should capture expiry_date and outbound allocation should follow FEFO policy. [file:30]
             */
            $table->boolean('requires_expiry_control')->default(false);

            /**
             * shelf_life_days (nullable)
             * Optional helper value:
             * - If provided, system can compute expiry_date = production_date + shelf_life_days (where policy allows).
             * - Also supports validations like "expiry must not exceed shelf life".
             */
            $table->integer('shelf_life_days')->nullable();

            /**
             * is_active
             * Operational toggle:
             * - false => SKU hidden/blocked for new operations (but history stays).
             */
            $table->boolean('is_active')->default(true);

            appTimestamps($table, true, true);


            /**
             * Uniqueness:
             * - SKU must be unique per tenant.
             * - Barcode must be unique per tenant when provided. [file:30]
             */
            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'barcode']);

            /**
             * Indexing:
             * Common filters: tenant + category/subcategory.
             */
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'sub_category_id']);
        });

        /**
         * Table: product_units
         * Purpose:
         * - Defines the allowed UOMs for a product and how they convert to base_uom.
         * - Stores pack-level dimensions (carton/pallet differs from each).
         *
         * Phase:
         * - Phase 1 (Multi-UOM). [file:30]
         */
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();

            /**
             * tenant_id
             * Tenant boundary for conversions.
             */
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');

            /**
             * product_id
             * Which SKU this unit belongs to.
             */
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            /**
             * units_of_measure_id
             * Which unit is being defined for this SKU (EA, CTN, PLT...).
             * Restrict delete to prevent removing UOM that is referenced in conversions.
             */
            $table->foreignId('units_of_measure_id')
                ->constrained('units_of_measures')
                ->onDelete('restrict');

            /**
             * conversion_factor:
             * Quantity of product.base_uom contained in 1 unit of units_of_measure_id.
             *
             * Examples:
             * - base=EA, uom=CTN => conversion_factor=12  (1 CTN = 12 EA)
             * - base=KG, uom=BAG => conversion_factor=25  (1 BAG = 25 KG)
             *
             * Notes:
             * - Must be > 0 (enforce in validation layer; DB-level check optional in MySQL 8 via CHECK).
             * - Use high precision to avoid rounding issues in inventory computations.
             *
             * SRS: FR-PC-002 (Phase 1). [file:30]
             */
            $table->decimal('conversion_factor', 15, 6)->default(1.000000);

            /**
             * Pack-level dimensions (nullable)
             * Same meaning as product dimensions, but for this UOM level.
             * Example:
             * - EA dimensions represent single item.
             * - CTN dimensions represent a full carton.
             * - PLT dimensions represent a pallet.
             */
            $table->decimal('height', 10, 4)->nullable();
            $table->decimal('width', 10, 4)->nullable();
            $table->decimal('length', 10, 4)->nullable();
            $table->decimal('weight', 10, 4)->nullable();
            $table->decimal('volume', 10, 4)->nullable();

            appTimestamps($table, true, true);


            /**
             * Uniqueness:
             * A SKU cannot have duplicate definitions for the same UOM.
             */
            $table->unique(['tenant_id', 'product_id', 'units_of_measure_id']);
            $table->index(['tenant_id', 'product_id']);
        });

        /**
         * Table: batches
         * Purpose:
         * - Master record for lot/batch tracking.
         * - Supports production_date and expiry_date (FEFO readiness). [file:30]
         *
         * Phase:
         * - Phase 1. [file:30]
         */
        Schema::create('batches', function (Blueprint $table) {
            $table->id();

            /**
             * tenant_id
             * Tenant boundary for lot records.
             */
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');

            /**
             * product_id
             * Which SKU the batch belongs to.
             */
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            /**
             * batch_number
             * Supplier/manufacturer lot number.
             * Examples: "LOT-2026-0001", "BATCH-A1".
             */
            $table->string('batch_number');

            /**
             * production_date (nullable)
             * Manufacturing/production date.
             * Nullable because sometimes not provided.
             */
            $table->date('production_date')->nullable();

            /**
             * expiry_date (nullable)
             * Expiry date for the lot (required when SKU requires_expiry_control=true by policy). [file:30]
             */
            $table->date('expiry_date')->nullable();

            appTimestamps($table, true, true);


            /**
             * Uniqueness:
             * Same product cannot have duplicate batch numbers within same tenant.
             */
            $table->unique(['tenant_id', 'product_id', 'batch_number']);

            /**
             * Indexing:
             * - Lookup batches per product.
             * - FEFO queries often sort/filter by expiry_date.
             */
            $table->index(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'expiry_date']);
        });

        /**
         * Table: serial_numbers
         * Purpose:
         * - Registry of serial numbers for serial-tracked SKUs.
         * - Used for per-item chain-of-custody and traceability (Phase 2 capability). [file:30]
         *
         * Phase:
         * - Phase 2 for operational usage, table can exist earlier. [file:30][file:31]
         */
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();

            /**
             * tenant_id
             * Tenant boundary for serial registry.
             */
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');

            /**
             * product_id
             * Serial numbers are SKU-specific (same serial value could exist for different SKUs in theory,
             * but we enforce uniqueness per tenant + product as safer rule).
             */
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            /**
             * batch_id (nullable)
             * Optional link to batch/lot if this serial is associated with a specific lot.
             */
            $table->foreignId('batch_id')
                ->nullable()
                ->constrained('batches')
                ->onDelete('cascade');

            /**
             * serial_number
             * The serial value as scanned/recorded.
             * Indexed for fast lookup during picking/receiving/returns.
             */
            $table->string('serial_number')->index();

            /**
             * warehouse_location_id (nullable)
             * Optional snapshot of current location.
             * - If null: unknown/not yet received/in transit/etc (depending on your workflows).
             * - If set: last known warehouse location holding this serial.
             *
             * Note: some designs derive this from inventory transactions instead of storing it here.
             */
            $table->foreignId('warehouse_location_id')
                ->nullable()
                ->constrained('warehouse_locations')
                ->onDelete('set null');

            /**
             * status (enum)
             * Operational state of the serial number.
             *
             * Allowed values (SerialNumberStatus enum):
             * - available  => in stock and usable
             * - allocated  => reserved/allocated to an order/task
             * - sold       => shipped/consumed (no longer available)
             * - scrapped   => destroyed/waste/quality failure
             */
            $table->enum('status', array_map(fn($c) => $c->value, SerialNumberStatus::cases()))
                ->default(SerialNumberStatus::available->value);

            appTimestamps($table, true, true);


            /**
             * Uniqueness:
             * Same serial cannot repeat for the same product inside the same tenant.
             */
            $table->unique(['tenant_id', 'product_id', 'serial_number']);
            $table->index(['tenant_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units_of_measures');
        Schema::dropIfExists('categories');
    }

    /**
     * products
     * product_options (id, product_id) (Size, Color, )
     * product_option_items (id, option_id)
     * product_skus (id, product_id, sku, price, options)
     * product_sku_options (id, sku_id, option_id)
     *
     *
     * Ex: Phone
     * - Size: S, M, L, XL
     * - Color: Black, White, Red, Blue
     *
     * - SKU1: PH01, 50SSAR
     *   - SKU Option: Size:S, Color: Black
     * - SKU2: PH02, 50SSAW
     *   - SKU Option: Size:S, Color: White
     * - SKU3: PH03, 50SSAR
     *   - SKU Option: Size:M, Color: Red
     * - SKU4: PH04, 50SSAW
     *   - SKU Option: Size:M, Color: Blue
     * - SKU5: PH05, 50SSAR
     *   - SKU Option: Size:L, Color: Black
     * - SKU6: PH06, 50SSAW
     *   - SKU Option: Size:L, Color: White
     * - SKU7: PH07, 50SSAR
     *   - SKU Option: Size:XL, Color: Red
     * - SKU8: PH08, 50SSAW
     *   - SKU Option: Size:XL, Color: Blue
     */
};
