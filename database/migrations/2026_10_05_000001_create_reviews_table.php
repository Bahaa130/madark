<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Udemy-grade upgrades:
 *  - reviews: star ratings + comments on journeys
 *  - lesson_assets: local storage path + extension for uploads
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('journey_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('rating'); // 1..5
            $t->text('comment')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'journey_id']);
        });

        Schema::table('lesson_assets', function (Blueprint $t) {
            $t->string('path')->nullable()->after('url');
            $t->string('extension', 16)->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Schema::table('lesson_assets', function (Blueprint $t) {
            $t->dropColumn(['path', 'extension']);
        });
    }
};
