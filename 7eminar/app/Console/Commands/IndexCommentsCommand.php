<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Comment;
use Elastic\Elasticsearch\Client;

class IndexCommentsCommand extends Command
{
    protected $signature = 'es:index-comments';
    protected $description = 'Perform full indexing of comments into Elasticsearch';

    protected Client $esClient;

    public function __construct()
    {
        parent::__construct();

        $this->esClient = app(Client::class);
    }

    public function handle()
    {
        $this->info('Starting full comments indexing...');

        Comment::chunk(100, function ($comments) {
            $this->info('Indexing batch of ' . $comments->count() . ' comments...');

            foreach ($comments as $comment) {
                try {
                    $this->esClient->index([
                        'index' => 'comments',
                        'id'    => $comment->id,
                        'body'  => $comment->toArray(),
                    ]);
                } catch (\Exception $e) {
                    $this->error('Failed to index comment ID ' . $comment->id . ': ' . $e->getMessage());
                }
            }
        });

        $this->esClient->indices()->refresh(['index' => 'comments']);

        $this->info('Comments indexing completed successfully.');
    }
}
