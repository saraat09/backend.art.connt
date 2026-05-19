<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ══════════════════════════════════════
         *  TABLE: users
         *  Artisans ET clients partagent cette table
         *  role = 'artisan' | 'client'
         * ══════════════════════════════════════ */
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['artisan', 'client'])->default('client');
            $table->string('phone', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('photo')->nullable();          // chemin storage/photos/xxx.jpg
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        /* ══════════════════════════════════════
         *  TABLE: artisan_profiles
         *  Infos métier (1-to-1 avec users où role=artisan)
         * ══════════════════════════════════════ */
        Schema::create('artisan_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');       // supprime le profil si user supprimé
            $table->string('trade', 100);      // métier : Électricien, Plombier…
            $table->text('description')->nullable();
            $table->string('location', 150);   // ex: "Casablanca, Maarif"
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('available')->default(true);
            $table->decimal('rating', 3, 1)->default(0.0);  // ex: 4.8
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamps();

            $table->unique('user_id');   // 1 profil par artisan max
        });

        /* ══════════════════════════════════════
         *  TABLE: artisan_services
         *  Services proposés par un artisan (1-to-many)
         * ══════════════════════════════════════ */
        Schema::create('artisan_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_profile_id')
                  ->constrained('artisan_profiles')
                  ->onDelete('cascade');
            $table->string('service_name', 100);
            $table->decimal('price_from', 10, 2)->nullable();  // prix de départ en MAD
            $table->timestamps();
        });

        /* ══════════════════════════════════════
         *  TABLE: messages
         *  Conversations entre artisan ↔ client
         * ══════════════════════════════════════ */
       Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
    $table->text('body');
    $table->boolean('read')->default(false);
    $table->timestamps();

    $table->index(['sender_id', 'receiver_id']);
    $table->index(['receiver_id', 'read']);
});

        /* ══════════════════════════════════════
         *  TABLE: notifications
         *  Notifications in-app pour artisans et clients
         * ══════════════════════════════════════ */
      Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('type', 60);
    $table->json('data');
    $table->boolean('read')->default(false);
    $table->timestamps();

    $table->index(['user_id', 'read']);
});

        /* ══════════════════════════════════════
         *  TABLE: password_reset_tokens
         * ══════════════════════════════════════ */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('artisan_services');
        Schema::dropIfExists('artisan_profiles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
