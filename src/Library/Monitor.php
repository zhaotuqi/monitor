<?php

namespace App\Libraries;

class Monitor {

    // http://php.net/manual/en/transports.unix.php
    const MONITOR_SERVER_UDS = "udg:///var/tmp/monitor.sock";
    static $error = "";

    // 打点增量，monitor-server会自动累加
    public static function inc($_key, $_value = 1) {
        $value = intval($_value);
        if (0 == strlen($_key) || $value < 0) {
            self::$error = "key不能为空，value不能小于0";
            return false;
        }

        $hd = self::getHandle();
        if (false === $hd) {
            return false;
        }

        $req = self::getIncReq($_key, $value);
        return self::write($hd, $req);
    }

    // 打点耗时，monitor-server会统计p99等
    public static function cost($_key, $_value) {
        $value = intval($_value);
        if (0 == strlen($_key) || $value < 0) {
            self::$error = "key不能为空，value不能小于0";
            return false;
        }
        $hd = self::getHandle();
        if (false === $hd) {
            return false;
        }

        $req = self::getCostReq($_key, $value);
        return self::write($hd, $req);
    }


// PRIVATE METHODS BELOW

    private static function getHandle() {
        $sock = @stream_socket_client(self::MONITOR_SERVER_UDS, $errno, $errstr, 0,
            STREAM_CLIENT_CONNECT|STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_PERSISTENT);

        if (false === $sock) {
            self::$error = "stream_socket_client failed, errno=$errno, error=$errstr";
            return false;
        }
        if (false === stream_set_blocking($sock, false)) {
            self::$error = "stream_set_blocking failed";
            return false;
        }
        if (false === stream_set_timeout($sock, 0, 50)) {   // 0.05 ms
            self::$error = "stream_set_timeout failed";
            return false;
        }
        return $sock;
    }

    private static function write($hd, $req) {
//        $ret = fwrite($hd, $req);
        $ret = @fwrite($hd, $req);
        if (false === $ret) {
            self::$error = "write failed";
            return false;
        }

        return true;
    }

    private static function getIncReq($_key, $_value) {
        $req = array(
            "type" => "inc",
            "key" => $_key,
            "value" => $_value,
        );
        return json_encode($req, JSON_UNESCAPED_UNICODE);
    }

    private static function getCostReq($_key, $_value) {
        $req = array(
            "type" => "cost",
            "key" => $_key,
            "value" => $_value,
        );
        return json_encode($req, JSON_UNESCAPED_UNICODE);
    }

}
