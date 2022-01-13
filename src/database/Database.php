<?php

namespace Horizon\Database;

class Database
{

    /**
     * Database connection.
     * @var db PDO Connection.
     */
    private static $db = null;

    /**
     * @var sql Actual PDOStatement object.
     */
    private $sql = null;

    function __construct() {
        if(self::$db === null) {
            $conInfo = json_decode(getenv("database"), true);

            self::$db = $this->getConnect($conInfo);
        }
    }

    private function getConnect(array $conInfo) {
        $pdo = new \PDO("mysql:host={$conInfo['host']}:{$conInfo['port']};dbname={$conInfo['name']}", "{$conInfo['username']}", "{$conInfo['password']}");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    private function replaceDotOnBindKey(string $key) {
        if (str_contains($key, '.')) {
            return str_replace('.', '_', $key);
        }
        return $key;
    }

    public function createBindParams(array $array, array $ignorekey = []) {
        $arrangeGraveAccentOnBindKey = function ($key) {
            if (!str_contains($key, '.')) {
                return "`$key`";
            } else {
                return (str_replace('.', '.`', $key) . '`');
            }
        };

        $query = '';
        foreach ($array as $key => $value) {
            if (in_array($key, $ignorekey) == false) {
                $query .= ($arrangeGraveAccentOnBindKey($key) . ' = :' . $this->replaceDotOnBindKey($key) . ', ');
            }
        }

        $query = substr($query, 0, -2) . ' '; //remove os dois ultimos caracters, uma virgula e um espaco ", " e depois aciociona um espaço " "

        return $query;
    }

    public function prepareSql($query) {
        $this->sql = self::$db->prepare($query);
        return $this;
    }

    public function bindValues(array $params) {
        foreach ($params as $key => $value) {
            $this->sql->bindValue(':' . $this->replaceDotOnBindKey($key), $value);
        }

        return $this;
    }

    protected function lastInsertId() {
        return self::$db->lastInsertId();
    }

    public function insert() {
        $this->sql->execute();
        $this->sql = null;

        return $this;
    }
}
