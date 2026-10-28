<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('public_reference', 40)->unique();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 120);
            $table->string('phone', 20)->index();
            $table->enum('fulfillment_type', ['delivery', 'pickup'])->index();
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_zone_name', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('subtotal_minor')
                ->comment('Order subtotal in PKR minor units (paisa).');
            $table->unsignedBigInteger('delivery_fee_minor')
                ->default(0)
                ->comment('Delivery fee snapshot in PKR minor units (paisa).');
            $table->unsignedBigInteger('total_minor')
                ->comment('subtotal_minor + delivery_fee_minor in PKR minor units.');
            $table->enum('status', [
                'pending',
                'accepted',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
                'collected',
                'cancelled',
                'rejected',
            ])->default('pending')->index();
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->index();
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'placed_at']);
            $table->index(['payment_status', 'placed_at']);
            $table->index(['fulfillment_type', 'placed_at']);
            $table->index('placed_at');
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
