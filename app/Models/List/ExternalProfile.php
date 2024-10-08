<?php

declare(strict_types=1);

namespace App\Models\List;

use App\Enums\Models\List\ExternalProfileSite;
use App\Enums\Models\List\ExternalProfileVisibility;
use App\Events\List\ExternalProfile\ExternalProfileCreated;
use App\Events\List\ExternalProfile\ExternalProfileDeleted;
use App\Events\List\ExternalProfile\ExternalProfileRestored;
use App\Events\List\ExternalProfile\ExternalProfileUpdated;
use App\Models\Auth\User;
use App\Models\BaseModel;
use App\Models\List\External\ExternalEntry;
use Database\Factories\List\ExternalProfileFactory;
use Elastic\ScoutDriverPlus\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class ExternalProfile.
 *
 * @property int $profile_id
 * @property Collection<int, ExternalEntry> $externalentries
 * @property string $name
 * @property ExternalProfileSite $site
 * @property int|null $user_id
 * @property User|null $user
 * @property ExternalProfileVisibility $visibility
 * 
 * @method static ExternalProfileFactory factory(...$parameters)
 */
class ExternalProfile extends BaseModel
{
    use Searchable;

    final public const TABLE = 'external_profiles';

    final public const ATTRIBUTE_ID = 'profile_id';
    final public const ATTRIBUTE_NAME = 'name';
    final public const ATTRIBUTE_SITE = 'site';
    final public const ATTRIBUTE_VISIBILITY = 'visibility';
    final public const ATTRIBUTE_USER = 'user_id';

    final public const RELATION_ANIMES = 'externalentries.anime';
    final public const RELATION_EXTERNAL_ENTRIES = 'externalentries';
    final public const RELATION_USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        ExternalProfile::ATTRIBUTE_NAME,
        ExternalProfile::ATTRIBUTE_SITE,
        ExternalProfile::ATTRIBUTE_VISIBILITY,
        ExternalProfile::ATTRIBUTE_USER,
    ];

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ExternalProfileCreated::class,
        'deleted' => ExternalProfileDeleted::class,
        'restored' => ExternalProfileRestored::class,
        'updated' => ExternalProfileUpdated::class,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = ExternalProfile::TABLE;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = ExternalProfile::ATTRIBUTE_ID;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            ExternalProfile::ATTRIBUTE_SITE => ExternalProfileSite::class,
            ExternalProfile::ATTRIBUTE_VISIBILITY => ExternalProfileVisibility::class,
        ];
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get subtitle.
     *
     * @return string
     */
    public function getSubtitle(): string
    {
        return $this->user === null ? $this->getName() : $this->user->getName();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function getRouteKeyName(): string
    {
        return ExternalProfile::ATTRIBUTE_ID;
    }

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable(): bool
    {
        return ExternalProfileVisibility::PUBLIC === $this->visibility;
    }

    /**
     * Get the entries for the profile.
     *
     * @return HasMany
     */
    public function externalentries(): HasMany
    {
        return $this->hasMany(ExternalEntry::class, ExternalEntry::ATTRIBUTE_PROFILE);
    }

    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, ExternalProfile::ATTRIBUTE_USER);
    }

    /**
     * Only get the attributes as an array to prevent recursive toArray() calls.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return $this->attributesToArray();
    }
}
