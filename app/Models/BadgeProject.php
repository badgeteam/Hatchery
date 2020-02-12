<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\BadgeProject.
 *
 * @property-read \App\Models\Badge $badge
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $badge_id
 * @property int $project_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject whereBadgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject whereUpdatedAt($value)
 */
class BadgeProject extends Model
{
    /**
     * Database table (relationships like this are not plural it seems).
     *
     * @var string
     */
    protected $table = 'badge_project';

    /**
     * Possible states.
     *
     * @var array
     */
    public static $states = [
        'working'     => 'Working',
        'in_progress' => 'In progress',
        'broken'      => 'Broken',
        'unknown'     => 'Unknown',
    ];

    /**
     * The Project this Badge support status relationship belongs to.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    /**
     * The Badge this Project support status relationship belongs to.
     *
     * @return BelongsTo
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
