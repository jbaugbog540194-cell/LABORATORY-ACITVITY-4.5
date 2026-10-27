<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
