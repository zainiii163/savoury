<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('from_status', [
                'pending',
                'accepted',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
                'collected',
                'cancelled',
                'rejected',
            ])->nullable();
            $table->enum('to_status', [
                'pending',
                'accepted',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
                'collected',
                'cancelled',
                'rejected',
            ]);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at']);
            $table->index(['to_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
