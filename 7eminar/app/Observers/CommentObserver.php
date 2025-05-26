<?php

namespace App\Observers;

use App\Jobs\IndexCommentInElasticsearchJob;
use App\Jobs\DeleteCommentFromElasticsearchJob;
use App\Models\Comment;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        IndexCommentInElasticsearchJob::dispatch($comment);
    }

    public function updated(Comment $comment): void
    {
        IndexCommentInElasticsearchJob::dispatch($comment);
    }

    public function deleted(Comment $comment): void
    {
        DeleteCommentFromElasticsearchJob::dispatch($comment->id);
    }

    public function restored(Comment $comment): void
    {
        IndexCommentInElasticsearchJob::dispatch($comment);
    }

    public function forceDeleted(Comment $comment): void
    {
        DeleteCommentFromElasticsearchJob::dispatch($comment->id);
    }
}
