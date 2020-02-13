<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class Project
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Badge[] $badges
 * @property-read string $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $dependants
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $dependencies
 * @property-read string $revision
 * @property-read int $size_of_content
 * @property-read int $size_of_zip
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Version[] $versions
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Project onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Project withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Project withoutTrashed()
 * @mixin \Eloquent
 *
 * @property-read int|null $badges_count
 * @property-read int|null $dependants_count
 * @property-read int|null $dependencies_count
 * @property-read int|null $versions_count
 * @property int $id
 * @property int $category_id
 * @property int $user_id
 * @property string $name
 * @property string|null $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $download_counter
 * @property string $status
 * @property-read string|null $description_html
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Vote[] $votes
 * @property-read int|null $votes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereDownloadCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereUserId($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BadgeProject[] $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Warning[] $warnings
 * @property-read int|null $warnings_count
 * @property \Illuminate\Support\Carbon|null $published_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project wherePublishedAt($value)
 *
 * @author annejan@badge.team
 * @package App\Models
 */
class Project extends Model
{
    use SoftDeletes;

    /**
     * Appended magic data.
     *
     * @var array<string>
     */
    protected $appends = [
        'revision',
        'size_of_zip',
        'size_of_content',
        'category',
        'description',
        'status',
    ];

    /**
     * Hidden data.
     *
     * @var array<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'user_id',
        'id',
        'category_id',
        'pivot',
        'versions',
        'states',
    ];

    /**
     * DateTime conversion for these fields.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at', 'published_at',
    ];

    /**
     * Forbidden names for apps.
     *
     * @var array<string>
     */
    public static $forbidden = [
        'os', 'uos', 'badge', 'esp32', 'ussl', 'time', 'utime', 'splash', 'launcher', 'installer', 'ota_update',
        'boot', 'appglue', 'database', 'dialogs', 'deepsleep', 'magic', 'ntp', 'rtcmem', 'machine', 'setup', 'version',
        'wifi', 'woezel', 'network', 'socket', 'uhashlib', 'hashlib', 'ugfx', 'btree', 'request', 'urequest', 'uzlib',
        'zlib', 'ssl',
    ];

    /**
     * Magical methods that associate a user and make sure projects have an empty __init__.py added.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(
            function ($project) {
                $user = Auth::guard()->user();
                $project->user()->associate($user);
            }
        );

        static::created(
            function ($project) {
                $version = new Version();
                $version->revision = 1;
                $version->project()->associate($project);
                $version->save();
                // add first empty python file :)
                $file = new File();
                $file->name = '__init__.py';
                $file->content = '';
                $file->version()->associate($version);
                $file->save();
            }
        );

        static::saving(
            function ($project) {
                $project->slug = Str::slug($project->name, '_');
                if (self::isForbidden($project->slug)) {
                    throw new \Exception('reserved name');
                }
            }
        );
    }

    /**
     * Get the User that owns the Project.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the Category this Project belongs to.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * Get the Versions this Project has.
     *
     * @return HasMany
     */
    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    /**
     * Get the Votes this Project has.
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the Warnings for the Project.
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class);
    }

    /**
     * Get the BadgeProjects for the Project.
     * This contains support state per badge.
     */
    public function states(): HasMany
    {
        return $this->hasMany(BadgeProject::class);
    }

    /**
     * @return string
     */
    public function getRevisionAttribute(): ?string
    {
        $version = $this->versions()->published()->get()->last();

        return is_null($version) ? null : (string) $version->revision;
    }

    /**
     * @return BelongsToMany
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'dependencies', 'project_id', 'depends_on_project_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function dependants(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'dependencies', 'depends_on_project_id', 'project_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)->withTimestamps();
    }

    /**
     * @return int
     */
    public function getSizeOfZipAttribute(): ?int
    {
        $version = $this->versions()->published()->get()->last();

        return is_null($version) ? null : (int) $version->size_of_zip;
    }

    /**
     * @return int
     */
    public function getSizeOfContentAttribute(): ?int
    {
        $version = $this->versions()->published()->get()->last();
        if (is_null($version)) {
            $version = $this->versions->last();
        }
        /** @var Version $version */
        $size = 0;
        foreach ($version->files as $file) {
            $size += strlen($file->content);
        }

        return $size;
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return string
     */
    public function getCategoryAttribute(): ?string
    {
        if (is_null($this->category()->first())) {
            return 'uncategorised';
        }

        return $this->category()->first()->slug;
    }

    /**
     * @param string $slug
     *
     * @return bool
     */
    public static function isForbidden(string $slug)
    {
        return in_array($slug, self::$forbidden);
    }

    /**
     * @return string|null
     */
    public function getDescriptionAttribute(): ?string
    {
        /** @var Version|null $version */
        $version = $this->versions->last();
        if ($version && $version->files()->where('name', 'like', 'README.md')->count() === 1) {
            return $version->files()->where('name', 'like', 'README.md')->first()->content;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getDescriptionHtmlAttribute(): ?string
    {
        if ($this->description) {
            return Markdown::parse($this->description);
        }

        return null;
    }

    /**
     * @return bool|null
     */
    public function userVoted(): ?bool
    {
        $user = Auth::guard()->user();
        if (is_null($user)) {
            return null;
        }

        return $this->votes()->where('user_id', $user->id)->exists();
    }

    /**
     * Ugly hack for now . .
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        $status = 'unknown';
        foreach ($this->states as $state) {
            if ($status === 'unknown') {
                $status = $state->status;
            }
            if ($status === 'broken') {
                if ($state->status !== 'unknown') {
                    $status = $state->status;
                }
            }
            if ($status === 'in_progress') {
                if ($state->status !== 'broken' && $state->status !== 'unknown') {
                    $status = $state->status;
                }
            }
            if ($status === 'working') {
                if ($state->status !== 'broken' && $state->status !== 'unknown' && $state->status !== 'in_progress') {
                    $status = $state->status;
                }
            }
        }

        return $status;
    }

    /**
     * @return bool
     */
    public function hasValidIcon(): bool
    {
        /** @var Version $version */
        $version = $this->versions->last();
        /** @var File|null $file */
        $file = $version->files()->where('name', 'icon.png')->get()->last();
        if (is_null($file)) {
            return false;
        }

        return $file->isValidIcon();
    }
}
