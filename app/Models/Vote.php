<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Vote
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property string $type
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Vote onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Vote whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Vote withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Vote withoutTrashed()
 * @mixin \Eloquent
 */
class Vote extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id', 'type',
    ];


    /**
     * Make sure a user is assigned.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($vote) {
            $user = Auth::guard()->user();
            $vote->user()->associate($user);
        });
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
