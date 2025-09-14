<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'user_type',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * User types constants
     */
    public const TYPE_ADMIN = 'admin';
    public const TYPE_OWNER = 'owner';
    public const TYPE_CUSTOMER = 'customer';

    /**
     * Get user type options
     */
    public static function getUserTypes()
    {
        return [
            self::TYPE_ADMIN => __('enums.user_types.admin'),
            self::TYPE_OWNER => __('enums.user_types.owner'),
            self::TYPE_CUSTOMER => __('enums.user_types.customer'),
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->user_type === self::TYPE_ADMIN;
    }

    /**
     * Check if user is owner
     */
    public function isOwner()
    {
        return $this->user_type === self::TYPE_OWNER;
    }

    /**
     * Check if user is customer
     */
    public function isCustomer()
    {
        return $this->user_type === self::TYPE_CUSTOMER;
    }
}
