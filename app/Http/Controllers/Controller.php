<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static function dataResponse($message, $data = null, $status = "success", $statusCode = null): JsonResponse
    {
        if (!$statusCode) {
            if ($status == "error") {
                $statusCode = Response::HTTP_BAD_REQUEST;
            } else {
                $statusCode = Response::HTTP_OK;
            }
        }

        return new JsonResponse([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
