<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Send any success response
     * @param   array|object    $payload
     * @param   string          $message
     * @param   integer         $statusCode
     */
    public function success($payload,$message,$statusCode = 200 )
    {
        return response()->json([
            'success' => true,
            'code' => $statusCode,
            'message' => $message,
            'data'   => ['payload' => $payload],
            'locale' => app()->getLocale(),
        ], $statusCode, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }
    /**
     * Send any success response with meta
     * @param   array|object    $payload
     * @param   string          $meta
     * @param   string          $message
     * @param   integer         $statusCode
     */
    public function successWithMeta($payload,$meta,$message,$statusCode = 200 )
    {
        return response()->json([
            'success' => true,
            'code' => $statusCode,
            'message' => $message,
            'data'   => ['payload' => $payload],
            'meta'   => $meta,
            'locale' => app()->getLocale(),
        ], $statusCode, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }
    /**
     * Send any error response
     *
     * @param   string          $message
     * @param   integer         $statusCode
     * @param   string          $errorCode
     *
     */
    public function error($message, $statusCode = 500 ,$errorCode=0)
    {
        return response()->json([
            'success' => false,
            'code' => $statusCode,
            'message' => $message,
            'data'=> ["payload" => null ],
            'locale' => app()->getLocale(),
            'error_code' => $errorCode,

        ], $statusCode, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }
    /**
     * Get error details by code
     *
     * @param string $code
     * @return   array $errorDetails
     */
    public static function getErrorByCode($code)
    {
        switch($code){
            case 'D001' :
                return [
                    'code' => $code,
                    'message' =>'Resource does not exist',
                    'service' => 'Dashboard Service',
                    'function'=> 'register',
                    'location'=> 'App\Exceptions\Handler'
                ];
                break;
            default :
                abort(404);
        }
    }
}
