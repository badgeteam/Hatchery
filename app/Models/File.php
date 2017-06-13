<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class File extends Model
{
    use SoftDeletes;

    static public $extensions = ['py', 'txt', 'pyc', 'png'];

    protected $editable = ['py', 'txt'];

    protected $appends = ['editable', 'extension'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($file) {
            $user = Auth::guard()->user();
            $file->user()->associate($user);
        });
    }

    /**
     * Get the Project Version this File belongs to.
     *
     * @return BelongsTo
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class)->withTrashed();
    }

    /**
     * Get the User that owns the Project.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    /**
     * @return string
     */
    public function getExtensionAttribute(): string
    {
        return end(explode('.', $this->name));
    }

    /**
     * @return bool
     */
    public function getEditableAttribute(): bool
    {
        return in_array($this->extension, $this->editable);
    }
}
