<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class Warning.
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User $user
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Warning onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Warning whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Warning withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Warning withoutTrashed()
 * @mixin \Eloquent
 *
 * @author annejan@badge.team
 */
class Warning extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'project_id', 'description',
    ];

    /**
     * Make sure a user is assigned.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(
            function ($warning) {
                if ($warning->user_id === null) {
                    $user = Auth::guard()->user();
                    $warning->user()->associate($user);
                }
            }
        );
    }

    /**
     * Get the User that owns this Vote.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the Project that this Vote is for.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }
}
