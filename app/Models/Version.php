<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Version extends Model
{
    use SoftDeletes;

    protected $appends = ['published'];

    /**
     * Get the Project this Version belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo('App\Models\Project')->withTrashed();
    }

    /**
     * Get the Versions this Project has.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * @return bool
     */
    public function getPublishedAttribute(): bool
    {
        return !empty($this->zip);
    }

    // @TODO scope published / unpublished
}
