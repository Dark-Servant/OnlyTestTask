<?
use \Bitrix\Main\{
    Application,
    Localization\Loc
};

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle(Loc::getMessage('TEST_TASK_TITLE'));

$request = Application::getInstance()->getContext()->getRequest();

$APPLICATION->IncludeComponent(
    'only.cars:carlistbytime', '',
    [
        'MODEL_NAME' => $request->get('model_name') ?: false,
        'MODEL_ID' => $request->get('model_id') ?: false,
        'DRIVER_ID' => $request->get('driver_id') ?: false,
        'DRIVER_NAME' => $request->get('driver_name') ?: false,
        'COMFORT_ID' => $request->get('comfort_id') ?: false,
        'COMFORT_NAME' => $request->get('comfort_name') ?: false,
        'DATE_START' => $request->get('date_start') ?: false,
        'DATE_FINISH' => $request->get('date_finish') ?: false,
    ]
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');?>