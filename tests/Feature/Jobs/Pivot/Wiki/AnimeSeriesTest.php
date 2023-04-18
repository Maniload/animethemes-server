<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Pivot\Wiki;

use App\Constants\Config\FlagConstants;
use App\Events\Pivot\Wiki\AnimeSeries\AnimeSeriesCreated;
use App\Events\Pivot\Wiki\AnimeSeries\AnimeSeriesDeleted;
use App\Jobs\SendDiscordNotificationJob;
use App\Models\Wiki\Anime;
use App\Models\Wiki\Series;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class AnimeSeriesTest.
 */
class AnimeSeriesTest extends TestCase
{
    /**
     * When an Anime is attached to a Series or vice versa, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testAnimeSeriesCreatedSendsDiscordNotification(): void
    {
        $anime = Anime::factory()->createOne();
        $series = Series::factory()->createOne();

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);
        Event::fakeExcept(AnimeSeriesCreated::class);

        $anime->series()->attach($series);

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }

    /**
     * When an Anime is detached from a Series or vice versa, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testAnimeSeriesDeletedSendsDiscordNotification(): void
    {
        $anime = Anime::factory()->createOne();
        $series = Series::factory()->createOne();

        $anime->series()->attach($series);

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);
        Event::fakeExcept(AnimeSeriesDeleted::class);

        $anime->series()->detach($series);

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }
}
