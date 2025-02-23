<?
$comfortNames = require __DIR__ . '/../data/comfort.php';
$comfortData = [];
$comfortIBlockFilter = [
    'IBLOCK_ID' => $optionParameter->getIBlocks(ONLY_IBLOCK_COMFORT_CATEGORY)
];
$comfortElements = CIBlockElement::GetList([], ['NAME' => $comfortNames] + $comfortIBlockFilter);
while ($comfortElement = $comfortElements->Fetch()) {
    $comfortNameKey = array_search($comfortElement['NAME'], $comfortNames);
    if ($comfortNameKey !== false) {
        $comfortData[$comfortElement['ID']] = $comfortElement['NAME'];
        array_splice($comfortNames, $comfortNameKey, 1);
    }
}

foreach ($comfortNames as $comfortName) {
    echo 'COMFORT NAME: ' . $comfortName . PHP_EOL;
    $ID = $ibElement->add(['NAME' => $comfortName] + $comfortIBlockFilter + ['ACTIVE' => 'Y']);
    if ($ID) {
        $comfortData[$ID] = $comfortName;
    }
}