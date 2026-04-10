<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;

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

Broadcast::channel('admin.{id}', function ($user, $id) {
    return $user instanceof AdminUser && (int) $user->id === (int) $id;
}, ['guards' => ['admin', 'web']]);

Broadcast::channel('App.Models.AdminUser.{id}', function ($user, $id) {
    return $user instanceof AdminUser && (int) $user->id === (int) $id;
}, ['guards' => ['admin', 'web']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user instanceof \App\Models\User && (int) $user->id === (int) $id;
}, ['guards' => ['web']]);
