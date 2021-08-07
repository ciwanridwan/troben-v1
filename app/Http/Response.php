<?php

namespace App\Http;

use ReflectionClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as LaravelResponse;

class Response implements Responsable
{
    // success group 0000-0099
    public const RC_SUCCESS = '0000';
    public const RC_CREATED = '0001';
    public const RC_UPDATED = '0002';
    public const RC_ACCEPTED = '0003';
    public const RC_ACCEPTED_NO_CONTENT = '0004';

    // client side fault. 0100 - 0199
    public const RC_INVALID_DATA = '0100';
    public const RC_RESOURCE_NOT_FOUND = '0101';
    public const RC_ROUTE_NOT_FOUND = '0102';
    public const RC_INVALID_PHONE_NUMBER = '0103';
    public const RC_OUT_OF_RANGE = '0104';

    // authentication / authorization related. 0200 - 0299
    public const RC_UNAUTHENTICATED = '0200';
    public const RC_INVALID_AUTHENTICATION_HEADER = '0201';
    public const RC_MISSING_AUTHENTICATION_HEADER = '0202';
    public const RC_ACCOUNT_NOT_VERIFIED = '0203';
    public const RC_UNAUTHORIZED = '0204';

    // one time password 0300 - 0399
    public const RC_MISMATCH_TOKEN_OWNERSHIP = '0301';
    public const RC_TOKEN_HAS_EXPIRED = '0302';
    public const RC_TOKEN_MISMATCH = '0303';
    public const RC_TOKEN_WAS_CLAIMED = '0304';
    public const RC_SMS_GATEWAY_WAS_BROKEN = '0305';

    // partner error
    public const RC_PARTNER_GEO_UNAVAILABLE = '0401';

    // patment gateway 0500-0599
    public const RC_FAILED_REGISTRATION_PAYMENT = '0501';
    public const RC_UNAVAILABLE_PAYMENT_GATEWAY = '0502';
    public const RC_PAYMENT_NOT_PAID = '0503';
    public const RC_FAILED_INQUIRY_PAYMENT = '0504';
    public const RC_PAYMENT_HAS_PAID = '505';

    // code logs 0600 - 0699
    public const RC_CODE_LOG_UNAVAILABLE = '0601';

    // server side faults. 0900 - 0999
    public const RC_SERVER_IN_MAINTENANCE = '0901';
    public const RC_DATABASE_ERROR = '0902';
    public const RC_OTHER = '0999';

    /**
     * Response Code.
     *
     * @var string
     */
    public string $code;

    /**
     * Response data.
     *
     * @var mixed
     */
    public $data;

    /**
     * Response constructor.
     *
     * @param string $code
     * @param $data
     */
    public function __construct(string $code, $data = [])
    {
        $this->code = $code;
        $this->data = $data;
    }

    /**
     * Get HttpCode Mapper.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function httpCodeMapper(): Collection
    {
        $mapper = [
            LaravelResponse::HTTP_OK => [
                self::RC_SUCCESS,
                self::RC_UPDATED,
            ],
            LaravelResponse::HTTP_NO_CONTENT => [
                self::RC_ACCEPTED_NO_CONTENT,
            ],
            LaravelResponse::HTTP_ACCEPTED => [
                self::RC_ACCEPTED,
            ],
            LaravelResponse::HTTP_CREATED => [
                self::RC_CREATED,
            ],
            LaravelResponse::HTTP_NOT_FOUND => [
                self::RC_RESOURCE_NOT_FOUND,
                self::RC_ROUTE_NOT_FOUND,
            ],
            LaravelResponse::HTTP_UNPROCESSABLE_ENTITY => [
                self::RC_INVALID_DATA,
                self::RC_INVALID_PHONE_NUMBER,
                self::RC_TOKEN_HAS_EXPIRED,
                self::RC_TOKEN_MISMATCH,
                self::RC_TOKEN_WAS_CLAIMED,
                self::RC_OUT_OF_RANGE,
                self::RC_PARTNER_GEO_UNAVAILABLE
            ],
            LaravelResponse::HTTP_PRECONDITION_FAILED => [
                self::RC_MISSING_AUTHENTICATION_HEADER,
                self::RC_INVALID_AUTHENTICATION_HEADER,
                self::RC_MISMATCH_TOKEN_OWNERSHIP,
                self::RC_FAILED_REGISTRATION_PAYMENT,
                self::RC_CODE_LOG_UNAVAILABLE,
                self::RC_PAYMENT_NOT_PAID,
                self::RC_FAILED_INQUIRY_PAYMENT,
                self::RC_PAYMENT_HAS_PAID,
            ],
            LaravelResponse::HTTP_UNAUTHORIZED => [
                self::RC_UNAUTHENTICATED,
                self::RC_ACCOUNT_NOT_VERIFIED,
                self::RC_UNAUTHORIZED,
            ],
            LaravelResponse::HTTP_SERVICE_UNAVAILABLE => [
                self::RC_SERVER_IN_MAINTENANCE,
                self::RC_UNAVAILABLE_PAYMENT_GATEWAY,
            ],
            LaravelResponse::HTTP_INTERNAL_SERVER_ERROR => [
                self::RC_DATABASE_ERROR,
                self::RC_OTHER,
            ],
        ];

        return collect($mapper);
    }

    /**
     * Get error codes.
     *
     * @return string[]
     */
    public static function getErrorCodes(): array
    {
        $class = new ReflectionClass(__CLASS__);

        return array_flip($class->getConstants());
    }

    /**
     * Get Response data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function getResponseData(Request $request): array
    {
        $responseData = [
            'code' => $this->code,
            'error' => (int) $this->code >= 100 ? $this->resolveErrorCode() : null,
            'message' => $this->resolveMessage(),
        ];

        if ($this->data instanceof LengthAwarePaginator) {
            $responseData = array_merge($responseData, $this->data->toArray());
        } elseif ($this->data instanceof Model) {
            $responseData['data'] = $this->data->toArray();
        } elseif ($this->data instanceof JsonResource) {
            if ($this->data->resource instanceof LengthAwarePaginator) {
                $responseData = array_merge($responseData, $this->data->resource->toArray());
            }

            $responseData['data'] = $this->data->toArray($request);
        } elseif (Arr::has($this->data, 'resource')) {
            if ($this->data['resource']->resource instanceof LengthAwarePaginator) {
                $responseData = array_merge($responseData, $this->data['resource']->resource->toArray());
            }
            $responseData['data'] = [];
            $responseData['data'] = $this->data['resource']->toArray($request);
            $responseData['data_extra'] = Arr::except($this->data, 'resource');
        } else {
            if ($responseData['code'] === self::RC_INVALID_DATA) {
                foreach ($this->data as $key => $value) {
                    if (is_array($value)) {
                        $this->data[$key] = Arr::first($value);
                    }
                }
            }
            $responseData['data'] = $this->data;
        }

        return $responseData;
    }

    /**
     * Get JSON representative.
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function json(?Request $request = null): JsonResponse
    {
        return response()->json($this->getResponseData($request ?? request()), $this->resolveHttpCode());
    }

    /** {@inheritdoc} */
    public function toResponse($request)
    {
        if ($request->expectsJson()) {
            return $this->json($request);
        }

        return new LaravelResponse(json_encode($this->getResponseData($request)), $this->resolveHttpCode());
    }

    /**
     * resolve error code.
     *
     * @return string
     */
    public function resolveErrorCode(): string
    {
        return str_replace('RC_', 'ERR_', self::getErrorCodes()[$this->code]);
    }

    /**
     * Resolve message.
     *
     * @return string
     */
    public function resolveMessage(): string
    {
        return trim(Str::ucfirst(Str::lower(str_replace(['RC', '_'], ' ', self::getErrorCodes()[$this->code]))));
    }

    /**
     * Resolve Http Code.
     *
     * @return int
     */
    public function resolveHttpCode(): int
    {
        return self::httpCodeMapper()->search(fn ($item) => in_array($this->code, $item));
    }
}
