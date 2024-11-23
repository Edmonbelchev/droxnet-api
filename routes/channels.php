<?php

use App\Broadcasting\MessageChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', MessageChannel::class);
