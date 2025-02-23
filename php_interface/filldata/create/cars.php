<?
$carsNames = require __DIR__ . '/../data/cars.php';
$carsData = [];

$carIBlockFilter = [
    'IBLOCK_ID' => $optionParameter->getIBlocks(ONLY_IBLOCK_CARS)
];
$carElements = CIBlockElement::GetList([], ['NAME' => $carsNames] + $carIBlockFilter);
while ($carElement = $carElements->Fetch()) {
    $carNameKey = array_search($carElement['NAME'], $carsNames);
    if ($carNameKey !== false) {
        $carsData[$carElement['ID']] = $carElement['NAME'];
        array_splice($carsNames, $carNameKey, 1);
    }
}


$userCount = count($userData);
$userIDs = array_keys($userData);
$comfortIDs = array_keys($comfortData);
foreach ($carsNames as $carsName) {
    $driverID = $userIDs[rand() % $userCount];
    $comfortID = $comfortIDs[rand() % $comfortCount];

    echo 'CAR NAME: ' . $carsName . PHP_EOL;
    echo '    DRIVER: ' . $userData[$driverID]['FULL_NAME'] . PHP_EOL;
    echo '    COMFORT: ' . $comfortData[$comfortID] . PHP_EOL . PHP_EOL;

    $data = $carIBlockFilter
          + [
                'NAME' => $carsName,
                'ACTIVE' => 'Y',
                'PROPERTY_VALUES' => [
                    ONLY_IB_CARS_PR_DRIVER => $driverID,
                    ONLY_IB_CARS_PR_COMFORT_CATEGORY => $comfortID,
                ]
            ];

    $ID = $ibElement->add($data);
    if ($ID) {
        $carsData[$ID] = $carsName;
    } 
}