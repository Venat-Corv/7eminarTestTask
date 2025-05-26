<?php

namespace App\Jobs;

use App\Models\Comment;
use Elastic\Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexCommentInElasticsearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Comment $comment) {}

    public function handle(Client $client): void
    {
        $this->comment->loadMissing(['user', 'post']);

        $client->index([
            'index' => 'comments',
            'id' => $this->comment->id,
            'body' => [
                'message' => $this->comment->message,
                'rating' => $this->comment->rating,
                'status' => $this->comment->status,
                'user_id' => $this->comment->user_id,
                'user_name' => $this->comment->user->name ?? null,
                'post_id' => $this->comment->post_id,
                'post_title' => $this->comment->post->title ?? null,
                'created_at' => $this->comment->created_at->toIso8601String(),
            ]
        ]);
    }
}
