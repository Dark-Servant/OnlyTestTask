<?
$userData = [];
$userNotPostionCount = 0;

$userUnit = new \CUser();
$positionCount = count($positionData);
$positionIDs = array_keys($positionData);

$users = CUser::GetList($field = 'ID', $sort = 'ASC', [], ['SELECT' => [ONLY_UF_USER_POSITION]]);
while ($user = $users->Fetch()) {
    $userData[$user['ID']] = [
        'FULL_NAME' => implode(' ', array_filter([$user['NAME'], $user['SECOND_NAME'], $user['LAST_NAME']]))
    ];

    if (!empty($user[ONLY_UF_USER_POSITION])) {
        continue;
    }

    ++$userNotPostionCount;
    $positionID = $positionIDs[rand() % $positionCount];
    $userUnit->update($user['ID'], [ONLY_UF_USER_POSITION => $positionID]);

    echo 'USER NAME: ' . $userData[$user['ID']]['FULL_NAME'] . ' >>> ' . $positionData[$positionID] . PHP_EOL;
}

echo '**** UPDATED USER COUNT: ' . $userNotPostionCount . PHP_EOL;