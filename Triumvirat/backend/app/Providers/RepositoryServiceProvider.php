<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PostService;
use App\Services\LikeService;
use App\Services\CommentService;
use App\Services\UserService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // On lie les classes au conteneur de services de Laravel
        $this->app->singleton(PostService::class, fn() => new PostService());
        $this->app->singleton(LikeService::class, fn() => new LikeService());
        $this->app->singleton(CommentService::class, fn() => new CommentService());
        $this->app->singleton(UserService::class, fn() => new UserService());
    }
}