<?php

namespace App\Services\Chatbox;

use GuzzleHttp\Client;

class Chatbox
{
    public static function createDriverChatbox($param)
    {
        $client = new Client();
        $url = config('services.api_v2.create_chat_room');
        $response = $client->request('POST', $url, [
            'form_params' => [
                'type' => $param['type'],
                'participant_id' => $param['participant_id'],
                'customer_id' => $param['customer_id']
            ]
        ]);
        return $response;
    }

    public static function endSessionDriverChatbox($param)
    {
        $client = new Client();
        $url = config('services.api_v2.end-session').$param['room_id'];
        $response = $client->request('POST', $url);
        return $response;
    }
}
