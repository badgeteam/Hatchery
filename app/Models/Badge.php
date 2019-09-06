<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Badge.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Badge query()
 * @mixin \Eloquent
 * @property-read int|null $projects_count
 */
class Badge extends Model
{
    /**
     * @return BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
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
