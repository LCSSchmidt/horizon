<?php

namespace Horizon\Service;

class RequestService {

    function __construct() {
        $this->db = new \Horizon\Database\RequestDatabase();
    }

    public function log($data) {
        return $this->db->create($data);
    }
}