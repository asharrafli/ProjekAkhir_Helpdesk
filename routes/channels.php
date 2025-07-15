<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    Log::info('Broadcasting Auth Check', [
        'user' => $user ? $user->id : 'null',
        'channel_id' => $id,
        'authorized' => $user && (int) $user->id === (int) $id
    ]);
    return $user && (int) $user->id === (int) $id;
});

// Allow authenticated users to access the tickets channel
Broadcast::channel('tickets', function ($user) {
    return $user !== null;
});

// User-specific private channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

//Chanel untuk admin/manager
Broadcast::channel('admin-notifications', function ($user) {
    return $user->hasAnyRole(['admin','manager','super-admin']);
});

// Channel untuk notifications global
Broadcast::channel('notifications.global', function ($user) {
    return $user !== null;
});

// Channel untuk notifications per user
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    Log::info('🔒 Private notification channel auth check', [
        'user_id' => $user ? $user->id : 'null',
        'channel_user_id' => $userId,
        'authorized' => $user && (int) $user->id === (int) $userId
    ]);
    return $user && (int) $user->id === (int) $userId;
});
