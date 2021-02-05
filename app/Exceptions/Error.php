<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use App\Http\Response;
use Illuminate\Contracts\Support\Responsable;

class Error extends Exception implements Responsable
{
    /**
     * Response instance.
     *
     * @var \App\Http\Response
     */
    protected Response $response;

    /**
     * Exception constructor.
     *
     * @param                 $code
     * @param array           $data
     * @param \Throwable|null $previous
     */
    public function __construct($code, $data = [], Throwable $previous = null)
    {
        $this->response = new Response($code, $data);

        parent::__construct($this->response->resolveMessage(), $code, $previous);
    }

    /** {@inheritdoc} */
    public function toResponse($request)
    {
        return $this->response->toResponse($request);
    }
}
