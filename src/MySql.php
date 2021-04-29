<?php

/*
 * Eduardo Malherbi Martins (http://emalherbi.com/)
 * Copyright @emm
 * Full Stack Web Developer.
 */

class MySql
{
    public $ini;
    public $error;
    public $db;

    public function __construct()
    {
        $this->ini = null;
        $this->db = null;
        $this->error = '';

        $this->loadIni();
    }

    public function loadIni()
    {
        $this->ini = parse_ini_file(CONFIG.DS.'application.ini');
    }

    /* error */

    public function getError()
    {
        $this->logger('MyMysql | method: getError');

        return $this->error;
    }

    /* connnect */

    public function connection()
    {
        $this->logger('MyMysql | method: connection');

        if (!empty($this->db)) {
            return $this->db;
        }

        $this->db = new PDO('mysql:host='.$this->ini['DB_HOST'].';dbname='.$this->ini['DB_NAME'], $this->ini['DB_USER'], $this->ini['DB_PASS'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

        // For Amazon.
        $stmt = $this->db->prepare("SET time_zone = 'America/Montevideo'");
        $stmt->execute();

        return $this->db;
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

    /*
     * BY BELUSSO: RETORNA 2, 3, 5 SELECTS NA MESMA PEGADA.
     * NUM ARRAY, DE ACORDO COM A ORDEM DOS MESMOS
     */
    public function fetchAll3($sql, $where = array())
    {
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

        $db = $this->db;
        $stmt = $db->prepare($sql);
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

            echo '<pre>';
            echo print_r($log);
            echo print_r($this->error);
            echo '</pre>';
            die();
        }
        $item->id = $db->lastInsertId();
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

        $db = $this->db;
        $stmt = $db->prepare($sql);
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

            echo '<pre>';
            echo print_r($log);
            echo print_r($this->error);
            echo '</pre>';
            die();
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

        $this->error = '';
        if (!$exec) {
            $errorInfo = $stmt->errorInfo();
            $this->error = $errorInfo[1].' - '.$errorInfo[2];

            $log = $this->error;
            $this->logger($log);

            echo '<pre>';
            echo print_r($log);
            echo print_r($this->error);
            echo '</pre>';
            die();
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

    private function logger($msg)
    {
        if (true == $this->ini['DB_LOG']) {
            $path = LOG.DS;
            $file = date('Y-m-d').'.log';

            @mkdir($path, 0777, true);
            @chmod($path, 0777);

            $file = @fopen($path.$file, 'a+');
            fwrite($file, $msg);
            fwrite($file, "\n");
            fclose($file);
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
