<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\BadgeProject
 *
 * @property-read \App\Models\Badge $badge
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BadgeProject query()
 * @mixin \Eloquent
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
        'working' => 'Working',
        'in_progress' => 'In progress',
        'broken' => 'Broken',
        'unknown' => 'Unknown'
    ];

    /**
     * The Project this Badge support status relationship belongs to.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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
