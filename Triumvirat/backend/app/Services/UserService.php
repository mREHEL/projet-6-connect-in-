<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function updateConnectionStatus(int $userId, bool $isConnected): bool
    {
        return User::where('id', $userId)->update([
            'is_connected' => $isConnected
        ]);
    }
}