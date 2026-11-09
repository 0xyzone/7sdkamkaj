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
        Schema::create('label_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Standard Garment Tag 50x30", "Price & Care Label"
            $table->string('category')->default('garment'); // garment, shipping, barcode, custom
            $table->decimal('width_mm', 8, 2)->default(50.00); // in mm
            $table->decimal('height_mm', 8, 2)->default(30.00); // in mm
            $table->decimal('gap_mm', 8, 2)->default(2.00); // in mm
            $table->integer('direction')->default(1); // 0 or 1
            $table->integer('density')->default(8); // 8 dots/mm (203 DPI) for Deli E886BW
            $table->string('command_language')->default('tspl'); // tspl, zpl, escpos
            
            // Visual elements stored as structured JSON array
            // (e.g. [{type: 'text', x: 20, y: 30, text: 'Seven Series', font: '3', size: 1}, {type: 'barcode', ...}])
            $table->json('elements')->nullable();
            
            // Raw template code with placeholders like {{brand}}, {{item_name}}, {{size}}, {{price}}, {{barcode}}
            $table->text('raw_template')->nullable();
            
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
        Schema::dropIfExists('label_templates');
    }
};
