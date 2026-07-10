<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table): void {
            $table->string('slug')->unique()->after('name');
            $table->string('image')->nullable()->after('social_media');
            $table->text('bio')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'image', 'bio']);
        });
    }
};
