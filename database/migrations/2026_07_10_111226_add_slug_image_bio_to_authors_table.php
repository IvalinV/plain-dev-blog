<?php

use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('name');
            $table->string('image')->nullable()->after('social_media');
            $table->text('bio')->nullable()->after('image');
        });

        Author::query()
            ->where(function (Builder $query): void {
                $query->whereNull('slug')->orWhere('slug', '');
            })
            ->get()
            ->each
            ->save();

        Schema::table('authors', function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
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
