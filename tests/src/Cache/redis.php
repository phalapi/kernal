<?php

if (!class_exists('Redis')) {

    class Redis {

        public function __call($method, $params) {
            echo 'Redis::' . $method . '() with: ', json_encode($params), " ... \n";
        }

    }
}

