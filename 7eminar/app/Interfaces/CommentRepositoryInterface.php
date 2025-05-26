<?php

namespace App\Interfaces;

use App\Models\Comment;

interface CommentRepositoryInterface
{
    public function find(int $id): ?Comment;
    public function create(array $data): Comment;
    public function update(array $data, int $id): bool;
    public function delete(int $id): bool;

    /**
     * @param int $postId
     * @return Comment[]
    */
    public function getByPostId(int $postId): array;
}
