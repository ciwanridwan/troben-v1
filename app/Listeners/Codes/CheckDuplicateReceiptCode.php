<?php

namespace App\Listeners\Codes;

use App\Events\Codes\CodeCreated;
use App\Models\Code;

class CheckDuplicateReceiptCode
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Codes\CodeCreated  $event
     * @return void
     */
    public function handle(CodeCreated $event)
    {
        if ($event instanceof CodeCreated) {
            $code = $event->code;
            // receipt code type
            if ($code->codeable_type == 'App\Models\Packages\Package') {
                $receiptCode = $code->content;

                $duplicateExist = Code::query()
                    ->where('content', $receiptCode)
                    ->where('codeable_type', 'App\Models\Packages\Package')
                    ->where('codeable_id', '!=', $code->codeable_id)
                    ->get();
                if ($duplicateExist->count()) {
                    $duplicateExist->each(function($r) {
                        $pre = Code::TYPE_RECEIPT;

                        // use current date
                        $pre .= substr(str_replace($pre, '', $r->content), 0, 6);

                        $last_order = Code::where('content', 'LIKE', $pre.'%')->orderBy('content', 'desc')->first();
                        $inc_number = $last_order ? substr($last_order->content, strlen($pre)) : 0;
                        $inc_number = (int) $inc_number;
                        $inc_number = $last_order ? $inc_number + 1 : $inc_number;

                        // assume 100.000/day
                        $inc_number = str_pad($inc_number, 5, '0', STR_PAD_LEFT);

                        $code = $pre.$inc_number;
                        
                        $r->content = $code;
                        $r->save();
                    });
                }
            }
        }
    }
}
