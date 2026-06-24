<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ハイブリッド入力対応:
            // マスタ選択時は food_id、自由記述/クイック記録時は food_name_free を使う。
            // どちらか一方は必須（アプリ側バリデーションで担保）。
            $table->foreignId('food_id')->nullable()->constrained('foods')->nullOnDelete();
            $table->string('food_name_free', 100)->nullable();

            $table->string('meal_type', 16); // morning / lunch / dinner / snack
            $table->date('eaten_on');
            $table->unsignedSmallInteger('amount_g')->nullable();

            // 栄養値はマスタ選択時は自動算出、自由記述時は手入力、未入力(NULL)は「未計上」扱い
            $table->unsignedSmallInteger('kcal')->nullable();
            $table->decimal('protein', 5, 1)->nullable();
            $table->decimal('fat', 5, 1)->nullable();
            $table->decimal('carbs', 5, 1)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'eaten_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_logs');
    }
};
