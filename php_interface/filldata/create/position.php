<?
use Bitrix\Highloadblock\HighloadBlockTable;

$positionNames = require __DIR__ . '/../data/positions.php';
$positionData = [];

$HLBlock = HighloadBlockTable::getById($optionParameter->getHighloadBlock(ONLY_HL_USER_POSITION))->fetch();
$HLUserPosition = HighloadBlockTable::compileEntity($HLBlock)->getDataClass();

$userPositions = $HLUserPosition::GetList(['filter' => [ONLY_HL_UP_NAME => $positionNames]]);
while ($userPosition = $userPositions->Fetch()) {
    $positionNameKey = array_search($userPosition[ONLY_HL_UP_NAME], $positionNames);
    if ($positionNameKey !== false) {
        $positionData[$userPosition['ID']] = $userPosition[ONLY_HL_UP_NAME];
        array_splice($positionNames, $positionNameKey, 1);
    }
}

$comfortCount = count($comfortData);
foreach ($positionNames as $positionName) {
    $comfortIDs = [];
    $positionComfortCount = rand() % $comfortCount;
    if ($positionComfortCount) {
        $keys = array_rand($comfortData, $positionComfortCount);
        $comfortIDs = is_array($keys) ? $keys : [$keys];
    }
    echo 'POSITION NAME: ' . $positionName . ' (' . implode(', ', array_map(fn($ID) => $comfortData[$ID], $comfortIDs)) . ')' . PHP_EOL;

    $result = $HLUserPosition::add([ONLY_HL_UP_NAME => $positionName, ONLY_HL_UP_COMFORT_CATEGORY => $comfortIDs]);
    if ($result->isSuccess()) {
        $positionData[$result->getID()] = $positionName;
    }
}