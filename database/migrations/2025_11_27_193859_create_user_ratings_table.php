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
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rating_user_id');
            $table->unsignedBigInteger('rated_user_id');
            $table->decimal('precision', 3, 1)->default(0.0);
            $table->decimal('driving', 3, 1)->default(0.0);
            $table->decimal('social', 3, 1)->default(0.0);
            $table->timestamps();
            $table->unique(['rating_user_id', 'rated_user_id']);
            $table->foreign('rating_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rated_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ratings');
    }
};
