<?php

namespace App\Actions\Core;

use App\Broadcasting\User\PrivateChannel;
use App\Models\Deliveries\Delivery as DeliveriesDelivery;
use App\Models\Notifications\Notification;
use App\Models\Notifications\Template;
use App\Models\Packages\Package;
use App\Models\Partners\Performances\Delivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlaLevel
{
    /**
     * @var Template
     */
    protected Template $notification;

    public static function doSlaSetter()
    {
        $types = ['delivery', 'package'];
        $levels = [2, 3];
        foreach ($types as $t) {
            foreach ($levels as $l) {
                try {

                    // todo new privateChannel($user, $notif, $title)
                    DB::statement(self::query($t, $l));
                    // push notification
                    self::pushNotification($t, $l);
                } catch (\Exception $e) {
                    $msg = sprintf('SLA Err [%s] [%s]: ', $t, $l, $e->getMessage());
                    dd($msg);
                }
            }
        }

        DB::table('sp_call_log')->where('sp_name', 'SLA_WORKER')->update(['last_call' => Carbon::now()->format('Y-m-d H:i:s')]);
    }


    private static function query($type, $level)
    {
        $levelPrev = $level - 1;
        $table = null;
        $column = null;
        switch ($type) {
            case 'delivery':
                $table = "partner_delivery_performances";
                $column = "delivery_id";
                break;
            case 'package':
                $table = "partner_package_performances";
                $column = "package_id";
                break;
            default:
                throw new \Exception("Invalid type for SLA: $type [$level]");
                break;
        }

        if (!in_array($level, [2, 3])) {
            throw new \Exception("Invalid level for SLA: $type [$level]");
        }

        // $status = null;
        // switch ($level) {
        //     case 3:
        //         $status = 99;
        //         break;
        //     default:
        //         $status = 1;
        //         break;
        // }

        $q = "UPDATE %s t
                    SET level = %d,
                        deadline = deadline + interval '24' hour,
                        updated_at = NOW()
                    WHERE 1=1
                        AND level = %d
                        AND status = 1
                        AND reached_at IS NULL
                        AND deadline < NOW()
                        and not exists (
                            select 1
                            from %s
                            WHERE 1=1
                            AND level = %d
                            AND status = 1
                            AND %s = t.%s
                            AND partner_id  = t.partner_id
                        )";

        $q = sprintf($q, $table, $level, $levelPrev, $table, $level, $column, $column);

        return $q;
    }

    /**
     * Get FCM Token from each users
     */
    private static function pushNotification($type, $level)
    {
        $table = null;
        $column = null;
        switch ($type) {
            case 'delivery':
                $table = "partner_delivery_performances";
                $column = "delivery_id";
                break;
            case 'package':
                $table = "partner_package_performances";
                $column = "package_id";
                break;
            default:
                throw new \Exception("Invalid type for SLA: $type [$level]");
                break;
        }

        // $status = null;
        $message = null;
        switch ($level) {
            case 3:
                // $status = 99;
                $message = Notification::messageLevelTree();
                break;
            default:
                // $status = 1;
                $message = Notification::messageLevelTwo();
                break;
        }

        $q = self::tokenFcmQuery();
        $q = sprintf($q, $column, $column, $column, $table, $level, $column, $column);

        $query = collect(DB::select($q))->toArray();

        foreach ($query as $q) {
            $user = User::where('id', $q->user_id)->first();
            $notification = self::getTemplate($q->type, $level);
            $code = null;

            switch ($type) {
                case 'delivery':
                    $code = DeliveriesDelivery::where('id', $q->delivery_id)->first()->code->content;
                    break;
                case 'package':
                    $code = Package::where('id', $q->package_id)->first()->code->content;
                    break;
                default:
                    throw new \Exception("Invalid type for SLA: $type [$level]");
                    break;
            }

            $check = Notification::query()->where('notifiable_id', $user->id)->where('data->title', 'ilike', '%' . $code . '%')->whereIn('data->body', $message)->first();

            if (is_null($check)) {
                $push = new PrivateChannel($user, $notification, ['package_code' => $code]);
                Log::info('Push notification for level 2 and 3 has been sent', [$push]);
            }

            Log::info('Can not send push notification because already sent');
        }
    }

    private static function getTemplate($type, $level)
    {
        if ($level === 2) {
            switch ($type) {
                case Delivery::TYPE_DRIVER_DOORING:
                    $notification = Template::where('type', Template::TYPE_DRIVER_IMMEDIATELY_DELIVERY_OF_ITEM)->first();

                    return $notification;
                    break;
                case Delivery::TYPE_MB_DRIVER_TO_TRANSIT:
                    $notification = Template::where('type', Template::TYPE_DRIVER_SHOULD_DELIVERY_TO_WAREHOUSE)->first();

                    return $notification;
                    break;
                case Delivery::TYPE_MPW_WAREHOUSE_GOOD_RECEIVE:
                    $notification = Template::where('type', Template::TYPE_WAREHOUSE_IMMEDIATELY_GOOD_RECEIVE)->first();

                    return $notification;
                    break;
                case Delivery::TYPE_MPW_WAREHOUSE_REQUEST_TRANSPORTER:
                    $notification = Template::where('type', Template::TYPE_WAREHOUSE_IMMEDIATELY_REQUEST_TRANSPORTER)->first();

                    return $notification;
                    break;
                case Delivery::TYPE_MTAK_DRIVER_TO_WAREHOUSE:
                    $notification = Template::where('type', Template::TYPE_DRIVER_IMMEDIATELY_DELIVERY_TO_WAREHOUSE)->first();

                    return $notification;
                    break;
                case Delivery::TYPE_MTAK_OWNER_TO_DRIVER:
                    $notification = Template::where('type', Template::TYPE_OWNER_IMMEDIATELY_TAKE_ITEM)->first();

                    return $notification;
                    break;
                default:
                    throw new \Exception("Invalid type for Template: $type [$level]");
                    break;
            }
        } else {
            switch ($type) {
                case Delivery::TYPE_MTAK_OWNER_TO_DRIVER:
                    $notification = Template::where('type', Template::TYPE_OWNER_HAS_LATE)->first();

                    return $notification;
                    break;
                default:
                    $notification = Template::where('type', Template::TYPE_TIME_LIMIT_HAS_PASSED)->first();

                    return $notification;
                    break;
            }
        }
    }

    /**
     * Query to get fcm_token, and delivery_id or package_id
     */
    private static function tokenFcmQuery(): string
    {
        $query = "SELECT u2.fcm_token, u2.id user_id, pp.type, pp.%s
                    from users u2
                    left join (
                        select u.user_id, p.type, p.level, p.%s from userables u
                        left join (
                            select pdp.partner_id, pdp.%s, pdp.type, pdp.level
                            from %s pdp
                            where 1=1
                                and pdp.type is not null
                                and pdp.level = %d
                                and pdp.status = 1
                                and pdp.reached_at is null
                                and pdp.deadline < now()
                        ) p on u.userable_id = p.partner_id
                        where 1=1
                            and u.userable_type = 'App\Models\Partners\Partner'
                            and p.%s is not null
                        group by u.user_id, p.type, p.level, p.%s
                        order by u.user_id asc
                    ) pp on u2.id = pp.user_id
                    where 1=1
                        and pp.type is not null
                        and u2.fcm_token is not null";

        return $query;
    }
}
