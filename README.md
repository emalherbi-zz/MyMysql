# MyMysql

My Mysql | PDO

# Install

```
composer require emalherbi/mymysql
```

# Usage

```php
require_once 'vendor/autoload.php';

try {
    $mysql = new MyMysql\MyMysql(array(
        'DB_LOG' => true,
        'DB_HOST' => '192.168.1.100',
        'DB_NAME' => 'DATABASE',
        'DB_USER' => 'USERNAME',
        'DB_PASS' => 'PASSWORD',
    ), realpath(dirname(__FILE__)));

    /* fetch */

    $result = $mysql->fetchRow('CLIENTS', array('ID_CLIENTS' => '2'), 'ORDER BY ID_CLIENTS');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    $result = $mysql->fetchRow2('SELECT * FROM CLIENTS');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    $result = $mysql->fetchAll('CLIENTS', array('ID_CLIENTS' => '2'), 'ORDER BY ID_CLIENTS');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    $result = $mysql->fetchAll2('SELECT * FROM CLIENTS LIMIT 2');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* insert */

    $item = new stdClass();
    $item->ID_BOARD = 0;
    $item->CODE = 999;
    $item->DESCRIPTION = 'TEST';
    $item->ACTIVE = 1;

    $result = $mysql->insert('BOARD', $item);
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* update */

    $ID_BOARD = 530;
    $item = new stdClass();
    $item->CODE = 999;
    $item->DESCRIPTION = 'TEST';
    $item->ACTIVE = 1;

    $result = $mysql->update('BOARD', $item, array('ID_BOARD' => $ID_BOARD), $ID_BOARD);
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* delete */

    $ID_BOARD = 531;
    $result = $mysql->delete('BOARD', array('ID_BOARD' => $ID_BOARD));
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* execute */

    $sql = " INSERT INTO BOARD(CODE, DESCRIPTION) VALUES (888, 'TEST') ";
    $result = $mysql->execute($sql);
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    echo 'Success...';
} catch (Exception $e) {
    die(print_r($e->getMessage().'-'.$mysql->getError()));
}
```
