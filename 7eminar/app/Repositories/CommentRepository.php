<?php

namespace App\Repositories;

use App\Interfaces\CommentRepositoryInterface;
use App\Models\Comment;

class CommentRepository implements CommentRepositoryInterface
{

    public function getByPostId(int $postId): array
    {
        return Comment::where('post_id', $postId)->orderBy('published_at', 'desc')->get()->all();
    }

    public function update(array $data, $id): bool
    {
        return Comment::findOrFail($id)->update($data);
    }

    public function delete($id): bool
    {
        return Comment::findOrFail($id)->delete();
    }

    public function find(int $id): ?Comment
    {
        return Comment::findOrFail($id);
    }

    public function create(array $data): Comment
    {
        return Comment::create($data);
    }
}
