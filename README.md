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
    if (@date_default_timezone_get() !== @ini_get('date.timezone')) {
        @date_default_timezone_set('America/Sao_Paulo');
    }

    $mysql = new MyMysql\MyMysql(array(
        'DB_LOG' => true,
        'DB_HOST' => '192.168.1.100',
        'DB_NAME' => 'DATABASE',
        'DB_USER' => 'USERNAME',
        'DB_PASS' => 'PASSWORD',
    ), realpath(dirname(__FILE__)));

    /* fetch */

    $result = $mysql->fetchRow('ENTIDADES', array('ID_ENTIDADE' => '2'), 'ORDER BY ID_ENTIDADE');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    $result = $mysql->fetchRow2('SELECT * FROM ENTIDADES');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    $result = $mysql->fetchAll('ENTIDADES', array('ID_ENTIDADE' => '2'), 'ORDER BY ID_ENTIDADE');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    $result = $mysql->fetchAll2('SELECT * FROM ENTIDADES LIMIT 2');
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* insert */

    $item = new stdClass();
    $item->ID_MESA = 0;
    $item->ID_EMPRESA = 2;
    $item->CODIGOMESA = 999;
    $item->DESCRICAO = 'TESTE';
    $item->ATIVO = 1;

    $result = $mysql->insert('MESAS', $item);
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* update */

    $ID_MESA = 530;
    $item = new stdClass();
    $item->ID_EMPRESA = 2;
    $item->CODIGOMESA = 999;
    $item->DESCRICAO = 'TESTE';
    $item->ATIVO = 1;

    $result = $mysql->update('MESAS', $item, array('ID_MESA' => $ID_MESA), $ID_MESA);
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* delete */

    $ID_MESA = 531;
    $result = $mysql->delete('MESAS', array('ID_MESA' => $ID_MESA));
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    /* execute */

    $sql = " INSERT INTO MESAS(ID_EMPRESA, CODIGOMESA, DESCRICAO) VALUES (2, 888, 'TESTE') ";
    $result = $mysql->execute($sql);
    echo '<pre>';
    echo print_r($result);
    echo '</pre>';

    echo 'Success...';
} catch (Exception $e) {
    die(print_r($e->getMessage().'-'.$mysql->getError()));
}
```

