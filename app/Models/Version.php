<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Class Version.
 *
 * @author annejan@badge.team
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $project_id
 * @property int         $revision
 * @property string|null $zip
 * @property int|null    $size_of_zip
 * @property string|null $git_commit_id
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|File[] $files
 * @property-read int|null $files_count
 * @property-read bool $published
 * @property-read Project $project
 * @property-read User $user
 *
 * @method static bool|null forceDelete()
 * @method static Builder|Version newModelQuery()
 * @method static Builder|Version newQuery()
 * @method static Builder|Version onlyTrashed()
 * @method static Builder|Version published()
 * @method static Builder|Version query()
 * @method static bool|null restore()
 * @method static Builder|Version unPublished()
 * @method static Builder|Version whereCreatedAt($value)
 * @method static Builder|Version whereDeletedAt($value)
 * @method static Builder|Version whereGitCommitId($value)
 * @method static Builder|Version whereId($value)
 * @method static Builder|Version whereProjectId($value)
 * @method static Builder|Version whereRevision($value)
 * @method static Builder|Version whereSizeOfZip($value)
 * @method static Builder|Version whereUpdatedAt($value)
 * @method static Builder|Version whereUserId($value)
 * @method static Builder|Version whereZip($value)
 * @method static Builder|Version withTrashed()
 * @method static Builder|Version withoutTrashed()
 * @mixin \Eloquent
 */
class Version extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * Appended magic data.
     *
     * @var array<string>
     */
    protected $appends = ['published'];

    /**
     * Hidden data.
     *
     * @var array<string>
     */
    protected $hidden = ['git_commit_id'];

    /**
     * Make sure a user is assigned.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(
            function ($version) {
                if ($version->user_id === null) {
                    $user = Auth::guard()->user();
                    $version->user()->associate($user);
                }
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
