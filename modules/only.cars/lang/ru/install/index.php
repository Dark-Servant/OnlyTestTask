<?
$MESS['ONLY_CARS_MODULE_NAME'] = 'Модуль "ООО Онли. Тестовое задание"';
$MESS['ONLY_CARS_MODULE_DESCRIPTION'] = '';
$MESS['ONLY_CARS_PARTNER_NAME'] = 'Konstantin Beloglazov';
$MESS['ONLY_CARS_MODULE_WAS_INSTALLED'] = $MESS['ONLY_CARS_MODULE_NAME'] . ' был установлен';
$MESS['ONLY_CARS_MODULE_NOT_INSTALLED'] = $MESS['ONLY_CARS_MODULE_NAME'] . ' не был установлен';
$MESS['ONLY_CARS_MODULE_WAS_DELETED'] = $MESS['ONLY_CARS_MODULE_NAME'] . ' удален';
$MESS['ERROR_NO_OPTION_CLASS'] = 'Для модуля не существует класса #CLASS#';
$MESS['ERROR_IBLOCK_TYPE_LANG'] = 'Отсутствует параметр LANG_CODE для типа инфоблока #TYPE#';
$MESS['ERROR_IBLOCK_TYPE_EMPTY_LANG'] = 'Значение языковой константы LANG_CODE не заполнено для типа инфоблока #TYPE#';
$MESS['ERROR_IBLOCK_TYPE_CREATING'] = 'Не удалось создать тип инфоблока #TYPE#';
$MESS['ERROR_IBLOCK_LANG'] = 'Отсутствует параметр LANG_CODE для инфоблока #IBLOCK#';
$MESS['ERROR_IBLOCK_EMPTY_LANG'] = 'Значение языковой константы LANG_CODE не заполнено для инфоблока #IBLOCK#';
$MESS['ERROR_IBLOCK_CREATING'] = 'Не удалось создать инфоблок #IBLOCK#';
$MESS['ERROR_BAD_PROPERTY_IBLOCK'] = 'Для свойства #PROPERTY# не указан параметр IBLOCK_ID с именем константы, по значению в которой '
                                   . 'можно получить информацию о связанном со свойством инфоблоке';
$MESS['ERROR_IBLOCK_PROPERTY_CREATING'] = 'Не удалось создать свойство инфоблока #PROPERTY#';
$MESS['ERROR_IBLOCK_PROPERTY_LANG'] = 'Отсутствует параметр LANG_CODE для свойства инфоблока #PROPERTY#';
$MESS['ERROR_IBLOCK_PROPERTY_EMPTY_LANG'] = 'Значение языковой константы константы LANG_CODE не заполнено для свойства инфоблока #PROPERTY#';
$MESS['ERROR_BAD_USER_FIELD_NAME'] = 'Пользовательское поле NAME начинается не с UF_';
$MESS['ERROR_BAD_USER_FIELD_VOTE_CHANNEL'] = 'У пользовательского поля NAME не указано в [\'SETTINGS\'][\'CHANNEL_ID\'] '
                                           . 'название константы, под которой хранится символьный код или идентификатор '
                                           . 'группы опросов, созданной модулем';
$MESS['ERROR_BAD_USER_FIELD_IBLOCK'] = 'У пользовательского поля NAME не указано в [\'SETTINGS\'][\'IBLOCK_ID\'] '
                                     . 'название константы, под которой хранится символьный код или идентификатор '
                                     . 'инфоблока';
$MESS['ERROR_USER_FIELD_CREATING'] = 'Не удалось добавить пользовательское поле NAME';
$MESS['ERROR_HIGHLOAD_CREATING'] = 'Не удалось добавить highload NAME';
$MESS['ERROR_BAD_HBLOCK_ID'] = 'Указана константа с неправильным значением, оно не является ни идентификатором, '
                             . 'ни именем настройки для создаваемых модулем highload-ов';

$MESS['HL_TEST_MESSAGES_TITLE'] = 'Тестовый highload';
$MESS['STR_FIELD_TITLE'] = 'Строковое поле';
$MESS['INT_FIELD_TITLE'] = 'Числовое поле';
$MESS['ALLREADY_EXISTS_HL_FIELD_TITLE'] = 'Поле для существующего highload';

require_once __DIR__ . '/iblock.php';
require_once __DIR__ . '/uf/user.php';
require_once __DIR__ . '/highload/position.php';