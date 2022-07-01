<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Internal\Finance\ListResource;
use App\Http\Resources\Api\Internal\Finance\OverviewResource;
use App\Http\Resources\Api\Internal\Finance\DetailResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    private const STATUS_APPROVE = 'approve';
    private const STATUS_REQUEST = 'request';
    private const STATUS_LIST = [
        self::STATUS_APPROVE,
        self::STATUS_REQUEST,
    ];

    public function list(Request $request): JsonResponse
    {
        $rows = [];
        foreach (range(1, 10) as $i) {
            $s = self::STATUS_LIST[ mt_rand(0, 1) ];
            $d = $s == self::STATUS_APPROVE ? (mt_rand(1, 5) * 10000) : 0;

            $rows[] = [
                'id' => $i,
                'mitra' => sprintf('MTM-JKTI-%s', str_pad(mt_rand(10, 99999), 5, '0', STR_PAD_LEFT)),
                'status' => $s,
                'request_at' => Carbon::now()->addDay( mt_rand(1, 10) )->format('Y-m-d H:i:s'),
                'amount_request' => mt_rand(1, 10) * 10000,
                'amount_disbursement' => $d,
            ];
        }

        $result = [
            'list' => $rows,
            'page' => 0,
            'total_data' => 0,
            'total_page' => 0,
        ];

        return $this->jsonSuccess(new ListResource($result));
    }

    public function overview(Request $request): JsonResponse
    {
        $result = [
            'mitra_count' => mt_rand(1, 10),
            'request_count' => mt_rand(11, 99) * 100000,
        ];

        return $this->jsonSuccess(new OverviewResource($result));
    }

    public function detail(Request $request): JsonResponse
    {
        $s = self::STATUS_LIST[ mt_rand(0, 1) ];
        $d = $s == self::STATUS_APPROVE ? (mt_rand(1, 5) * 10000) : 0;

        $rows = [];
        foreach (range(1, 10) as $i) {
            $d = $s == self::STATUS_APPROVE ? (mt_rand(1, 5) * 10000) : 0;

            $rows[] = [
                'id' => $i,
                'receipt' => sprintf('TB-%s', str_pad(mt_rand(10, 99999), 5, '0', STR_PAD_LEFT)),
                'mitra' => sprintf('MTM-JKTI-%s', str_pad(mt_rand(10, 99999), 5, '0', STR_PAD_LEFT)),
                'total_amount' => mt_rand(5, 10) * 10000,
                'total_receive' => mt_rand(1, 4) * 10000,
            ];
        }

        $result = [
            'receipt_list' => $rows,
            'request' => [
                'id' => mt_rand(1, 99),
                'mitra' => sprintf('MTM-JKTI-%s', str_pad(mt_rand(10, 99999), 5, '0', STR_PAD_LEFT)),
                'status' => $s,
                'request_at' => Carbon::now()->addDay( mt_rand(1, 10) )->format('Y-m-d H:i:s'),
                'total_approved' => $d,
                'total_request' => mt_rand(1, 10) * 10000,
            ],
        ];

        return $this->jsonSuccess(new DetailResource($result));
    }
}
