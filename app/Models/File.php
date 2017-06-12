<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    /**
     * Get the Project Version this File belongs to.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class)->withTrashed();
    }
}
