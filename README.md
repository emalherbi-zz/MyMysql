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
    // define timezone if not defined in ini file.
    if (@date_default_timezone_get() !== @ini_get('date.timezone')) {
        @date_default_timezone_set('America/Sao_Paulo');
    }

    echo 'Success...';
} catch (Exception $e) {
    die(print_r($e->getMessage()));
}
```

