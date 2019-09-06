<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * App\Models\Project.
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
 * @property-read int|null $badges_count
 * @property-read int|null $dependants_count
 * @property-read int|null $dependencies_count
 * @property-read int|null $versions_count
 */
class Project extends Model
{
    use SoftDeletes;

    protected $appends = ['revision', 'size_of_zip', 'size_of_content', 'category'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'user_id', 'id', 'category_id', 'pivot'];

    public static $forbidden = [
        'os', 'uos', 'badge', 'esp32', 'ussl', 'time', 'utime', 'splash', 'launcher', 'installer', 'ota_update',
        'boot', 'appglue', 'database', 'dialogs', 'deepsleep', 'magic', 'ntp', 'rtcmem', 'machine', 'setup', 'version',
        'wifi', 'woezel', 'network', 'socket', 'uhashlib', 'hashlib', 'ugfx', 'btree', 'request', 'urequest', 'uzlib',
        'zlib', 'ssl',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            $user = Auth::guard()->user();
            $project->user()->associate($user);
        });

        static::created(function ($project) {
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
        });

        static::saving(function ($project) {
            $project->slug = Str::slug($project->name, '_');
            if (self::isForbidden($project->slug)) {
                throw new \Exception('reserved name');
            }
        });
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
     * @return string
     */
    public function getRevisionAttribute(): ? string
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
        return $this->belongsToMany(Badge::class);
    }

    /**
     * @return int
     */
    public function getSizeOfZipAttribute(): ? int
    {
        $version = $this->versions()->published()->get()->last();

        return is_null($version) ? null : (int) $version->size_of_zip;
    }

    /**
     * @return int
     */
    public function getSizeOfContentAttribute(): ? int
    {
        $version = $this->versions()->published()->get()->last();
        if (is_null($version)) {
            $version = $this->versions->last();
        }

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
    public function getCategoryAttribute(): ? string
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
}
