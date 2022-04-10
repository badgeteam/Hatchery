<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Class Category.
 *
 * @author annejan@badge.team
 * @property int         $id
 * @property string      $name
 * @property string      $slug
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool        $hidden
 * @property-read int $eggs
 * @property-read Collection|Project[] $projects
 * @property-read int|null $projects_count
 * @method static bool|null forceDelete()
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category onlyTrashed()
 * @method static Builder|Category query()
 * @method static bool|null restore()
 * @method static Builder|Category whereCreatedAt($value)
 * @method static Builder|Category whereDeletedAt($value)
 * @method static Builder|Category whereHidden($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereName($value)
 * @method static Builder|Category whereSlug($value)
 * @method static Builder|Category whereUpdatedAt($value)
 * @method static Builder|Category withTrashed()
 * @method static Builder|Category withoutTrashed()
 * @mixin \Eloquent
 * @method static \Database\Factories\CategoryFactory factory(...$parameters)
 */
class Category extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * Hidden attributes.
     *
     * @var array<string>
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'id', 'hidden'];

    /**
     * Appended attributes.
     *
     * @var array<string>
     */
    protected $appends = ['eggs'];

    /**
     * Boot function to create slug from name upon saving.
     */
    public static function boot(): void
    {
        parent::boot();

        static::saving(
            function ($project) {
                $project->slug = Str::slug($project->name, '_');
            }
        );
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
        return $this->projects()->whereHas(
            'versions',
            function ($query) {
                $query->whereNotNull('zip');
            }
        )->count();
    }
}
