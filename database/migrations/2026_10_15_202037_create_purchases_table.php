<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->string('invoice_number')->nullable();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->date('purchase_date');
            $table->string('status')->default('received');
            $table->string('payment_status')->default('unpaid');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->json('items')->nullable();
            $table->json('payments')->nullable();
            $table->json('bill_images')->nullable();
            $table->json('product_images')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
