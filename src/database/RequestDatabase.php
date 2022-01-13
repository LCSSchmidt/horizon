<?php

namespace Horizon\Database;

use Horizon\Database\Database;

class RequestDatabase extends Database {
    private $tableName = 'request_log';

    public function create(array $data) {
        $params = $this->createBindParams($data);
        $query = "INSERT INTO $this->tableName SET $params";

        return $this->prepareSql($query)->bindValues($data)->insert()->lastInsertId();
    }
}