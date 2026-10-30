<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->index(['is_featured', 'is_active']);
        });

        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->unsignedBigInteger('min_order_minor')
                ->default(0)
                ->after('delivery_fee_minor')
                ->comment('Minimum order subtotal in PKR minor units before this zone applies.');
            $table->text('coverage_notes')
                ->nullable()
                ->after('min_order_minor')
                ->comment('Owner-supplied coverage description; never invent content.');
        });

        Schema::table('business_hours', function (Blueprint $table) {
            $table->unique('day_of_week');
        });
    }

    public function down(): void
    {
        Schema::table('business_hours', function (Blueprint $table) {
            $table->dropUnique(['day_of_week']);
        });

        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['min_order_minor', 'coverage_notes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'is_active']);
            $table->dropColumn('is_featured');
        });
    }
};
