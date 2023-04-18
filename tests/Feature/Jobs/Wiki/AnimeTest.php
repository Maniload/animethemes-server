<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Wiki;

use App\Constants\Config\FlagConstants;
use App\Events\Wiki\Anime\AnimeCreated;
use App\Events\Wiki\Anime\AnimeDeleted;
use App\Events\Wiki\Anime\AnimeRestored;
use App\Events\Wiki\Anime\AnimeUpdated;
use App\Jobs\SendDiscordNotificationJob;
use App\Models\Wiki\Anime;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class AnimeTest.
 */
class AnimeTest extends TestCase
{
    /**
     * When an anime is created, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testAnimeCreatedSendsDiscordNotification(): void
    {
        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);
        Event::fakeExcept(AnimeCreated::class);

        Anime::factory()->createOne();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }

    /**
     * When an anime is deleted, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testAnimeDeletedSendsDiscordNotification(): void
    {
        $anime = Anime::factory()->createOne();

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);
        Event::fakeExcept(AnimeDeleted::class);

        $anime->delete();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }

    /**
     * When an anime is restored, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testAnimeRestoredSendsDiscordNotification(): void
    {
        $anime = Anime::factory()->createOne();

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);
        Event::fakeExcept(AnimeRestored::class);

        $anime->restore();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }

    /**
     * When an anime is updated, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testAnimeUpdatedSendsDiscordNotification(): void
    {
        $anime = Anime::factory()->createOne();

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);
        Event::fakeExcept(AnimeUpdated::class);

        $changes = Anime::factory()->makeOne();

        $anime->fill($changes->getAttributes());
        $anime->save();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }
}
