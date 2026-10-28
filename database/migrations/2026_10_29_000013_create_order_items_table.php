<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 150);
            $table->string('variant_name', 100)->nullable();
            $table->unsignedBigInteger('unit_price_minor')
                ->comment('Base product/variant unit price snapshot in PKR minor units.');
            $table->unsignedBigInteger('addons_total_minor')
                ->default(0)
                ->comment('Selected addons total per unit in PKR minor units.');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total_minor')
                ->comment('(unit_price_minor + addons_total_minor) * quantity.');
            $table->json('addons_snapshot')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
