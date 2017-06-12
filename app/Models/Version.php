<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Version extends Model
{
    use SoftDeletes;

    /**
     * Get the project this version belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo('App\Models\Project')->withTrashed();
    }
}
