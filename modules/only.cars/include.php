<?
// Основные константы
define('ONLY_CARS_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
foreach ((require __DIR__ . '/install/version.php') as $key => $value) {
    define('ONLY_CARS_' . $key, $value);
}

require_once __DIR__ . '/constants/iblock.php';
require_once __DIR__ . '/constants/highload/position.php';
require_once __DIR__ . '/constants/uf/user.php';