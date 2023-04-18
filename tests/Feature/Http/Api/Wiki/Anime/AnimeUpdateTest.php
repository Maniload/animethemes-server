<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\Wiki\Anime;

use App\Enums\Auth\CrudPermission;
use App\Enums\Models\Wiki\AnimeSeason;
use App\Models\Auth\User;
use App\Models\Wiki\Anime;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class AnimeUpdateTest.
 */
class AnimeUpdateTest extends TestCase
{
    /**
     * The Anime Update Endpoint shall be protected by sanctum.
     *
     * @return void
     */
    public function testProtected(): void
    {
        $anime = Anime::factory()->createOne();

        $parameters = array_merge(
            Anime::factory()->raw(),
            [Anime::ATTRIBUTE_SEASON => AnimeSeason::getRandomInstance()->description],
        );

        $response = $this->put(route('api.anime.update', ['anime' => $anime] + $parameters));

        $response->assertUnauthorized();
    }

    /**
     * The Anime Update Endpoint shall forbid users without the update anime permission.
     *
     * @return void
     */
    public function testForbidden(): void
    {
        $anime = Anime::factory()->createOne();

        $parameters = array_merge(
            Anime::factory()->raw(),
            [Anime::ATTRIBUTE_SEASON => AnimeSeason::getRandomInstance()->description],
        );

        $user = User::factory()->createOne();

        Sanctum::actingAs($user);

        $response = $this->put(route('api.anime.update', ['anime' => $anime] + $parameters));

        $response->assertForbidden();
    }

    /**
     * The Anime Update Endpoint shall forbid users from updating an anime that is trashed.
     *
     * @return void
     */
    public function testTrashed(): void
    {
        $anime = Anime::factory()->createOne();

        $anime->delete();

        $parameters = array_merge(
            Anime::factory()->raw(),
            [Anime::ATTRIBUTE_SEASON => AnimeSeason::getRandomInstance()->description],
        );

        $user = User::factory()->withPermissions(CrudPermission::UPDATE()->format(Anime::class))->createOne();

        Sanctum::actingAs($user);

        $response = $this->put(route('api.anime.update', ['anime' => $anime] + $parameters));

        $response->assertForbidden();
    }

    /**
     * The Anime Update Endpoint shall update an anime.
     *
     * @return void
     */
    public function testUpdate(): void
    {
        $anime = Anime::factory()->createOne();

        $parameters = array_merge(
            Anime::factory()->raw(),
            [Anime::ATTRIBUTE_SEASON => AnimeSeason::getRandomInstance()->description],
        );

        $user = User::factory()->withPermissions(CrudPermission::UPDATE()->format(Anime::class))->createOne();

        Sanctum::actingAs($user);

        $response = $this->put(route('api.anime.update', ['anime' => $anime] + $parameters));

        $response->assertOk();
    }
}
