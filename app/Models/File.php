<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    static public $extensions = ['py', 'txt', 'pyc', 'png'];

    protected $editable = ['py', 'txt'];

    protected $appends = ['editable', 'extension'];

    /**
     * Get the Project Version this File belongs to.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class)->withTrashed();
    }

    public function getExtensionAttribute(): string
    {
        return end(explode('.', $this->name));
    }

    public function getEditableAttribute(): bool
    {
        return in_array($this->extension, $this->editable);
    }
}
