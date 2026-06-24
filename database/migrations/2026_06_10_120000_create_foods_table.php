<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('emoji', 8);
            $table->unsignedSmallInteger('kcal');
            $table->decimal('protein', 5, 1);
            $table->decimal('fat', 5, 1);
            $table->decimal('carbs', 5, 1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
