<?php

namespace Horizon;

use \Horizon\Service\RequestService;
use \Horizon\log\OperationTypeEnum;
use Horizon\Service\ApplicationService;

class Log {

    /*
        User id
        Sys
        Req date
        HTTP status code
        Response size
        Wallclock Time
    */

    /*
        LOG LEVELS:

        TRACE;
        DEBUG;
        INFO;
        NOTICE;
        WARN;
        ERROR;
        FATAL.
    */

    static float $startTime;

    /**
     * Request Log table Id created.
     * @var reqLogId table id.
     */
    private static int $reqLogId;

    function __construct() {
        Log::$startTime = microtime(true);
    }

    /* ############################ LOG ACTIONS ############################ */

    private static function getRequestData() {
        $data = [];
        $clientAddres = $_SERVER['REMOTE_ADDR'];
        $reqQueryString = "{$_SERVER['REQUEST_METHOD']} / {$_SERVER['SERVER_PROTOCOL']}";
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $hostName = $_SERVER['HTTP_HOST'];
        $xff = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : NULL;
        $uri = $_SERVER['REQUEST_URI'];

        $data['client_address'] = $clientAddres;
        $data['request_querystring'] = $reqQueryString;
        $data['user_agent'] = $userAgent;
        $data['host_name'] = $hostName;
        $data['xff'] = $xff;
        $data['uri'] = $uri;

        return $data;
    }

    public static function logRequest() {
        $requestLogService = new RequestService();
        $data = self::getRequestData();

        self::$reqLogId = $requestLogService->log($data);
    }

    public static function logINFO(string $message, OperationTypeEnum $type = null) {
        $appLogService = new ApplicationService();

        if(empty(self::$reqLogId)) throw new \Exception('Resquest log not inserted.');
        $data = [
            "level" => 'INFO',
            "message" => $message,
            "request_log_id" => self::$reqLogId
        ];

        $appLogService->log($data);
    }

    public static function logNOTICE(string $message, array $info) {
        $appLogService = new ApplicationService();

        if(empty(self::$reqLogId)) throw new \Exception('Resquest log not inserted.');
        $data = [
            "level" => 'WARN',
            "message" => $message,
            "line" => $info['line'],
            "file" => $info['file'],
            "request_log_id" => self::$reqLogId
        ];

        $appLogService->log($data);
    }

    public static function registerNOTICEHandler() {
        try {
            set_error_handler(
                function ($number, $message, $file, $line) {
                    $data = [
                        "line" => $line,
                        "file" => $file
                    ];

                    self::logNOTICE($message, $data);
                }, 
            E_NOTICE);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public static function logWARN(string $message, array $info) {
        $appLogService = new ApplicationService();

        if(empty(self::$reqLogId)) throw new \Exception('Resquest log not inserted.');
        $data = [
            "level" => 'WARN',
            "message" => $message,
            "line" => $info['line'],
            "file" => $info['file'],
            "request_log_id" => self::$reqLogId
        ];

        $appLogService->log($data);
    }

    public static function registerWARNHandler() {
        try {
            set_error_handler(
                function ($number, $message, $file, $line) {
                    $data = [
                        "line" => $line,
                        "file" => $file
                    ];

                    self::logWARN($message, $data);
                }, 
            E_WARNING);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public static function logERROR(string $message) {
        $appLogService = new ApplicationService();

        if(empty(self::$reqLogId)) throw new \Exception('Resquest log not inserted.');
        $data = [
            "level" => 'ERROR',
            "message" => $message,
            "request_log_id" => self::$reqLogId
        ];

        $appLogService->log($data);
    }

    public static function logFATAL(string $message, array $info) {
        $appLogService = new ApplicationService();

        if(empty(self::$reqLogId)) throw new \Exception('Resquest log not inserted.');
        $data = [
            "level" => 'FATAL',
            "message" => $message,
            "line" => $info['line'],
            "file" => $info['file'],
            "request_log_id" => self::$reqLogId
        ];

        $appLogService->log($data);
    }

    public static function registerFATALErrorHandler() {
        try {
            register_shutdown_function(
                function () {
                    $error = error_get_last();
                    self::logFATAL($error['message'], $error);
                }
            );
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}