<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class BadgeProject.
 *
 * @author annejan@badge.team
 *
 * @property int         $id
 * @property int         $badge_id
 * @property int         $project_id
 * @property string      $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Badge $badge
 * @property-read Project $project
 *
 * @method static Builder|BadgeProject newModelQuery()
 * @method static Builder|BadgeProject newQuery()
 * @method static Builder|BadgeProject query()
 * @method static Builder|BadgeProject whereBadgeId($value)
 * @method static Builder|BadgeProject whereCreatedAt($value)
 * @method static Builder|BadgeProject whereId($value)
 * @method static Builder|BadgeProject whereProjectId($value)
 * @method static Builder|BadgeProject whereStatus($value)
 * @method static Builder|BadgeProject whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BadgeProject extends Model
{
    use HasFactory;

    /**
     * Database table (relationships like this are not plural it seems).
     *
     * @var string
     */
    protected $table = 'badge_project';

    /**
     * Possible states.
     *
     * @var array<string, string>
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
