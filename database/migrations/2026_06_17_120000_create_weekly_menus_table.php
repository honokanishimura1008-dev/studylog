<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('dow', 3); // mon / tue / wed / thu / fri / sat / sun
            $table->unsignedSmallInteger('sort_order');
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sets');
            $table->unsignedSmallInteger('rep_min');
            $table->unsignedSmallInteger('rep_max');
            $table->string('memo', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dow', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_menus');
    }
};
