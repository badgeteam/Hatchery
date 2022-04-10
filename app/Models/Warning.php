<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Class Warning.
 *
 * @author annejan@badge.team
 * @property int         $id
 * @property int         $user_id
 * @property int         $project_id
 * @property string      $description
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project $project
 * @property-read User $user
 * @method static bool|null forceDelete()
 * @method static Builder|Warning newModelQuery()
 * @method static Builder|Warning newQuery()
 * @method static Builder|Warning onlyTrashed()
 * @method static Builder|Warning query()
 * @method static bool|null restore()
 * @method static Builder|Warning whereCreatedAt($value)
 * @method static Builder|Warning whereDeletedAt($value)
 * @method static Builder|Warning whereDescription($value)
 * @method static Builder|Warning whereId($value)
 * @method static Builder|Warning whereProjectId($value)
 * @method static Builder|Warning whereUpdatedAt($value)
 * @method static Builder|Warning whereUserId($value)
 * @method static Builder|Warning withTrashed()
 * @method static Builder|Warning withoutTrashed()
 * @mixin \Eloquent
 * @method static \Database\Factories\WarningFactory factory(...$parameters)
 */
class Warning extends Model
{
    use SoftDeletes;
    use HasFactory;

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
