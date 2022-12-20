<?php

namespace App\Actions\Core;

use App\Models\Notifications\Template;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                    // select semua delivery id yg due date

                    // todo new privateChannel($user, $notif, $title)
                    DB::statement(self::query($t, $l));

                    // loop and blast ke masing2 user, berdasarkan
                } catch (\Exception $e) {
                    $msg = sprintf('SLA Err [%s] [%s]: ', $t, $l, $e->getMessage());
                    dd($msg);
                }
            }
            // self::queryPenalty($t);
        }


        DB::table('sp_call_log')->where('sp_name', 'SLA_WORKER')->update(['last_call' => Carbon::now()->format('Y-m-d H:i:s')]);
    }

    // private static function queryPenalty($type)
    // {
    //     $table = null;
    //     switch ($type) {
    //         case 'delivery':
    //             $table = "partner_delivery_performances";
    //             break;
    //         case 'package':
    //             $table = "partner_package_performances";
    //             break;
    //         default:
    //             throw new \Exception("Invalid type for SLA: $type");
    //             break;
    //     }

    //     $q = "UPDATE %s t
    //     SET status = 10,
    //         updated_at = NOW()
    //     WHERE 1=1
    //         AND level = 3
    //         AND status = 1
    //         AND reached_at IS NULL
    //         AND deadline < NOW()
    //         and not exists (
    //             select 1
    //             from %s
    //             WHERE 1=1
    //             AND level = 3
    //             AND status = 10
    //             AND reached_at is null
    //             and deadline < now()
    //         )";

    //     $q = sprintf($q, $table, $table);
    //     return $q;
    // }

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
    private function getFcmToken(): string
    {
        $q = "SELECT fcm_token
        from users u2
        where
        fcm_token is not null
        and id in (
            select user_id
            from userables u
                where 1=1
                and userable_type = 'App\Models\Partners\Partner'
                and userable_id  in (
                        select partner_id
                        from partner_delivery_performances t
                        WHERE 1=1
                            AND level = 2
                            AND status = 1
                            AND reached_at IS NULL
                            AND deadline < NOW()
                            and not exists (
                                select 1
                                from partner_delivery_performances
                                WHERE 1=1
                                AND level = 3
                                AND status = 1
                                AND delivery_id  = t.delivery_id
                                AND partner_id  = t.partner_id
                            )
                ) group by user_id
        )";

        return $q;
    }

    /**
     * Set notifitication for each sla
     */
    private function setNotification(): void
    {
        $this->notification  = Template::where('type', Template::TYPE_TIME_LIMIT_HAS_PASSED)->first();
    }
}
