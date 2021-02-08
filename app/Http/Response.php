<?php

namespace App\Http;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as LaravelResponse;

class Response implements Responsable
{
    // success group 0000-0099
    const RC_SUCCESS = '0000';
    const RC_CREATED = '0001';
    const RC_ACCEPTED = '0002';
    const RC_ACCEPTED_NO_CONTENT = '0003';

    // client side fault. 0100 - 0199
    const RC_INVALID_DATA = '0100';
    const RC_RESOURCE_NOT_FOUND = '0101';
    const RC_ROUTE_NOT_FOUND = '0102';
    const RC_INVALID_PHONE_NUMBER = '0103';

    // authentication / authorization related. 0200 - 0299
    const RC_UNAUTHENTICATED = '0200';
    const RC_INVALID_AUTHENTICATION_HEADER = '0201';
    const RC_MISSING_AUTHENTICATION_HEADER = '0202';
    const RC_ACCOUNT_NOT_VERIFIED = '0203';

    // one time password 0300 - 0399
    const RC_MISMATCH_TOKEN_OWNERSHIP = '0301';
    const RC_TOKEN_HAS_EXPIRED = '0302';
    const RC_TOKEN_MISMATCH = '0303';

    // server side faults. 0900 - 0999
    const RC_SERVER_IN_MAINTENANCE = '0901';
    const RC_DATABASE_ERROR = '0902';
    const RC_OTHER = '0999';

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
            ],
            LaravelResponse::HTTP_PRECONDITION_FAILED => [
                self::RC_MISSING_AUTHENTICATION_HEADER,
                self::RC_INVALID_AUTHENTICATION_HEADER,
                self::RC_MISMATCH_TOKEN_OWNERSHIP,
            ],
            LaravelResponse::HTTP_UNAUTHORIZED => [
                self::RC_UNAUTHENTICATED,
                self::RC_ACCOUNT_NOT_VERIFIED,
            ],
            LaravelResponse::HTTP_SERVICE_UNAVAILABLE => [
                self::RC_SERVER_IN_MAINTENANCE,
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
        } else {
            $responseData['data'] = $this->data;
        }

        return $responseData;
    }

    /** {@inheritdoc} */
    public function toResponse($request)
    {
        $responseData = $this->getResponseData($request);

        if ($request->expectsJson()) {
            return response()->json($responseData, $this->resolveHttpCode());
        }

        return new LaravelResponse(json_encode($responseData), $this->resolveHttpCode());
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
