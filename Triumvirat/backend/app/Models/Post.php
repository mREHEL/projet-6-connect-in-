<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = ['user_id', 'content'];

    // Récupère auteur post (meme si supprimé)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)
                    ->select(['id', 'username', 'profile_photo_path', 'first_name', 'last_name', 'deleted_at']) // N'oubliez pas 'deleted_at'
                    ->withTrashed(); 
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }
}