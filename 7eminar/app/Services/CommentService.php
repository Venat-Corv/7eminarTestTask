<?php

namespace App\Services;

use App\Events\CommentCreated;
use App\Interfaces\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected readonly CommentRepositoryInterface $commentRepository) {}

    public function getByPost(Post $post): array
    {
        return $this->commentRepository->getByPostId($post->id);

    }

    public function show(Comment $comment): Comment
    {
        return $this->commentRepository->find($comment->id);
    }

    public function update(Comment $comment, array $data): bool
    {
        return $this->commentRepository->update($data, $comment->id);
    }

    public function updateStatus(Comment $comment, array $data): bool
    {
        if ($comment->status === $data['status']) {
            return false;
        }

        if ($data['status'] === 'pending' || $data['status'] === 'rejected') {
            $data['published_at'] = null;
        } else {
            $data['published_at'] = now();
        }

        return $this->commentRepository->update($data, $comment->id);


    }

    public function delete(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment->id);
    }

    public function create(User $user, Post $post, array $data): Comment
    {
        $commentData = array_merge($data, ['user_id' => $user->id, 'post_id' => $post->id]);

        $comment = $this->commentRepository->create($commentData);

        broadcast(new CommentCreated($comment))->toOthers();

        return $comment;
    }
}
