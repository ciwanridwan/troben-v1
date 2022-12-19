<?php

namespace App\Actions\Core;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlaLevel
{
    public static function doSlaSetter()
    {
        $types = ['delivery', 'package'];
        $levels = [2, 3];
        foreach ($types as $t) {
            foreach ($levels as $l) {
                try {
                    // select semua delivery id yg due date

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

        if (! in_array($level, [2, 3])) {
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
}
