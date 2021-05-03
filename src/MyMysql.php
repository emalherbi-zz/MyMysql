<?php

/*
 * Eduardo Malherbi Martins (http://emalherbi.com/)
 * Copyright @emm
 * Full Stack Web Developer.
 */

namespace MyMysql;

use PDO;
use stdClass;

set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

class MyMysql
{
    private $ini = null;
    private $dirlog = null;
    private $db = null;

    public function __construct($ini = '', $dirlog = '')
    {
        $this->ini = $ini;
        $this->dirlog = empty($dirlog) ? realpath(dirname(__FILE__)) : $dirlog;
        $this->db = null;

        $this->logger('MyMysql | method: __construct');

        if (empty($this->ini)) {
            $err = "It's not possible to connect to the database because the parameters entered are invalid.";
            $log = 'Error. MyMysql | method: __construct.';

            echo '<pre>';
            echo print_r($log);
            echo print_r($err);
            echo '</pre>';
            die();
        }

        $this->connection();
    }

    public function __destruct()
    {
        $this->logger('MyMysql | method: __destruct');

        $this->disconnect();
    }

    /* connnect */

    public function connection()
    {
        $this->logger('MyMysql | method: connection');

        if (!empty($this->db)) {
            return $this->db;
        }

        $this->db = new PDO('mysql:host='.$this->ini['DB_HOST'].';dbname='.$this->ini['DB_NAME'], $this->ini['DB_USER'], $this->ini['DB_PASS'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

        return $this->db;
    }

    public function disconnect()
    {
        $this->logger('MyMysql | method: disconnect');
        $this->db = null;

        return !$this->isConnect();
    }

    public function isConnect()
    {
        $this->logger('MyMysql | method: isConnect');

        return empty($this->db) ? false : true;
    }

    /* fetch */

    public function fetchRow($table, $where = array(), $orderBy = '')
    {
        $this->logger('MyMysql | method: fetchRow');

        $sql = " SELECT * FROM $table WHERE 1 = 1 ";
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $key = str_replace(':', '', $key);
                $sql .= " AND $key = :$key ";
            }
        }
        $sql .= $orderBy;
        $log = $sql;

        $stmt = $this->db->prepare($sql);
        if (!empty($where)) {
            foreach ($where as $key => &$value) {
                $key = str_replace(':', '', $key);
                $stmt->bindParam($key, $value);
                $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
            }
        }

        $this->logger($log);
        $exec = $stmt->execute();

        return (!$exec) ? false : $stmt->fetchObject();
    }

    public function fetchRow2($sql, $where = array())
    {
        $this->logger('MyMysql | method: fetchRow2');

        $log = $sql;

        $stmt = $this->db->prepare($sql);
        if (!empty($where)) {
            foreach ($where as $key => &$value) {
                $key = str_replace(':', '', $key);
                $stmt->bindParam($key, $value);
                $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
            }
        }

        $this->logger($log);
        $exec = $stmt->execute();

        return (!$exec) ? false : $stmt->fetchObject();
    }

    public function fetchAll($table, $where = array(), $orderBy = '')
    {
        $this->logger('MyMysql | method: fetchAll');

        $sql = " SELECT * FROM $table WHERE 1 = 1 ";
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $key = str_replace(':', '', $key);
                $sql .= " AND $key = :$key ";
            }
        }
        $sql .= $orderBy;
        $log = $sql;

        $stmt = $this->db->prepare($sql);
        if (!empty($where)) {
            foreach ($where as $key => &$value) {
                $key = str_replace(':', '', $key);
                $stmt->bindParam($key, $value);
                $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
            }
        }

        $this->logger($log);
        $exec = $stmt->execute();

        return (!$exec) ? false : $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetchAll2($sql, $where = array())
    {
        $this->logger('MyMysql | method: fetchAll2');

        $log = $sql;

        $conn = $this->db;
        $stmt = $conn->prepare('SET SQL_BIG_SELECTS = 1');
        $stmt->execute();
        $stmt = $conn->prepare($sql);
        if (!empty($where)) {
            foreach ($where as $key => &$value) {
                $key = str_replace(':', '', $key);
                $stmt->bindParam($key, $value);
                $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
            }
        }

        $this->logger($log);
        $exec = $stmt->execute();

        return (!$exec) ? false : $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /*
     * RETURN ARRAY OF SELECT.
     */
    public function fetchAll3($sql, $where = array())
    {
        $this->logger('MyMysql | method: fetchAll3');

        $log = $sql;

        $conn = $this->db;
        $stmt = $conn->prepare('SET SQL_BIG_SELECTS = 1');
        $stmt->execute();
        $stmt = $conn->prepare($sql);
        if (!empty($where)) {
            foreach ($where as $key => &$value) {
                $key = str_replace(':', '', $key);
                $stmt->bindParam($key, $value);
                $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
            }
        }

        $this->logger($log);
        $exec = $stmt->execute();
        $results = array();

        do {
            $rowset = $stmt->fetchAll(PDO::FETCH_OBJ);
            $results[] = $rowset ?? array();
        } while ($stmt->nextRowset());

        return $results;
    }

    public function insert($table, $item)
    {
        $this->logger('MyMysql | method: insert');

        $this->deleteColumnFromSqlIfNotExist($table, $item);

        $sql = '';
        $sql .= " INSERT INTO $table (";
        foreach ($item as $key => $value) {
            $sql .= "$key,";
        }
        $sql = rtrim($sql, ',');
        $sql .= ') VALUES (';
        foreach ($item as $key => $value) {
            $sql .= ":$key,";
        }
        $sql = rtrim($sql, ',');
        $sql .= ')';
        $log = $sql;

        $conn = $this->db;
        $stmt = $conn->prepare($sql);
        foreach ($item as $key => &$value) {
            $stmt->bindParam($key, $value);
            $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
        }

        $this->logger($log);
        $exec = $stmt->execute();

        $err = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $err = $errorInfo[1].' - '.$errorInfo[2];

            $this->logger($log, $err);

            return false;
        }
        $item->id = $conn->lastInsertId();
        $item->ID = $item->id;

        return (!$exec) ? false : $item;
    }

    public function update($table, $item, $where, $id)
    {
        $this->logger('MyMysql | method: update');

        $this->deleteColumnFromSqlIfNotExist($table, $item);

        $sql = '';
        $sql .= " UPDATE $table SET ";
        foreach ($item as $key => $value) {
            $sql .= "$key=:$key,";
        }
        $sql = rtrim($sql, ',');
        $sql .= ' WHERE 1 = 1 ';
        foreach ($where as $key => $value) {
            $key = str_replace(':', '', $key);
            $sql .= " AND $key = :$key ";
        }
        $log = $sql;

        $conn = $this->db;
        $stmt = $conn->prepare($sql);
        foreach ($item as $key => &$value) {
            $key = str_replace(':', '', $key);
            $stmt->bindParam($key, $value);
            $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
        }
        foreach ($where as $key => &$value) {
            $key = str_replace(':', '', $key);
            $stmt->bindParam($key, $value);
            $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
        }

        $this->logger($log);
        $exec = $stmt->execute();

        $err = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $err = $errorInfo[1].' - '.$errorInfo[2];

            $this->logger($log, $err);

            return false;
        }
        $item->id = $id;
        $item->ID = $id;

        return (!$exec) ? false : $item;
    }

    public function delete($table, $where)
    {
        $this->logger('MyMysql | method: delete');

        if (empty($where)) {
            $this->logger('Impossible to delete without the clause where...');

            return false;
        }

        $sql = "DELETE FROM $table WHERE 1 = 1 ";
        foreach ($where as $key => $value) {
            $key = str_replace(':', '', $key);
            $sql .= " AND $key = :$key ";
        }
        $log = $sql;

        $stmt = $this->db->prepare($sql);
        foreach ($where as $key => &$value) {
            $key = str_replace(':', '', $key);
            $stmt->bindParam($key, $value);
            $log = preg_replace('/:'.$key.'\b/i', "'$value'", $log);
        }

        $this->logger($log);
        $exec = $stmt->execute();

        $err = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $err = $errorInfo[1].' - '.$errorInfo[2];

            $this->logger($log, $err);

            return false;
        }

        return $exec;
    }

    public function execute($sql)
    {
        $this->logger('MyMysql | method: execute');
        $this->logger($sql);

        $stmt = $this->db->prepare($sql);
        $exec = $stmt->execute();

        $err = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $err = $errorInfo[1].' - '.$errorInfo[2];

            $this->logger($log, $err);

            return false;
        }

        return $exec;
    }

    public function sql($method = '', $sql = '', $table = '', $where = array(), $orderBy = '', $obj = null, $id = 0)
    {
        $this->logger('MyMysql | method: sql');

        $result = new stdClass();
        $result->msg = '';
        $result->status = true;
        $result->model = null;

        if ('fetchRow' === $method) {
            $result->model = $this->fetchRow($table, $where, $orderBy);
        } elseif ('fetchAll' === $method) {
            $result->model = $this->fetchAll($table, $where, $orderBy);
        } elseif ('fetchRow2' === $method) {
            $result->model = $this->fetchRow2($sql);
        } elseif ('fetchAll2' === $method) {
            $result->model = $this->fetchAll2($sql);
        } elseif ('insert' === $method) {
            $result->model = $this->insert($table, $obj);
        } elseif ('update' === $method) {
            $result->model = $this->update($table, $obj, $where, $id);
        } elseif ('delete' === $method) {
            $result->model = $this->delete($table, $where);
        } else {
            $result->status = false;
        }

        if (is_bool($result->model) && (false == $result->model)) {
            $result->status = false;
            $result->msg = "Ops. Ocorreu um erro. Method: $method. Sql: $sql. Table: $table. Where: ".json_encode($where).". Order By: $orderBy. Obj: ".json_encode($obj).". Id: $id ";
        }

        return $result;
    }

    /* transaction */

    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    public function closeTransaction()
    {
        return $this->db->closeTransaction();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollback()
    {
        return $this->db->rollback();
    }

    /* private */

    private function logger($str, $err = '')
    {
        $date = date('Y-m-d');
        $hour = date('H:i:s');

        if (!empty($err) && !empty($this->db)) {
            $sql = " SELECT COUNT(*) AS CT
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = 'MYMYSQLLOG' ";

            $row = $this->fetchRow2($sql);

            if (!empty($row) && $row->CT > 0) {
                $obj = new stdClass();
                $obj->LOG = "[$hour] > $str";
                $obj->ERROR = "[ERROR] > $err";

                $this->insert('MYMYSQLLOG', $obj);
            }
        }

        if (true === $this->ini['DB_LOG'] || 'true' === $this->ini['DB_LOG']) {
            @mkdir($this->dirlog, 0777, true);
            @chmod($this->dirlog, 0777);

            $log = '';
            $log .= "[$hour] > $str \n";
            if (!empty($err)) {
                $log .= '[ERROR] > '.$err." \n\n";
            }

            $file = @fopen($this->dirlog.DIRECTORY_SEPARATOR."log-$date.txt", 'a+');
            @fwrite($file, $log);
            @fclose($file);
        }
    }

    /* private func */

    private function deleteColumnFromSqlIfNotExist($table, &$item)
    {
        $sql = " SELECT COLUMN_NAME
            FROM information_schema.COLUMNS
            WHERE TABLE_NAME = '$table' ";

        $stmt = $this->db->prepare($sql);
        $exec = $stmt->execute();

        $arr = (!$exec) ? false : $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($arr)) {
            $columns = array();
            foreach ($arr as $key => $value) {
                $columns[] = $value->COLUMN_NAME;
            }

            foreach ($item as $key => $value) {
                if (!in_array($key, $columns)) {
                    unset($item->$key);
                }
            }
        }
    }
}
