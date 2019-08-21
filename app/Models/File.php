<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\File
 *
 * @property-read bool $editable
 * @property-read string $extension
 * @property-read int $size_of_content
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Version $version
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\File onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\File withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\File withoutTrashed()
 * @mixin \Eloquent
 */
class File extends Model
{
    use SoftDeletes;

    public static $extensions = ['py', 'txt', 'pyc', 'png', 'json', 'md', 'mp3', 'elf'];
    public static $mimes = [
        'py' => 'application/x-python-code',
        'txt' => 'text/plain',
        'pyc' => 'application/x-python-bytecode',
        'png' => 'image/png',
        'json' => 'application/json',
        'md' => 'text/markdown',
        'mp3' => 'audio/mpeg',
        'elf' => 'application/x-elf'
    ];

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

    /**
     * @param File $file
     * @return string
     */
    public static function getMime(File $file): string
    {
        $name = collect(explode('.', $file->name));

        if (in_array($name->last(), self::$extensions)) {
            return self::$mimes[$name->last()];
        }

        return 'application/octet-stream';
    }
}
