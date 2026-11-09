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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Deli E886BW - Factory Floor"
            $table->string('type')->default('cups'); // cups, network (tcp/raw socket)
            $table->string('cups_queue_name')->nullable(); // e.g. Deli_886BW
            $table->string('ip_address')->nullable(); // e.g. 192.168.1.150
            $table->integer('port')->default(9100);
            $table->string('protocol')->default('tspl'); // tspl, zpl, escpos
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
