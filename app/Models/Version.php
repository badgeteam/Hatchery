<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Version.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $files
 * @property-read bool $published
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Version onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version published()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version unPublished()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Version withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Version withoutTrashed()
 * @mixin \Eloquent
 * @property-read int|null $files_count
 * @property int $id
 * @property int|null $user_id
 * @property int $project_id
 * @property int $revision
 * @property string|null $zip
 * @property int|null $size_of_zip
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereRevision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereSizeOfZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Version whereZip($value)
 */
class Version extends Model
{
    use SoftDeletes;

    /**
     * Appended magic data.
     *
     * @var array
     */
    protected $appends = ['published'];

    /**
     * Make sure a user is assigned.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($version) {
                $user = Auth::guard()->user();
                $version->user()->associate($user);
            }
        );
    }

    /**
     * Get the Project this Version belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo('App\Models\Project')->withTrashed();
    }

    /**
     * Get the Versions this Project has.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get the User that owns the Project.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    /**
     * @return bool
     */
    public function getPublishedAttribute(): bool
    {
        return !empty($this->zip);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('zip');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeUnPublished(Builder $query): Builder
    {
        return $query->whereNull('zip');
    }
}
