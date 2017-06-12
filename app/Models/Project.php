<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    /**
     * Get the User that owns the Project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    /**
     * Get the Versions this Project has.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    /**
     * @todo refactor, make version do all or most of this
     *
     * @return Project
     */
    public function assertEditableVersion(): Project
    {
        if ($this->versions()->where('zip', null)->count() < 1) {
            $last = $this->versions->last();
            if (is_null($last)) {
                $revision = 1;
            } else {
                $revision = $last->revision +1;
            }
            $version = new Version;
            $version->revision = $revision;
            $version->project()->associate($this);
            $version->save();
        }
        return $this;
    }
}
