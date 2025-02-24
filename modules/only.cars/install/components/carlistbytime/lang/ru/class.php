<?
require $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . ONLY_CARS_MODULE_ID . '/lang/ru/install/highload/position.php';

$MESS['ERROR_EMPTY_CURRENT_USER_POSITION'] = 'У вас не установлена должность из highload-блока "' . $MESS['HL_USER_POSITION'] . '"';
$MESS['ERROR_BAD_SPECIFIED_COMFORT_ID'] = 'Не была найдена категория комфота с идентификатором "#VALUE#"';
$MESS['ERROR_BAD_RIGHTS_FOR_SPECIFIED_COMFORT_ID'] = 'У вашей должности нет доступа к авомобилям указанной категории комфота "#NAME#"';
$MESS['ERROR_EMPTY_COMFORT_BY_NAME'] = 'Нет категорий комфорта, подходящим для вашей должности и со значением "#VALUE#" в названии';
$MESS['ERROR_EMPTY_MODEL_LIST'] = 'У вас нет автомобилей, чтобы узнать какие свободны на время #DATE_START# - #DATE_FINISH#';
