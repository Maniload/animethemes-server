<?php

declare(strict_types=1);

namespace App\Actions\Discord;

use App\Models\Wiki\Image;
use App\Models\Wiki\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Class DiscordVideoNotificationAction.
 */
class DiscordVideoNotificationAction
{
    /**
     * Handle the action.
     *
     * @param  Collection<int, Video>  $videos
     * @param  array  $fields
     *
     * @return void
     */
    public function handle(Collection $videos, array $fields): void
    {
        $type = Arr::get($fields, 'notification-type');
        $shouldForce = Arr::get($fields, 'should-force-thread');

        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $fs = Storage::disk(Config::get('image.disk'));

        $newVideos = [];

        foreach ($videos as $video) {
            $video
                ->load([
                    'animethemeentries.animetheme.anime.discordthread',
                    'animethemeentries.animetheme.anime.images',
                    'animethemeentries.animetheme.group',
                    'animethemeentries.animetheme.song.artists',
                ]);

            $theme = $video->animethemeentries->first()->animetheme;
            $anime = $theme->anime;

            if ($anime->discordthread === null) {
                if ($shouldForce === 'no') return;

                $threadAction = new DiscordThreadAction();

                $threadAction->handle($anime, ['name' => $anime->getName()]);
                $anime->load('discordthread');
            }

            Arr::set($video, 'source_name', $video->source->localize());
            Arr::set($video, 'overlap_name', $video->overlap->localize());
            Arr::set($theme, 'type_name', $theme->type->localize());

            $anime->images->each(function (Image $image) use ($fs) {
                Arr::set($image, 'link', $fs->url($image->path));
            });

            $newVideos[] = $video;
        }

        Http::withHeaders(['x-api-key' => Config::get('services.discord.api_key')])
            ->post(Config::get('services.discord.api_url') . '/notification', [
                'type' => $type,
                'videos' => $newVideos,
            ])
            ->throw();
    }
}
