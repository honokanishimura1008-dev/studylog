<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mussle_logs', function (Blueprint $table) {
            $table->decimal('weight_kg', 5, 1)->nullable()->after('mussle_date');
            $table->unsignedSmallInteger('reps')->nullable()->after('weight_kg');
            $table->unsignedSmallInteger('sets')->nullable()->after('reps');
            $table->string('memo', 1000)->nullable()->after('sets');
            $table->unsignedInteger('minutes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mussle_logs', function (Blueprint $table) {
            $table->dropColumn(['weight_kg', 'reps', 'sets', 'memo']);
            $table->unsignedInteger('minutes')->nullable(false)->change();
        });
    }
};
