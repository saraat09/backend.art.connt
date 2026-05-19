<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — ArtisanConnect
|--------------------------------------------------------------------------
|
| Chaque utilisateur ne peut s'abonner qu'à son propre canal privé.
| L'authentification JWT est gérée par le middleware auth:api.
|
*/

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
