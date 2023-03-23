<?php

namespace App\Services\Tracker;

use App\Models\CodeLogable;

class CancelOrderTracker
{
    public static function CancelOrder($code, $req)
    {
        CodeLogable::create([
            'code_id' => $code->code_id,
            'code_logable_type' => $code->code_logable_type,
            'code_logable_id' => $code->code_logable_id,
            'type' => $code->type,
            'showable' => $code->showable,
            'status' => $req['package_status'],
            'description' => $req['status_description'],
        ]);
    }

    public static function cancelService($exist, $code, $cancel, $req)
    {
        if ($exist) {
            $cancel->type = $req['package_status'];
            $cancel->pickup_price = $req['pickup_price'];
            $cancel->save();
            $code->status = $req['package_status'];
            $code->description = $req['status_description'];
            $code->save();
        } else {
            $cancel_order = $req['cancel_order']['get_class'];
            $cancel_order->package_id = $req['cancel_order']['package']->id;
            $cancel_order->type = $req['package_status'];
            $cancel_order->pickup_price = $req['pickup_price'];
            $cancel_order->save();
            CodeLogable::create([
                'code_id' => $req['codelogable']['code_id'],
                'code_logable_type' => $req['codelogable']['code_logable_type'],
                'code_logable_id' => $req['codelogable']['code_logable_id'],
                'type' => $req['codelogable']['type'],
                'showable' => $req['codelogable']['showable'],
                'status' => $req['package_status'],
                'description' => $req['status_description'],
            ]);
        }
    }

    public static function PayThenClaimOrder($package, $log, $req)
    {
        CodeLogable::create([
            'code_id' => $package->code->id,
            'code_logable_type' => $log->code_logable_type,
            'code_logable_id' => $log->code_logable_id,
            'type' => $log->type,
            'showable' => $log->showable,
            'status' => $req['status'],
            'description' => $req['status_description'],
        ]);
    }
}
