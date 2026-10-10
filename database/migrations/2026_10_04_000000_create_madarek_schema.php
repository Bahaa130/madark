<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Madarek — full LMS schema (port of prisma/schema.prisma).
 * Laravel-standard plural table names + snake_case columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─────────────── Identity ───────────────
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable()->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('image')->nullable();
            $t->string('password')->nullable();
            $t->enum('role', ['STUDENT', 'INSTRUCTOR', 'ADMIN'])->default('STUDENT');
            $t->text('headline')->nullable();
            $t->text('bio')->nullable();
            $t->string('country')->nullable();
            $t->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });

        Schema::create('accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->string('provider');
            $t->string('provider_account_id');
            $t->text('refresh_token')->nullable();
            $t->text('access_token')->nullable();
            $t->unsignedInteger('expires_at')->nullable();
            $t->string('token_type')->nullable();
            $t->text('scope')->nullable();
            $t->text('id_token')->nullable();
            $t->text('session_state')->nullable();
            $t->unique(['provider', 'provider_account_id']);
        });

        Schema::create('instructor_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->enum('status', ['PENDING', 'VERIFIED', 'REJECTED'])->default('PENDING');
            $t->text('expertise')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->timestamps();
        });

        // ─────────────── Journeys ───────────────
        Schema::create('journeys', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('title_en')->nullable();
            $t->text('tagline')->nullable();
            $t->longText('description')->nullable();
            $t->string('cover_image')->nullable();
            $t->enum('level', ['BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'MASTER'])->default('BEGINNER');
            $t->enum('status', ['DRAFT', 'PENDING_REVIEW', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $t->boolean('is_free')->default(true);
            $t->unsignedInteger('price')->default(0);
            $t->string('language')->default('ar');
            $t->unsignedInteger('duration_min')->default(0);
            $t->timestamps();
            $t->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $t->index(['status', 'level']);
        });

        Schema::create('modules', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->unsignedInteger('order')->default(0);
            $t->foreignId('journey_id')->constrained()->cascadeOnDelete();
            $t->index(['journey_id', 'order']);
        });

        Schema::create('lessons', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('video_url')->nullable();
            $t->unsignedInteger('duration_min')->default(0);
            $t->unsignedInteger('order')->default(0);
            $t->boolean('is_preview')->default(false);
            $t->foreignId('module_id')->constrained()->cascadeOnDelete();
            $t->index(['module_id', 'order']);
        });

        Schema::create('lesson_assets', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('url');
            $t->unsignedInteger('size_kb')->default(0);
            $t->string('mime_type')->nullable();
            $t->foreignId('lesson_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('enrollments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('journey_id')->constrained()->cascadeOnDelete();
            $t->enum('status', ['ACTIVE', 'COMPLETED', 'PAUSED'])->default('ACTIVE');
            $t->timestamp('enrolled_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->unique(['user_id', 'journey_id']);
        });

        Schema::create('lesson_progress', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $t->boolean('completed')->default(false);
            $t->unsignedInteger('watched_seconds')->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->unique(['user_id', 'lesson_id']);
        });

        // ─────────────── Assessment ───────────────
        Schema::create('quizzes', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('description')->nullable();
            $t->unsignedInteger('passing_score')->default(60);
            $t->foreignId('journey_id')->nullable()->constrained()->cascadeOnDelete();
            $t->index('journey_id');
        });

        Schema::create('questions', function (Blueprint $t) {
            $t->id();
            $t->text('text');
            $t->unsignedInteger('order')->default(0);
            $t->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $t->index(['quiz_id', 'order']);
        });

        Schema::create('question_options', function (Blueprint $t) {
            $t->id();
            $t->text('text');
            $t->boolean('is_correct')->default(false);
            $t->unsignedInteger('order')->default(0);
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->index(['question_id', 'order']);
        });

        Schema::create('quiz_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('score')->default(0);
            $t->boolean('passed')->default(false);
            $t->longText('answers')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'quiz_id']);
        });

        // ─────────────── Achievements & Certificates ───────────────
        Schema::create('achievements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('icon')->nullable();
            $t->timestamp('earned_at')->nullable();
            $t->index(['user_id', 'earned_at']);
        });

        Schema::create('certificates', function (Blueprint $t) {
            $t->id();
            $t->string('serial')->unique();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('journey_id')->constrained()->cascadeOnDelete();
            $t->timestamp('issued_at')->nullable();
            $t->unique(['user_id', 'journey_id']);
        });

        // ─────────────── Library ───────────────
        Schema::create('articles', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->text('excerpt')->nullable();
            $t->longText('content')->nullable();
            $t->string('cover_url')->nullable();
            $t->enum('type', ['ARTICLE', 'BOOK', 'SUMMARY'])->default('ARTICLE');
            $t->string('language')->default('ar');
            $t->unsignedInteger('read_min')->default(5);
            $t->boolean('published')->default(false);
            $t->timestamps();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->index(['type', 'published']);
        });

        // ─────────────── Forum ───────────────
        Schema::create('forum_threads', function (Blueprint $t) {
            $t->id();
            $t->text('title');
            $t->boolean('pinned')->default(false);
            $t->boolean('locked')->default(false);
            $t->timestamps();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('journey_id')->nullable()->constrained('journeys')->nullOnDelete();
            $t->index(['pinned', 'created_at']);
        });

        Schema::create('forum_posts', function (Blueprint $t) {
            $t->id();
            $t->text('content');
            $t->timestamps();
            $t->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->index(['thread_id', 'created_at']);
        });

        // ─────────────── Notifications ───────────────
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['SYSTEM', 'JOURNEY', 'ACHIEVEMENT', 'FORUM', 'CERTIFICATE'])->default('SYSTEM');
            $t->string('title');
            $t->text('body')->nullable();
            $t->string('url')->nullable();
            $t->boolean('read')->default(false);
            $t->timestamp('created_at')->nullable();
            $t->index(['user_id', 'read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('lesson_assets');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('journeys');
        Schema::dropIfExists('instructor_profiles');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};