<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;

/**
 * Class File.
 *
 * @author annejan@badge.team
 *
 * @property int $id
 * @property int $user_id
 * @property int $version_id
 * @property string $name
 * @property mixed|null $content
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $editable
 * @property-read string $extension
 * @property-read string $mime
 * @property-read int $size_of_content
 * @property-read string|null $viewable
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Version $version
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\File onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereVersionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\File withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\File withoutTrashed()
 * @mixin \Eloquent
 */
class File extends Model
{
    use SoftDeletes;

    /**
     * Supported extensions.
     *
     * @var array<string>
     */
    public static $extensions = [
        'py', 'pyc',
        'png', 'bmp', 'jpg',
        'json', 'txt', 'md',
        'wav', 'mp3', 'ogg',
        'mod', 'xm', 's3m',
        'elf', 'bin', 'gif',
    ];

    /**
     * Mime types for supported extensions.
     *
     * @var array<string,string>
     */
    public static $mimes = [
        'py'   => 'application/x-python-code',
        'txt'  => 'text/plain',
        'pyc'  => 'application/x-python-bytecode',
        'png'  => 'image/png',
        'json' => 'application/json',
        'md'   => 'text/markdown',
        'mp3'  => 'audio/mpeg',
        'elf'  => 'application/x-elf',
        'bmp'  => 'image/bmp',
        'jpg'  => 'image/jpeg',
        'wav'  => 'audio/wave',
        'ogg'  => 'audio/ogg',
        'mod'  => 'audio/mod',
        'xm'   => 'audio/module-xm',
        's3m'  => 'audio/s3m',
        'gif'  => 'image/gif',
    ];

    /**
     * File extensions viewable by Hatchery.
     *
     * @var array<string,string>
     */
    public static $viewables = [
        'png'  => 'image',
        'bmp'  => 'image',
        'jpg'  => 'image',
        'mp3'  => 'audio',
        'wav'  => 'audio',
        'ogg'  => 'audio',
        'gif'  => 'image',
    ];

    /**
     * File extensions editable by Hatchery.
     *
     * @var array<string>
     */
    protected $editables = [
        'py',
        'txt',
        'md',
        'json',
    ];

    /**
     * Appended magic variables.
     *
     * @var array<string>
     */
    protected $appends = ['editable', 'extension', 'size_of_content'];

    /**
     * Mass assignable variables.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'version_id'];

    /**
     * Make sure a file is owned by a user.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(
            function ($file) {
                if ($file->user_id === null) {
                    $user = Auth::guard()->user();
                    $file->user()->associate($user);
                }
            }
        );
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
        return ltrim((string) strstr($this->name, '.'), '.');
    }

    /**
     * @return bool
     */
    public function getEditableAttribute(): bool
    {
        return in_array($this->extension, $this->editables);
    }

    /**
     * @return int
     */
    public function getSizeOfContentAttribute(): ?int
    {
        if (is_string($this->content)) {
            return strlen($this->content);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getMimeAttribute(): string
    {
        if (array_key_exists($this->extension, self::$mimes)) {
            return self::$mimes[$this->extension];
        }

        return 'application/octet-stream';
    }

    /**
     * @return string|null
     */
    public function getViewableAttribute(): ?string
    {
        if (array_key_exists($this->extension, self::$viewables)) {
            return self::$viewables[$this->extension];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isValidIcon(): bool
    {
        if ($this->extension != 'png') {
            return false;
        }

        try {
            $icon = Image::make($this->content);
        } catch (NotReadableException $e) {
            return false;
        }

        return $icon->width() == 32 && $icon->height() == 32;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    public static function valid(string $fileName): bool
    {
        $str = strstr($fileName, '.');
        if (!$str) {
            return false;
        }
        $ext = ltrim($str, '.');

        return in_array($ext, self::$extensions);
    }
}
