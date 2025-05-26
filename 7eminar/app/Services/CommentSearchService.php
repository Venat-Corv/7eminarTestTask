<?php

namespace App\Services;

use App\Models\Comment;
use Elastic\Elasticsearch\Client;

class CommentSearchService
{
    public function __construct(private Client $elasticsearch) {}

    public function search(string $query)
    {
        $items = $this->elasticsearch->search([
            'index' => 'comments',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => [
                            'message^3',
                            'user_name^2',
                            'post_title'
                        ],
                        'type' => 'best_fields',
                        'operator' => 'and'
                    ]
                ]
            ]
        ]);

        $ids = collect($items['hits']['hits'])->pluck('_id')->all();

        return Comment::with(['user', 'post'])
            ->whereIn('id', $ids)
            ->get();
    }
}
