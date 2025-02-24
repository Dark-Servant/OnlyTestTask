<?php
use \Bitrix\Main\{
    Loader,
    Localization\Loc,
    Type\DateTime
};
use \Bitrix\Highloadblock\HighloadBlockTable;
use \Only\Cars\Helpers\OptionParameter;

class CarListByTime extends \CBitrixComponent
{
    const DATE_TEMPLATE = '%s-%s-%s %s';
    const DATE_FORMAT = 'Y-m-d H:i';

    protected $driverIDs = [];
    protected $comfortIDs = [];
    protected $modelIDs = [];

    public function executeComponent()
    {
        Loader::includeModule('only.cars');

        try {
            $this->arResult['MDL_OPTIONS'] = new OptionParameter();
            $IDs = $this->prepareCurrentUserComfort()
                        ->prepareDateInterval()
                        ->prepareDrivers()
                        ->prepareResultComfort()
                        ->prepareModels()
                        ->getResultIDs();

            $this->showModelDataByIDs($IDs);

        } catch (\Exception $error) {
            ShowError($error->getMessage());
        }
    }

    private function prepareCurrentUserComfort(): self
    {
        global $USER;

        $data = CUser::GetList(
                    $field = 'ID',
                    $direction = 'ASC',
                    ['ID' => $USER->GetID()],
                    ['SELECT' => [ONLY_UF_USER_POSITION]]
                )->Fetch();

        if (empty($data[ONLY_UF_USER_POSITION])) {
            throw new \Exception(Loc::getMessage('ERROR_EMPTY_CURRENT_USER_POSITION'));
        }

        Loader::includeModule('highloadblock');

        $HLBlock = HighloadBlockTable::getByID($this->arResult['MDL_OPTIONS']->getHighloadBlock(ONLY_HL_USER_POSITION))->Fetch();
        $HLUserPosition = HighloadBlockTable::compileEntity($HLBlock)->getDataClass();

        $this->comfortIDs = $HLUserPosition::GetByID($data[ONLY_UF_USER_POSITION])->Fetch()[ONLY_HL_UP_COMFORT_CATEGORY];
        return $this;
    }

    private function prepareDateInterval(): self
    {
        $this->arResult['DATE_START'] = $this->getDateTimeByValue($this->arParams['DATE_START'] ?? '');
        $this->arResult['DATE_FINISH'] = empty($this->arParams['DATE_FINISH'])
                                       ? (new DateTime($this->arResult['DATE_START']->format(self::DATE_FORMAT), self::DATE_FORMAT))->add('1 hour')
                                       : $this->getDateTimeByValue($this->arParams['DATE_FINISH']);

        echo '************* DATE_START: ' . $this->arResult['DATE_START']->format(self::DATE_FORMAT) . PHP_EOL;
        echo '************* DATE_FINISH: ' . $this->arResult['DATE_FINISH']->format(self::DATE_FORMAT) . PHP_EOL;

        return $this;
    }

    private function getDateTimeByValue(string $value): DateTime
    {
        /**
         * Для формата записи ГГГГ-ММ-ДД ЧЧ:ММ или ГГГГ.ММ.ДД ЧЧ:ММ
         */
        if (preg_match('/^(\d{4})([.-])(\d{2})\2(\d{2})(?: +(\d{2}:\d{2}))?$/', $value, $valueParts)) {
            $value = sprintf(self::DATE_TEMPLATE, $valueParts[1], $valueParts[3], $valueParts[4], $valueParts[5] ?? '00:00');
            return new DateTime($value, self::DATE_FORMAT);

        /**
         * Для формата записи ДД-ММ-ГГГГ ЧЧ:ММ или ДД.ММ.ГГГГ ЧЧ:ММ
         */
        } elseif (preg_match('/^(\d{2})([.-])(\d{2})\2(\d{4})(?: +(\d{2}:\d{2}))?$/', $value, $valueParts)) {
            $value = sprintf(self::DATE_TEMPLATE, $valueParts[4], $valueParts[3], $valueParts[1], $valueParts[5] ?? '00:00');
            return new DateTime($value, self::DATE_FORMAT);

        /**
         * Для формата записи ГГГГММДДЧЧММ
         */
        } elseif (preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})$/', $value, $valueParts)) {
            $value = sprintf(self::DATE_TEMPLATE, $valueParts[1], $valueParts[2], $valueParts[3], $valueParts[4] . ':' . $valueParts[5]);
            return new DateTime($value, self::DATE_FORMAT);

        /**
         * Для случаем, когда время задано как метка времени
         */
        } elseif (preg_match('/^\d{10}$/', $value)) {
            return DateTime::createFromTimestamp($value);

        } else {
            return new DateTime();
        }
    }

    private function prepareDrivers(): self
    {
        $this->driverIDs = [];
        $driverName = toLower(trim($this->arParams['DRIVER_NAME']));
        $driverID = trim($this->arParams['DRIVER_ID']);
        if (empty($driverName) && empty($driverID)) {
            return $this;
        }

        if (empty($driverName)) {
            $user = \CUser::GetByID($driverID)->Fetch();
            if ($user) {
                $this->driverIDs[] = $user['ID'];
            }

        } else {
            $users = \CUser::GetList($field = 'ID', $direction = 'ASC', []);
            while ($user = $users->Fetch()) {
                $userName = toLower(trim(preg_replace('/[^\wа-я]+/iu', ' ', $user['NAME'] . ' ' . $user['SECOND_NAME'] . ' ' . $user['LAST_NAME'])));
                if (str_contains($userName, $driverName)) {
                    $this->driverIDs[] = $user['ID'];
                }
            }
        }
        return $this;
    }

    private function prepareResultComfort(): self
    {
        Loader::includeModule('iblock');

        $comfortName = trim($this->arParams['COMFORT_NAME']);
        $comfortID = trim($this->arParams['COMFORT_ID']);
        if (empty($comfortName) && empty($comfortID)) {
            return $this;
        }

        if (empty($comfortName)) {
            $comfort = CIBlockElement::GetByID($comfortID)->getNextElement();
            if (!$comfort) {
                throw new \Exception(Loc::getMessage('ERROR_BAD_SPECIFIED_COMFORT_ID', ['#VALUE#' => $comfortID]));
            }

            if (!in_array($comfort->fields['ID'], $this->comfortIDs)) {
                throw new \Exception(Loc::getMessage('ERROR_BAD_RIGHTS_FOR_SPECIFIED_COMFORT_ID', ['#NAME#' => $comfort->fields['NAME']]));
            }

            $this->comfortIDs = [$comfort->fields['ID']];

        } else {
            $comfortIDs = [];
            $comfortUnits = CIBlockElement::GetList(
                                            [], [
                                                'IBLOCK_ID' => $this->arResult['MDL_OPTIONS']->getIBlocks(ONLY_IBLOCK_COMFORT_CATEGORY),
                                                'NAME' => '%' . $comfortName . '%',
                                            ]
                                        );
            while ($model = $comfortUnits->getNextElement()) {
                if (in_array($model->fields['ID'], $this->comfortIDs)) {
                    $comfortIDs[] = $model->fields['ID'];
                }
            }

            if (empty($comfortIDs)) {
                throw new \Exception(Loc::getMessage('ERROR_EMPTY_COMFORT_BY_NAME', ['#VALUE#' => $comfortName]));

            } else {
                $this->comfortIDs = $comfortIDs;
            }
        }
        return $this;
    }

    private function prepareModels(): self
    {
        Loader::includeModule('iblock');

        $this->modelIDs = [];
        $modelName = trim($this->arParams['MODEL_NAME']);
        $modelID = trim($this->arParams['MODEL_ID']);
        if (!empty($modelID)) {
            $model = CIBlockElement::GetByID($modelID)->getNextElement();
            if ($model && $this->checkIsNecessaryNextCarModel($model)) {
                $this->modelIDs[] = $model->fields['ID'];
            }

        } else {
            $filter = ['IBLOCK_ID' => $this->arResult['MDL_OPTIONS']->getIBlocks(ONLY_IBLOCK_CARS)];
            if (!empty($modelName)) {
                $filter['NAME'] = '%' . $modelName . '%';
            }

            $models = CIBlockElement::GetList(['ID' => 'ASC'], $filter);
            while ($model = $models->getNextElement()) {
                if ($this->checkIsNecessaryNextCarModel($model)) {
                    $this->modelIDs[] = $model->fields['ID'];
                }
            }
        }
        return $this;
    }

    private function checkIsNecessaryNextCarModel(_CIBElement $model): bool
    {
        $properties = $model->getProperties();
        if (!in_array($properties[ONLY_IB_CARS_PR_COMFORT_CATEGORY]['VALUE'], $this->comfortIDs)) {
            return false;
        }

        if (!empty($this->driverIDs) && !in_array($properties[ONLY_IB_CARS_PR_DRIVER]['VALUE'], $this->driverIDs)) {
            return false;
        }
        return true;
    }

    private function getResultIDs(): array
    {
        if (empty($this->modelIDs)) {
            throw new \Exception(
                        Loc::getMessage(
                            'ERROR_EMPTY_MODEL_LIST',
                            [
                                '#DATE_START#' => $this->arResult['DATE_START']->format(self::DATE_FORMAT),
                                '#DATE_FINISH#' => $this->arResult['DATE_FINISH']->format(self::DATE_FORMAT),
                            ]
                        )
                    );
        }

        $HLBlock = HighloadBlockTable::getByID($this->arResult['MDL_OPTIONS']->getHighloadBlock(ONLY_HL_CAR_EMPLOYMENT))->Fetch();
        $HLCarEmployment = HighloadBlockTable::compileEntity($HLBlock)->getDataClass();

        $result = $this->modelIDs;

        $filter = [
            ONLY_HL_CE_CAR => $this->modelIDs,
            [
                'LOGIC' => 'OR',
                [
                    '>=' . ONLY_HL_CE_START_DATETIME => $this->arResult['DATE_START'],
                    '<=' . ONLY_HL_CE_START_DATETIME => $this->arResult['DATE_FINISH'],
                ],
                [
                    '>=' . ONLY_HL_CE_FINISH_DATETIME => $this->arResult['DATE_START'],
                    '<=' . ONLY_HL_CE_FINISH_DATETIME => $this->arResult['DATE_FINISH'],
                ],
                [
                    '<=' . ONLY_HL_CE_START_DATETIME => $this->arResult['DATE_START'],
                    '>=' . ONLY_HL_CE_FINISH_DATETIME => $this->arResult['DATE_FINISH'],
                ]
            ]
        ];
        $carEmployment = $HLCarEmployment::GetList([
                                'order' => ['ID' => 'ASC'],
                                'filter' => $filter
                            ]);
        while ($employment = $carEmployment->Fetch()) {
            $position = array_search($employment[ONLY_HL_CE_CAR], $result);
            if ($position !== false) {
                array_splice($result, $position, 1);
            }
        }
        return $result;
    }

    private function showModelDataByIDs(array $IDs): self
    {
        if (empty($IDs)) {
            return $this;
        }

        $comfortModelIDs = [];
        $driverModelIDs = [];
        $data = [];
        $models = CIBlockElement::GetList(['ID' => 'ASC'], ['ID' => $IDs]);
        while ($model = $models->getNextElement()) {
            $data[$model->fields['ID']] = ['NAME' => $model->fields['NAME']];
            $properties = $model->getProperties();

            $comfortModelIDs[$properties[ONLY_IB_CARS_PR_COMFORT_CATEGORY]['VALUE']][] = $model->fields['ID'];
            $driverModelIDs[$properties[ONLY_IB_CARS_PR_DRIVER]['VALUE']][] = $model->fields['ID'];
        }

        $comfortUnits = CIBlockElement::GetList(['ID' => 'ASC'], ['ID' => array_keys($comfortModelIDs)]);
        while ($comfortUnit = $comfortUnits->Fetch()) {
            foreach ($comfortModelIDs[$comfortUnit['ID']] as $modelID) {
                $data[$modelID]['CONFORT'] = $comfortUnit['NAME'];
            }
        }

        $users = CUser::GetList($field = 'ID', $direction = 'ASC', ['ID' => implode('|', array_keys($driverModelIDs))]);
        while ($user = $users->Fetch()) {
            $fullName = implode(' ', array_filter([$user['NAME'], $user['SECOND_NAME'], $user['LAST_NAME']]));
            foreach ($driverModelIDs[$user['ID']] as $modelID) {
                $data[$modelID]['DRIVER'] = $fullName;
            }
        }

        echo '<pre>';
        print_r($data);
        echo '</pre>';
        return $this;
    }
}