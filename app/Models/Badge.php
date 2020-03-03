<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Badge.
 *
 * @author annejan@badge.team
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects
 * @property-read int|null $projects_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BadgeProject[] $states
 * @property-read int|null $states_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge whereUpdatedAt($value)
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
