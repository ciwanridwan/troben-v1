<?php

namespace App\Http\Controllers\Api;

use App\Http\Response;
use App\Models\Service;
use App\Exceptions\Error;
use App\Exceptions\InvalidDataException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Jobs\Services\CreateNewService;
use App\Jobs\Services\UpdateExistingService;

class ServiceController extends Controller
{
    /**
     * Get Service List
     * Route Path       : {API_DOMAIN}/service
     * Route Name       : api.service
     * Route Method     : GET.
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return $this->jsonSuccess(ServiceResource::collection(Service::all()));
    }

    /**
     *
     * Get Service by Code
     * Route Path       : {API_DOMAIN}/service
     * Route Name       : api.service
     * Route Method     : GET.
     *
     * @param string $service
     *
     * @return JsonResponse
     */
    public function show($service): JsonResponse
    {
        $service = Service::find($service);
        throw_if($service === null, InvalidDataException::make(Response::RC_INVALID_DATA));

        return $this->jsonSuccess(ServiceResource::make($service));
    }

    /**
     *
     * Create New Service
     * Route Path       : {API_DOMAIN}/service
     * Route Name       : api.service
     * Route Method     : POST.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function creation(Request $request): JsonResponse
    {
        $job = new CreateNewService($request->all());
        $response = $this->dispatch($job);

        throw_if(! $response, Error::make(Response::RC_DATABASE_ERROR));

        return (new Response(Response::RC_CREATED, ServiceResource::make($job->service)))->json();
    }

    /**
     *
     * Update Service
     * Route Path       : {API_DOMAIN}/service
     * Route Name       : api.service
     * Route Method     : PUT.
     *
     * @param Service $service
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update($service, Request $request): JsonResponse
    {
        $service = Service::find($service);
        throw_if($service === null, InvalidDataException::make(Response::RC_INVALID_DATA));

        $job = new UpdateExistingService($service, $request->all());
        $response = $this->dispatch($job);

        throw_if(! $response, Error::make(Response::RC_DATABASE_ERROR));

        return (new Response(Response::RC_UPDATED, ServiceResource::make($job->service)))->json();
    }
}
