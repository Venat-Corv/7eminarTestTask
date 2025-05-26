<?php

namespace App\Providers;

use App\Models\Comment;
use App\Observers\CommentObserver;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerSearchClient();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Comment::observe(app(CommentObserver::class));

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Broadcast::routes(['middleware' => ['custom.user.auth']]);
        require base_path('routes/channels.php');
    }

    private function registerSearchClient(): void
    {
        $this->app->bind(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts($app['config']->get('services.search.hosts'))
                ->build();
        });
    }
}
