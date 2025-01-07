<?php

namespace App\Models;

use App\Services\TableValuesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class CustomerPlatform extends Authenticatable
{
    use HasApiTokens, HasFactory, TableValuesTrait;

    const STATUS_NEW = 'new';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_ACTIVE = 'active';

    const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $table = 'customer_platforms';

    protected $fillable = [
        'customer_id', 'phone', 'platform_id', 'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function status()
    {
        $css = [
            self::STATUS_INACTIVE => 'primary',
            self::STATUS_ACTIVE => 'success',
            self::STATUS_UNSUBSCRIBED => 'default',
        ];

        return '<div class="badge badge-'.$css[$this->status].' text-wrap">'.$this->statusesAll()[$this->status].'</div>';
    }

    public function statusesAll()
    {
        return [
            self::STATUS_INACTIVE => __('app.Status inactive'),
            self::STATUS_ACTIVE => __('app.Status active'),
            self::STATUS_UNSUBSCRIBED => __('app.Unsubscribed'),
        ];
    }

    public function saveCustomer(?string $last_name = null, ?string $first_name = null): void
    {
        if (! $this->customer_id) {
            $customer = $this->customer()->create();
            $this->customer()->associate($customer);
            $this->save();
        }
        if ($last_name) {
            $customer->update(['last_name' => $last_name]);
        }
        if ($first_name) {
            $customer->update(['first_name' => $first_name]);
        }
    }
}
