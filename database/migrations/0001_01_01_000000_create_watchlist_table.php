<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist', function (Blueprint $table) {
            $table->id();
            $table->integer('movie_id');
            $table->integer('user_id')->default(1);
            $table->string('title');
            $table->string('poster_path')->nullable()->default('');
            $table->decimal('vote_average', 3, 1)->default(0.0);
            $table->string('release_date', 50)->nullable()->default('');
            $table->timestamp('added_date')->useCurrent();
            $table->unique(['user_id', 'movie_id']);
            $table->index('user_id');
            $table->index('added_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist');
    }
};
