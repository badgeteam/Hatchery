<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\Category.
 *
 * @property-read int $eggs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category withoutTrashed()
 * @mixin \Eloquent
 */
class Category extends Model
{
    use SoftDeletes;

    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'id', 'hidden'];

    protected $appends = ['eggs'];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($project) {
            $project->slug = Str::slug($project->name, '_');
        });
    }

    /**
     * Get the Projects that belong to this Category has.
     *
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
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

    /**
     * Get the project count for this category.
     *
     * @return int
     */
    public function getEggsAttribute(): int
    {
        return $this->projects()->whereHas('versions', function ($query) {
            $query->whereNotNull('zip');
        })->count();
    }
}
