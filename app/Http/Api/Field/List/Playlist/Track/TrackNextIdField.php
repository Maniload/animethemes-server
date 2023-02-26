<?php

declare(strict_types=1);

namespace App\Http\Api\Field\List\Playlist\Track;

use App\Contracts\Http\Api\Field\CreatableField;
use App\Contracts\Http\Api\Field\SelectableField;
use App\Contracts\Http\Api\Field\UpdatableField;
use App\Http\Api\Field\Field;
use App\Http\Api\Query\Query;
use App\Http\Api\Schema\Schema;
use App\Models\List\Playlist;
use App\Models\List\Playlist\PlaylistTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Class TrackNextIdField.
 */
class TrackNextIdField extends Field implements CreatableField, SelectableField, UpdatableField
{
    /**
     * Create a new field instance.
     *
     * @param  Schema  $schema
     */
    public function __construct(Schema $schema)
    {
        parent::__construct($schema, PlaylistTrack::ATTRIBUTE_NEXT);
    }

    /**
     * Set the creation validation rules for the field.
     *
     * @param  Request  $request
     * @return array
     */
    public function getCreationRules(Request $request): array
    {
        /** @var Playlist|null $playlist */
        $playlist = $request->route('playlist');

        return [
            'sometimes',
            'required',
            'integer',
            Str::of('prohibits:')->append(PlaylistTrack::ATTRIBUTE_PREVIOUS)->__toString(),
            Rule::exists(PlaylistTrack::TABLE, PlaylistTrack::ATTRIBUTE_ID)
                ->where(PlaylistTrack::ATTRIBUTE_PLAYLIST, $playlist?->getKey()),
        ];
    }

    /**
     * Determine if the field should be included in the select clause of our query.
     *
     * @param  Query  $query
     * @param  Schema  $schema
     * @return bool
     */
    public function shouldSelect(Query $query, Schema $schema): bool
    {
        // Needed to match next track relation.
        return true;
    }

    /**
     * Set the update validation rules for the field.
     *
     * @param  Request  $request
     * @return array
     */
    public function getUpdateRules(Request $request): array
    {
        /** @var Playlist|null $playlist */
        $playlist = $request->route('playlist');

        /** @var PlaylistTrack|null $track */
        $track = $request->route('track');

        return [
            'sometimes',
            'required',
            'integer',
            Str::of('prohibits:')->append(PlaylistTrack::ATTRIBUTE_PREVIOUS)->__toString(),
            Rule::exists(PlaylistTrack::TABLE, PlaylistTrack::ATTRIBUTE_ID)
                ->where(PlaylistTrack::ATTRIBUTE_PLAYLIST, $playlist?->getKey())
                ->whereNot(PlaylistTrack::ATTRIBUTE_ID, $track?->getKey()),
        ];
    }
}
