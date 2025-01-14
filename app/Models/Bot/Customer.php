<?php

namespace App\Models\Bot;

use App\Bot\TmAdmin;
use App\Bot\TmWaiter;
use App\Models\Project\CustomerEstablishment;
use App\Models\Project\Establishment;
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

    const ROLE_ADMIN = 'admin';
    const ROLE_WAITER = 'waiter';

    protected $table = 'bot_customers';

    protected $fillable = [
        'phone',
        'platform_id',
        'role',
        'status',
        'name',
    ];

    public function establishment()
    {
        return $this->belongsToMany(Establishment::class, CustomerEstablishment::tableName());
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
        $name = "$this->first_name $this->last_name";
        return $name != " " ? $name : $this->name;
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

    public static function getModel($customer)
    {
        if (!$dbCustomer = self::where('external_id', $customer->id)->first()) {
            $dbCustomer = new self();
            $dbCustomer->external_id = $customer->id;
            $dbCustomer->role = $customer->role;
            $dbCustomer->first_name = $customer->first_name ?? null;
            $dbCustomer->last_name = $customer->last_name ?? null;
            $dbCustomer->phone = $customer->phone;
            $dbCustomer->save();
        }
        return $dbCustomer;
    }

    public function getBot()
    {
        $bot = match ($this->role) {
            self::ROLE_ADMIN => new TmAdmin(),
            self::ROLE_WAITER => new TmWaiter(),
        };
        $bot->init->currentCustomer($this);
        return $bot;
    }
}
