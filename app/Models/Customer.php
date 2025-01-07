<?php

namespace App\Models;

use App\Services\TableValuesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory, TableValuesTrait;

    const STATUS_NEW = 'new';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_SUBSCRIBED = 'subscribed';

    const STATUS_ACTIVE = 'active';

    const STATUS_UNSUBSCRIBED = 'unsubscribed';

    const STATUS_BLACKLIST = 'blacklist';

    protected $table = 'customers';

    protected $fillable = [
        'status',
        'name',
        'password',
    ];

    public function platforms()
    {
        return $this->hasMany(CustomerPlatform::class);
    }

    public function status(): string
    {
        $css = [
            self::STATUS_INACTIVE => 'primary',
            self::STATUS_ACTIVE => 'success',
            self::STATUS_UNSUBSCRIBED => 'default',
            self::STATUS_BLACKLIST => 'dark',
        ];

        return '<div class="badge badge-'.$css[$this->status].' text-wrap">'.self::statusesAll()[$this->status].'</div>';
    }

    public static function statusesAll(): array
    {
        return [
            self::STATUS_INACTIVE => __('app.Status inactive'),
            self::STATUS_ACTIVE => __('app.Status active'),
            self::STATUS_UNSUBSCRIBED => __('app.Unsubscribed'),
            self::STATUS_BLACKLIST => 'Заблоковано',
        ];
    }

    public function fullName()
    {
        return "$this->first_name $this->last_name";
    }

    public function imgUrl()
    {
        if (is_file(storage_path('app').'/public/customer/'.$this->id.'/avatar.jpg')) {
            return '/storage/customer/'.$this->id.'/avatar.jpg';
        }

        return '/img/bot-2.png';
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

    public function isBlacklist()
    {
        return $this->status === self::STATUS_BLACKLIST;
    }
}
