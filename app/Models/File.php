<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class File extends Model
{
    use SoftDeletes;

    public static $extensions = ['py', 'txt', 'pyc', 'png', 'json', 'md'];

    protected $editable = ['py', 'txt', 'md'];

    protected $appends = ['editable', 'extension', 'size_of_content'];

    protected $fillable = ['name'];

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
        $parts = explode('.', $this->name);

        return end($parts);
    }

    /**
     * @return bool
     */
    public function getEditableAttribute(): bool
    {
        return in_array($this->extension, $this->editable);
    }

    /**
     * @return int
     */
    public function getSizeOfContentAttribute():? int
    {
        if (is_string($this->content)) {
            return strlen($this->content);
        }
        return null;
    }
}
