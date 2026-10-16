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
        Schema::create('expense_records', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('expense_type_id')->constrained('expense_types')->cascadeOnDelete();
            $table->string('title');
            $table->date('expense_date');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('invoice_number')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('recipient_or_vendor')->nullable();
            $table->json('invoice_images')->nullable();
            $table->json('item_images')->nullable();
            $table->json('payments')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_records');
    }
};
