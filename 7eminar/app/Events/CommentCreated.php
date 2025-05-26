<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('posts.' . $this->comment->post_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'message' => $this->comment->message,
            'post_id' => $this->comment->post_id,
            'user_id' => $this->comment->user_id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }
}
