<?php

/*
 * Eduardo Malherbi Martins (http://emalherbi.com/)
 * Copyright @emm
 * Full Stack Web Developer.
 */

namespace MyMysql;

use Exception;
use PDO;
use stdClass;

set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

class MyMysql
{
    private $DS = null; // DS
    private $RT = null; // ROOT
    private $DL = null; // DIR LOG

    private $db = null;
    private $error = '';
    private $ini = null;

    public function __construct($ini = array(), $dl = '')
    {
        $this->DS = DIRECTORY_SEPARATOR;
        $this->RT = realpath(dirname(__FILE__));
        $this->DL = empty($dl) ? realpath(dirname(__FILE__)) : $dl;

        if (!empty($ini)) {
            $this->setIni($ini);
        }

        $this->ini = $this->getIni();
        $this->connect();
    }

    /* error */

    public function getError()
    {
        $this->logger('MyMysql | method: getError');

        return $this->error;
    }

    /* connnect */

    public function connect()
    {
        $this->logger('MyMysql | method: connect');
        $this->db = new PDO('mysql:host='.$this->ini['DB_HOST'].';dbname='.$this->ini['DB_NAME'], $this->ini['DB_USER'], $this->ini['DB_PASS'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

        return $this->isConnect();
    }

    public function isConnect()
    {
        $this->logger('MyMysql | method: isConnect');

        return empty($this->db) ? false : true;
    }

    public function disconnect()
    {
        $this->logger('MyMysql | method: disconnect');
        $this->db = null;

        return !$this->isConnect();
    }

    /* ini */

    public function getIni()
    {
        try {
            $this->logger('MyMysql | method: getIni');

            return parse_ini_file($this->RT.$this->DS.'MyMysql.ini');
        } catch (Exception $e) {
            $err = $e->getMessage();
            $this->logger('MyMysql | getIni', $err);
            die(print_r($e->getMessage()));
        }
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

        $connection = $this->db;
        $stmt = $connection->prepare('SET SQL_BIG_SELECTS = 1');
        $stmt->execute();
        $stmt = $connection->prepare($sql);
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

        $this->error = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $this->error = $errorInfo[1].' - '.$errorInfo[2];

            $log = $this->error;
            $this->logger($log);
        } else {
            $item->id = $conn->lastInsertId();
            $item->ID = $item->id;
        }

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

        $this->error = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $this->error = $errorInfo[1].' - '.$errorInfo[2];

            $log = $this->error;
            $this->logger($log);
        } else {
            $item->id = $id;
            $item->ID = $id;
        }

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

        $this->error = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $this->error = $errorInfo[1].' - '.$errorInfo[2];

            $log = $this->error;
            $this->logger($log);
        }

        return $exec;
    }

    public function execute($sql)
    {
        $this->logger('MyMysql | method: execute');
        $this->logger($sql);

        $stmt = $this->db->prepare($sql);

        return $stmt->execute();
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

        if (is_bool($result->model)) {
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

    private function setIni($ini = array())
    {
        $this->logger('MyMysql | method: setIni');

        INI::write($this->RT.$this->DS.'MyMysql.ini', array('INI' => $ini));
    }

    private function logger($str, $err = '')
    {
        if (true == $this->ini['DB_LOG']) {
            $date = date('Y-m-d');
            $hour = date('H:i:s');

            @mkdir($this->DL, 0777, true);
            @chmod($this->DL, 0777);

            $log = '';
            $log .= "[$hour] > $str \n";
            if (!empty($err)) {
                $log .= "[ERROR] > $err \n\n";
            }

            $file = fopen($this->DL.$this->DS."log-$date.txt", 'a+');
            fwrite($file, $log);
            fclose($file);
        }
    }

    /* private func */

    private function deleteColumnFromSqlIfNotExist($table, &$item)
    {
        foreach ($item as $key => $value) {
            $sql = '';
            $sql .= " SELECT COUNT(*) AS COLUMN_EXIST
      				FROM information_schema.COLUMNS
      				WHERE TABLE_NAME = '$table'
      				AND COLUMN_NAME = '$key' ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $columnExist = $stmt->fetchObject();
            $columnExist = $columnExist->COLUMN_EXIST;
            if (empty($columnExist)) {
                unset($item->$key);
            }
        }
    }
}
