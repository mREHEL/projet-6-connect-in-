<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute; 

class User extends Authenticatable
{
    // Ajouter SoftDeletes ici
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; 

    protected $fillable = [
        'email',
        'password',
        'username',
        'first_name',
        'last_name',
        'bio',
        'profile_photo_path',
        'cover_image_path',
        'last_seen_at'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_seen_at' => 'datetime'
    ];

    // Accesseur pour remplacer nom d'utilisateur si soft-deleted
    protected function username(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trashed() ? 'Utilisateur supprimé' : $value,
        );
    }
    
    // Accesseur pour vider le prénom si soft-deleted
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trashed() ? '' : $value,
        );
    }

    // Accesseur pour vider le nom de famille si soft-deleted
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trashed() ? '' : $value,
        );
    }

    // Accesseur pour retirer la photo de profil si soft-deleted
    protected function profilePhotoPath(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->trashed() ? null : $value,
        );
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
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