<?php

namespace Horizon\Service;

class ApplicationService {

    function __construct() {
        $this->db = new \Horizon\Database\ApplicationDatabase();
    }

    public function log($data) {
        $this->db->create($data);
    }
}