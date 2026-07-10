<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    use HasUniqueSlug;

    protected $fillable = [
        'name',
        'email',
        'slug',
        'social_media',
        'image',
        'bio',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected function sluggableSourceColumn(): string
    {
        return 'name';
    }
}
