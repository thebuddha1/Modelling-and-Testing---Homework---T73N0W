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
        if (!Schema::hasTable('user_comment_reports')) {
            Schema::create('user_comment_reports', function (Blueprint $table) {
                $table->id();
                $table->boolean('reported')->default(false);
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'comment_id']);

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_comment_reports');
    }
};
