<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\CommentSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;


    #[Test]
    public function it_creates_comment_successfully()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $payload = [
            'message' => 'This is a test comment',
            'rating' => 5,
        ];

        $response = $this->actingAs($user)->postJson("/api/post/{$post->id}/comments", $payload);

        $response->assertCreated();
        $response->assertJsonFragment([
            'message' => 'This is a test comment',
            'rating' => 5
        ]);

        $this->assertDatabaseHas('comments', [
            'message' => 'This is a test comment',
            'rating' => 5,
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }


    #[Test]
    public function it_fails_to_create_comment_with_invalid_data()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $payload = [
            'message' => '',
            'rating' => 10,
        ];

        $response = $this->actingAs($user)->postJson("/api/post/{$post->id}/comments", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message', 'rating']);
    }

    #[Test]
    public function it_updates_comment_if_user_is_authorized()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'message' => 'Old message',
            'rating' => 3,
        ]);

        $payload = [
            'message' => 'Updated message',
            'rating' => 4,
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/comments/{$comment->id}", $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment(['status' => true]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'message' => 'Updated message',
            'rating' => 4,
        ]);
    }

    #[Test]
    public function it_forbids_updating_comment_if_user_is_not_authorized()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $post = Post::factory()->create();

        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'post_id' => $post->id,
        ]);

        $payload = [
            'message' => 'Hacked!',
            'rating' => 1,
        ];

        $response = $this->actingAs($attacker)
            ->putJson("/api/comments/{$comment->id}", $payload);

        $response->assertForbidden();
    }

    #[Test]
    public function it_deletes_comment_if_user_is_authorized()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function it_forbids_deleting_comment_if_user_is_not_authorized()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $post = Post::factory()->create();

        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'post_id' => $post->id,
        ]);

        $response = $this->actingAs($attacker)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function it_returns_comments_from_elasticsearch_search()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $matchingComment = Comment::factory()->create([
            'message' => 'This is a matching comment',
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $nonMatchingComment = Comment::factory()->create([
            'message' => 'Unrelated comment',
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        // Мокуємо CommentSearchService
        $this->mock(CommentSearchService::class, function ($mock) use ($matchingComment) {
            $mock->shouldReceive('search')
                ->once()
                ->with('matching')
                ->andReturn(collect([$matchingComment]));
        });

        $response = $this->getJson('/api/comments/search?q=matching');

        $response->assertOk();
        $response->assertJsonFragment(['message' => 'This is a matching comment']);
        $response->assertJsonMissing(['message' => 'Unrelated comment']);
    }
}
