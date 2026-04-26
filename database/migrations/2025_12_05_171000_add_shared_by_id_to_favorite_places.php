<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorite_places', function (Blueprint $table) {
            $table->unsignedBigInteger('shared_by_id')->nullable()->after('user_id');
            $table->foreign('shared_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('favorite_places', function (Blueprint $table) {
            $table->dropForeign(['shared_by_id']);
            $table->dropColumn('shared_by_id');
        });
    }
};
