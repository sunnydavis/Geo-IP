<?php

namespace {
    if (!class_exists('JsonException')) {
        class JsonException extends Exception
        {
        }
    }

    if (!defined('JSON_THROW_ON_ERROR')) {
        define('JSON_THROW_ON_ERROR', 4194304);
    }
}

namespace Ayesh\GeoIP {

    class GeoIPLookup {
        /**
         * @var string
         */
        private $data_dir;

        private $maps = [];

        public function __construct($data_dir) {
            $this->data_dir = rtrim($data_dir, '/\\');
        }

        public static function createFromDefaultDatabase() {
            return new static(__DIR__ . '/../../../ayesh/geo-ip-database/data');
        }

        public function lookup($ip_address) {
            $parts = explode('.', $ip_address, 2);

            if (isset($this->maps[$parts[0]])) {
                $map = $this->maps[$parts[0]];
            }
            else {
                $file = $this->data_dir . '/' . $parts[0] . '.json';
                $data = file_get_contents($file);
                $map = json_decode($data, true, 3, JSON_THROW_ON_ERROR);
                if ($map === false) {
                    throw new \JsonException(json_last_error());
                }
                $this->maps[$parts[0]] = $map;
            }

            $find = ip2long('0.' . $parts[1]);
            $last = null;
            foreach ($map as $long_val => $code) {
                if ($find >= $long_val) {
                    $last = $code;
                    continue;
                }
                return $last;
            }

            return $last;
        }
    }

}
