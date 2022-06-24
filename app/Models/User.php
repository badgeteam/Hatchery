<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use LaravelWebauthn\Models\WebauthnKey;

/**
 * Class User.
 *
 * @author annejan@badge.team
 * @property int         $id
 * @property bool        $admin
 * @property string      $name
 * @property string      $email
 * @property string      $password
 * @property string|null $remember_token
 * @property string      $editor
 * @property bool        $public
 * @property bool        $show_projects
 * @property bool        $google2fa_enabled
 * @property string|null $google2fa_secret
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $email_verified_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Project[] $projects
 * @property-read int|null $projects_count
 * @property-read Collection|Vote[] $votes
 * @property-read int|null $votes_count
 * @property-read Collection|Warning[] $warnings
 * @property-read int|null $warnings_count
 * @property-read Collection|WebauthnKey[] $webauthnKeys
 * @property-read int|null $webauthn_keys_count
 * @property-read Collection|Project[] $collaborations
 * @property-read int|null $collaborations_count
 * @method static bool|null forceDelete()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User query()
 * @method static bool|null restore()
 * @method static Builder|User whereAdmin($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEditor($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereGoogle2faEnabled($value)
 * @method static Builder|User whereGoogle2faSecret($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User wherePublic($value)
 * @method static Builder|User whereShowProjects($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 * @method static UserFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'editor',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password', 'remember_token', 'google2fa_secret',
    ];

    /**
     * Get the Projects for the User.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the Votes for the User.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the (Project)Warnings for the User.
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class);
    }

    /**
     * Get the WebauthnKeys for the User.
     */
    public function webauthnKeys(): HasMany
    {
        return $this->hasMany(WebauthnKey::class);
    }

    /**
     * Collaborations.
     *
     * @return BelongsToMany
     */
    public function collaborations(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    /**
     * Change the email to an impossible email.
     */
    public function delete(): ?bool
    {
        $this->email = 'deleted' . mt_rand() . '#' . $this->email;
        $this->save();

        return parent::delete();
    }
}
