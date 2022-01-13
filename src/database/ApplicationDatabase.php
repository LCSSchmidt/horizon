<?php

namespace Horizon\Database;

use Horizon\Database\Database;

class ApplicationDatabase extends Database {
    private $tableName = 'application_log';

    public function create(array $data) {
        $params = $this->createBindParams($data);
        $query = "INSERT INTO $this->tableName SET $params";

        $this->prepareSql($query)->bindValues($data)->insert();
    }
}