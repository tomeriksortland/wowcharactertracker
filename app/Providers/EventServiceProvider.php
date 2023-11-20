<?php

namespace App\Providers;

use App\Events\ApiErrorLog;
use App\Events\ApiLog;
use App\Events\UpdateCharacters;
use App\Listeners\FetchAndUpdateCharacters;
use App\Listeners\LogApiErrorRequest;
use App\Listeners\LogApiRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Battlenet\BattlenetExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SocialiteWasCalled::class => [
            BattlenetExtendSocialite::class . '@handle',
        ],
        UpdateCharacters::class => [
            FetchAndUpdateCharacters::class
        ],
        ApiLog::class => [
            LogApiRequest::class
        ],
        ApiErrorLog::class => [
            LogApiErrorRequest::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
