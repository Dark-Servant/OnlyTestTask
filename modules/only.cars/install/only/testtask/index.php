<?
use Bitrix\Main\Localization\Loc;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle(Loc::getMessage('TEST_TASK_TITLE'));
$APPLICATION->IncludeComponent('only.cars:carlistbytime', '');

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');?>