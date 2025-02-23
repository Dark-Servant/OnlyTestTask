<?php
use Bitrix\Main\Loader;
use Only\Cars\Helpers\OptionParameter;

$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
define("EXTRANET_NO_REDIRECT", true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/bx_root.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

Loader::includeModule('iblock');
Loader::includeModule('highloadblock');
Loader::includeModule('only.cars');

$optionParameter = new OptionParameter();
$ibElement = new CIBlockElement();

require_once __DIR__ . '/create/comfort.php';
require_once __DIR__ . '/create/position.php';
require_once __DIR__ . '/create/position2user.php';
require_once __DIR__ . '/create/cars.php';