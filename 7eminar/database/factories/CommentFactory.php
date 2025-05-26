<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'message' => $this->faker->sentence(10),
            'published_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'rating' => $this->faker->optional()->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
