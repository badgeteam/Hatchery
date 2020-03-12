<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class Badge.
 *
 * @author annejan@badge.team
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Project[] $projects
 * @property-read int|null $projects_count
 * @property-read Collection|BadgeProject[] $states
 * @property-read int|null $states_count
 *
 * @method static Builder|Badge newModelQuery()
 * @method static Builder|Badge newQuery()
 * @method static Builder|Badge query()
 * @method static Builder|Badge whereCreatedAt($value)
 * @method static Builder|Badge whereDeletedAt($value)
 * @method static Builder|Badge whereId($value)
 * @method static Builder|Badge whereName($value)
 * @method static Builder|Badge whereSlug($value)
 * @method static Builder|Badge whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Badge extends Model
{
    /**
     * @return BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function states(): HasMany
    {
        return $this->hasMany(BadgeProject::class);
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
}
