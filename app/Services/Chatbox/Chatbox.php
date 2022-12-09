<?php

namespace App\Services\Chatbox;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class Chatbox
{
    public static function createDriverChatbox($param)
    {
        $client = new Client();
        $url = config('services.api_v2.create_chat_room');
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer '.$param['token']
            ],
            'form_params' => [
                'type' => $param['type'],
                'participant_id' => $param['participant_id'],
                'customer_id' => $param['customer_id'],
                'package_id' => $param['package_id'],
                'product' => $param['product']
            ]
        ]);
        return $response;
    }

    public static function endSessionDriverChatbox($param)
    {
        $relateId = DB::table('cb_chat_participants as p')
            ->join('cb_chat_metas as m', 'p.chat_room_id', '=', 'm.chat_room_id')
            ->join('cb_chat_rooms as r', 'm.chat_room_id', '=', 'r.id')
            ->whereIn('participantable_id', [$param['customer_id'],$param['participant_id']])
            ->where('m.value', '=', 'ontheway')
            ->where('r.type', '!=', 'live_chat')
            ->get()->pluck('chat_room_id')->toArray();
        $unique = [];
        $chat_room_id = [];
        foreach ($relateId as $value) {
            if (! in_array($value, $unique)) {
                $unique[] = $value;
            } else {
                $chat_room_id = $value;
            }
        }
        if ($chat_room_id) {
            $client = new Client();
            $url = config('services.api_v2.end-session').$chat_room_id;
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$param['token']
                ]
            ]);
            return $response;
        }
    }
}
