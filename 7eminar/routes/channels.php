<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('posts.{postId}', function ($user) {
    return (bool)$user;
});
