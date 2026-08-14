<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Models;

use App\Support\Helpers;
use App\Support\Icon;
use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Class File.
 *
 * @author annejan@badge.team
 * @property int         $id
 * @property int         $user_id
 * @property int         $version_id
 * @property string      $name
 * @property mixed|null  $content
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $editable
 * @property-read bool $lintable
 * @property-read bool $processable
 * @property-read string $extension
 * @property-read string $baseName
 * @property-read string $mime
 * @property-read int $size_of_content
 * @property-read string $size_formatted
 * @property-read string|null $viewable
 * @property-read string $crc32
 * @property-read User $user
 * @property-read Version $version
 * @property-read string $base_name
 * @method static bool|null forceDelete()
 * @method static Builder|File newModelQuery()
 * @method static Builder|File newQuery()
 * @method static Builder|File onlyTrashed()
 * @method static Builder|File query()
 * @method static bool|null restore()
 * @method static Builder|File whereContent($value)
 * @method static Builder|File whereCreatedAt($value)
 * @method static Builder|File whereDeletedAt($value)
 * @method static Builder|File whereId($value)
 * @method static Builder|File whereName($value)
 * @method static Builder|File whereUpdatedAt($value)
 * @method static Builder|File whereUserId($value)
 * @method static Builder|File whereVersionId($value)
 * @method static Builder|File withTrashed()
 * @method static Builder|File withoutTrashed()
 * @method static FileFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class File extends Model
{
    use SoftDeletes;
    /** @use HasFactory<FileFactory> */
    use HasFactory;

    /**
     * Largest upload accepted, in megabytes.
     *
     * The whole chain has to agree with this: PHP's upload_max_filesize and
     * post_max_size, MariaDB's max_allowed_packet, and the LONGBLOB the
     * content is stored in.
     */
    public const MAX_UPLOAD_MEGABYTES = 32;

    /**
     * Supported extensions.
     *
     * @var array<string>
     */
    public static $extensions = [
        'py', 'pyc', 'mpy', 'v',
        'png', 'bmp', 'jpg',
        'json', 'txt', 'md',
        'wav', 'mp3', 'ogg',
        'mod', 'xm', 's3m',
        'elf', 'bin', 'gif', 'dat',
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
        'mpy'  => 'application/x-python-bytecode',
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
        'v'    => 'text/x-verilog',
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
        'v',
    ];

    /**
     * File extensions lintable by Hatchery.
     *
     * @var array<string>
     */
    protected $lintables = [
        'py',
        'md',
        'json',
        'v',
    ];

    /**
     * File extensions processable by Hatchery.
     *
     * @var array<string>
     */
    protected $processables = [
        'v',
    ];

    /**
     * Appended magic variables.
     *
     * @var list<string>
     */
    protected $appends = ['editable', 'extension', 'size_of_content'];

    /**
     * Mass assignable variables.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'version_id', 'content'];

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

        static::saving(
            function ($file) {
                // A badge wants icon.png at exactly 32 by 32. Scale whatever
                // was uploaded instead of quietly dropping it from the egg.
                if (!Icon::isIconName((string) $file->name) || !$file->isDirty('content')) {
                    return;
                }

                $resized = Icon::normalise((string) $file->content);
                if ($resized !== null) {
                    $file->content = $resized;
                }
            }
        );
    }

    /**
     * Get the Project Version this File belongs to.
     *
     * @return BelongsTo<Version, $this>
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class)->withTrashed();
    }

    /**
     * Get the User that owns the Project.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return string
     */
    public function getExtensionAttribute(): string
    {
        return ltrim((string) strstr($this->name, '.'), '.');
    }

    /**
     * @return string
     */
    public function getBaseNameAttribute(): string
    {
        return str_replace('.' . $this->extension, '', $this->name);
    }

    /**
     * @return bool
     */
    public function getEditableAttribute(): bool
    {
        return in_array($this->extension, $this->editables);
    }

    /**
     * @return bool
     */
    public function getLintableAttribute(): bool
    {
        return in_array($this->extension, $this->lintables);
    }

    /**
     * @return bool
     */
    public function getProcessableAttribute(): bool
    {
        return in_array($this->extension, $this->processables);
    }

    /**
     * @return int
     */
    public function getSizeOfContentAttribute(): ?int
    {
        if ($this->content) {
            return strlen($this->content);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getSizeFormattedAttribute(): string
    {
        return Helpers::formatBytes((int) $this->getSizeOfContentAttribute());
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
     * @return string
     */
    public function getCrc32Attribute(): string
    {
        return hash('crc32b', $this->content);
    }

    /**
     * @return bool
     */
    public function isValidIcon(): bool
    {
        if ($this->extension != 'png') {
            return false;
        }

        return Icon::dimensions((string) $this->content) === [Icon::SIZE, Icon::SIZE];
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
