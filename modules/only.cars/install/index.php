<?php
use Bitrix\Main\{
    Localization\Loc,
    Loader,
    Config\Option
};
use Only\Cars\EventHandles\Employment;

class only_cars extends CModule
{
    public $MODULE_ID;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;

    protected $nameSpaceValue;
    protected $subLocTitle;
    protected $optionParameterClass;
    protected $optionParameter = null;
    protected $definedContants;

    protected static $defaultSiteID;

    const ADMIN_GROUP_ID = 1;
    const ALL_USER_GROUP_ID = 2;
    const SAVE_OPTIONS_WHEN_DELETED = true;
    
    /**
     * Опции, которые необходимо добавить в проект, сгруппированы по названиям, которые будут использоваться
     * в имени метода для их добавления. Опции описываются как ассоциативный массив, где "ключ" - центральная
     * часть имени метода, который будет вызван для добавления/удаления опций той группы, чье имя указано
     * в "ключе".
     * Для того, чтобы была инициализация опций в конкретной группе или их обработка перед удалением, необходимо
     * создать методы init<"Ключ">Options и remove<"Ключ">Options.
     * В каждой группе опций, которые так же оформлены, как ассоциативный массив,
     *      "ключ" - название константы, которая хранит название опции, эта константа должна быть объявлена в
     *      файле include.php у модуля, в этом "ключе" обычно описывается символьное имя элемента
     *      "значение" - настройки для инициализации каждого элемента из группы опций.
     * Итоговые данные элементов из групп опций после добавления будут сохранены в опциях модуля, каждый в
     * своей группе, для обращения к ним надо использовать класс Helpers\Options и методы по шаблону
     *     get<"Название группы опций">(<название конкретного элемента, необязательный параметр>)
     *
     * Если объявить в классе константу SAVE_OPTIONS_WHEN_DELETED со значением true, то все данные, добавленные
     * при установке модуля, при удалении модуля будут сохранены в системе и снова будут использоваться без
     * переустановки при новой установке модуля. Эта возможность автоматически унаследуется и для дочених модулей,
     * но эту константу можно переобъявить в дочерних модулях, изменив там необходимость сохранения данных
     * при удалении модуля
     * 
     * ВНИМАНИЕ. Не стоит в каждой группе объявлять настройки для более одного элемента группы под именем константы
     * в "ключе", пусть и со своим уникальным именем, но с тем же самым "значением" константы, иначе после установки
     * модуль просто потеряет все, кроме последнего, установленные данные по этому "значению", что может привести к
     * багу, а так же после удаления модуля в системе останется мусор, т.е. информация, которую модуль установил,
     * но не смог удалить при своем удалении, так как ничего о ней не знал. Настройки для каждого элемента той же
     * самой группы должны храниться под "ключом", который является именем константы, "значение" которой уникально для
     * этой группы данных. Для некоторых групп данных, например, свойств инфоблоков, полей списка предусмотрено
     * использование в "значении" префикса, отделенного точкой, сам префикс при установке элемента группы игнорируется,
     * а при хранении в опциях модуля позволяет избежать перезаписи информации установленного элемента группы информацией
     * о другом установленном элементе той же группы. Для элементов других групп нельзя использовать константы с тем же
     * самым "значением", но то же "значение" под любым именем константы в той же самой группе данных можно будет
     * использовать в следующем модуле. 
     */
    const OPTIONS = [
        /**
         * Настройки для создания типов инфоблока. В "значении" указываются параметры для создания типа инфоблока.
         * Обязательно нужен параметр LANG_CODE с именем языковой константы для названия
         */
        'IBlockTypes' => [
            'ONLY_IB_TYPE_ID' => [
                'LANG_CODE' => 'ONLY_IB_TYPE_ID'
            ]
        ],

        /**
         * Настройки для создания инфоблоков. В "значении" указываются параметры для создания инфоблоков. Обязательно
         * нужны параметры LANG_CODE с именем языковой константы для названия и IBLOCK_TYPE_ID с именем константы, в
         * которой хранится код типа инфоблока.
         * В параметре PERMISSIONS указываются права доступа к инфоблоку, где "ключ" - идентификатор пользовательской
         * группы, а "значение" - код права доступа. По-умолчанию, для администраторов  установлен "полный доступ",
         * а для всех пользователей - "чтение".
         * Права доступа:
         *     E - добавление элементов инфоблока в публичной части;
         *     S - просмотр элементов и разделов в административной части;
         *     T - добавление элементов инфоблока в административной части; 
         *     R - чтение; 
         *     U - редактирование через документооборот; 
         *     W - запись; 
         *     X - полный доступ (запись + назначение прав доступа на данный инфоблок).
         * Права доступа можно указывать и для групп, созданных модулем, просто указав в "ключе" строковый
         * идентификатор константы, используемый в UserGroup
         */
        'IBlocks' => [
            'ONLY_IBLOCK_CARS' => [
                'LANG_CODE' => 'ONLY_IBLOCK_CARS',
                'IBLOCK_TYPE_ID' => 'ONLY_IB_TYPE_ID'
            ],

            'ONLY_IBLOCK_COMFORT_CATEGORY' => [
                'LANG_CODE' => 'ONLY_IBLOCK_COMFORT_CATEGORY',
                'IBLOCK_TYPE_ID' => 'ONLY_IB_TYPE_ID'
            ],
        ],

        /**
         * Настройки для создания свойств инфоблоков. В "ключе" указывается название константы модуля, у которой в
         * "значении" указан символьным код свойства. В "значении" же указываются параметры для создания свойств инфоблоков.
         * Обязательно нужны параметры LANG_CODE с именем языковой константы для названия и IBLOCK_ID с именем константы,
         * которая использовалась в IBlocks как "ключ", под которым хранятся настройки инфоблока, или которая объявлена
         * в include.php с идентификатором уже существующего инфоблока.
         * Если тип свойства список (PROPERTY_TYPE = L), то в параметрах свойств можно указать параметр LIST_VALUES, в
         * значении которого указан массив, где каждый элемент содержит минимум один параметр с ключом LANG_CODE для
         * языковой константы, под которой хранится значение параметра, но название константы указывается не полностью,
         * а лишь ее последняя часть, что должна идти после префикса, который указан как название языковой константы в
         * LANG_CODE у самого свойства, т.е. название языковой константы в файлах с переводом для какого-то элемента
         * списка должно соответствовать шаблону
         *      <константа из LANG_CODE для свойства инфоблока><дополнительная часть языковой константы>
         * 
         * Если тип свойства связан с другим инфоблоком, то в параметрах используется параметр LINK_IBLOCK_ID, для значения
         * которого действуют те же правила, что и для параметра IBLOCK_ID
         * 
         * Как и в других группах, параметры каждого элемента группы, точнее свойства инфоблока указываются под "ключом",
         * который является константой из файла модуля include.php. Но тут возможна ошибка, так как могут быть использованы
         * разные инфоблоки, а группа настроек для свойств инфоблока у одного модуля одна, то может случиться ситуация, когда
         * были указаны настройки для свойств разных инфоблоков под разными названиями констант, но одинаковыми "значениями",
         * т.е. символьными именами свойств, из-за чего вся информация о всех добавленных свойствах инфоблока с этими символьными
         * именами, кроме последнего добавленного свойства, будет не сохранена, а, значит, модуль после удаления оставит мусор в
         * системе. Для решения такой проблемы в значении константы надо использовать префикс, например, для свойства STRING_PROPERTY
         * из инфоблока с символьным именем some_iblock можно указать some_iblock.STRING_PROPERTY, при установке будет использовано
         * именно STRING_PROPERTY
         */
        'IBlockProperties' => [
            'ONLY_IB_CARS_PR_DRIVER' => [
                'LANG_CODE' => 'ONLY_IB_CARS_PR_DRIVER',
                'IBLOCK_ID' => 'ONLY_IBLOCK_CARS',
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE' => 'employee'
            ],

            'ONLY_IB_CARS_PR_COMFORT_CATEGORY' => [
                'LANG_CODE' => 'ONLY_IB_CARS_PR_COMFORT_CATEGORY',
                'IBLOCK_ID' => 'ONLY_IBLOCK_CARS',
                'PROPERTY_TYPE' => 'E',
                'LINK_IBLOCK_ID' => 'ONLY_IBLOCK_COMFORT_CATEGORY'
            ],
        ],

        /**
         * Настройки для создания HighloadBlock. В значении массив с "ключами"
         *     NAME - кодовое имя HighloadBlock
         *     TABLE_NAME - название таблицы
         * После установки данные каждого highloadblock сохраняются в опциях модуля в группе HighloadBlock как массив, где
         * "ключ" это значение константы, название которой указанно тут как "ключ", а "значение" это ID highloadblock.
         * Получить из опций модуля к данным установленного конкретного highloadblock можно с помощью
         *      Infoservice\<Символьное имя модуля>\Helpers\Options::getHighloadBlock(<константа, чье имя использовалось тут в настройках>)
         */
        'HighloadBlock' => [],

        /**
         * настройки для создания пользовательских полей для чего-угодно. Значения хранят настройки пользовательского
         * поля. 
         * ENTITY_ID и FIELD_NAME не указывать. Значение FIELD_NAME должно быть объявлено в include.php как
         * константа с именем, указанным здесь в каждой группе как "ключ".
         * В настройках можно указать LANG_CODE, который используется для указания кода языковой опции, где
         * хранится название пользовательского поля.
         * Указывать тип надо не в USER_TYPE_ID, в TYPE, это более сокращено. Остальные настройки такие же,
         * какие надо передавать в Битриксе. Чтобы добавить к модулю настрокий какого-то пользовательского поля
         * конкретного типа, сначало стоит создать его в административной части, потом с помощью
         *      Настройки -> Инструменты -> Командная PHP-строка
         * и метода
         *      CUserTypeEntity::GetById(<ID созданного пользовательского поля>)
         * затем выбрать нужные параметры поля, указать тут
         * 
         * Если указан тип vote, то важно, чтобы было указано в ['SETTINGS']['CHANNEL_ID'] навазние константы модуля,
         * в значении которой либо указан идентификатор группы опросов, либо символьное поле, т.е. константа используется
         * в настройках для VoteChannels, где указаны настройки создаваемой группы опросов.
         * Если указан тип iblock_element, то важно, чтобы было указано в ['SETTINGS']['IBLOCK_ID'] навазние константы модуля,
         * в значении которой либо указан идентификатор инфоблока, либо символьное поле, т.е. константа используется
         * в настройках для IBlocks, где указаны настройки создаваемого инфоблока.
         * Если указан тип enumeration, то в параметрах можно указать параметр LIST_VALUES как массив, каждый
         * элемент которого представляет отдельное значения для списка, для каждого значения списка обязательно
         * должен быть указан LANG_CODE с именем языковой константы, в которой хранится название значения,
         * указаные элементы списка с одинаковыми значения будут созданы один раз. При наличии LANG_CODE у
         * пользовательского поля параметр LANG_CODE для значений списка надо писать в ином виде, так как
         * значение параметра у пользовательского поля будет использоваться как префикс, т.е. языковые константы
         * для значений списка должны иметь названия, начинающиеся с названия языковой константы у их
         * пользовательского поля, если такое имеется у него, и знаком подчеркивания после.
         * Значения для SHOW_FILTER:
         *      N - не показывать
         *      I - точное совпадение
         *      E - маска
         *      S - подстрока
         * 
         * После создания пользовательского поля его ID будет записан в опциях модуля в группе, в которой он был
         * объявлен, т.е. для IBlockSectionFields ID будет записан в опциях модуля в группе IBlockSectionFields,
         * в массиве под "ключом" ID.
         * ID значений пользовательского поля типа "Список" так же будут сохранены в опциях модуля в данных своего
         * пользовательского поля.
         * Для получения инфрмации о пользовательском поле из опций модуля надо ипользовать класс модуля и метод,
         * начинающийся с get и далее название группы опций
         *      Infoservice\<Символьное имя модуля>\Helpers\Options::get<название группы опций>
         * например, для IBlockSectionFields
         *      Infoservice\<Символьное имя модуля>\Helpers\Options::getIBlockSectionFields
         * 
         * Настройки пользовательских полей для HighloadBlock. Обязательно указание параметра HBLOCK_ID,
         * в значении которого указать константу модуля, под которой хранится идентификатор существующего
         * HighloadBlock или значение, которое используеся в части HighloadBlock для создания модулем своих
         * highloadblock. Остальные настройки такие же, как указано выше для пользовательских полей
         * 
         * ВНИМАНИЕ. Если в настройках полей HighloadBlock не указывать параметры SHOW_IN_LIST и EDIT_IN_LIST,
         * равные Y, то при добавлении данных highloadblock через административную часть нельзя будет увидеть
         * добавленные к highloadblock поля
         */
        'HighloadFields' => [],

        /**
         * Настройки пользовательских полей для пользователей
         */
        'UserFields' => [],
    ];

    function __construct()
    {
        $this->initMainTitles()->initVersionTitles();
    }

    /**
     * Инициализирует название и описание модуля, а так же в процессе инициализации проходят
     * инициализацию другие переменные объекта класса, например, идентификатор модуля
     *
     * @return static
     */
    protected function initMainTitles(): static
    {
        $this->initModuleClassPath()->initOptionParameterClass();
        Loc::loadMessages($this->moduleClassPath . '/' . basename(__FILE__));

        $this->subLocTitle = strtoupper(static::class) . '_';
        $this->MODULE_NAME = Loc::getMessage($this->subLocTitle . 'MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage($this->subLocTitle . 'MODULE_DESCRIPTION');

        $this->PARTNER_NAME = Loc::getMessage($this->subLocTitle . 'PARTNER_NAME');
        return $this;
    }

    /**
     * Запоминает и возвращает настоящий путь к текущему классу
     * 
     * @return string
     */
    protected function initModuleClassPath(): static
    {
        $this->moduleClass = new \ReflectionClass(static::class);
        // не надо заменять на __DIR__, так как могут быть дополнительные модули $this->moduleClassPath
        $this->moduleClassPath = rtrim(preg_replace('/[^\/\\\\]+$/', '', $this->moduleClass->getFileName()), '\//');
        return $this;
    }

    /**
     * Запоминает и возвращает название класса, используемого для установки и сохранения
     * опций текущего модуля
     * 
     * @return string
     */
    protected function initOptionParameterClass(): static
    {
        $this->optionParameterClass = $this->initNameSpaceValue()->nameSpaceValue . '\\Helpers\\OptionParameter';
        return $this;
    }

    /**
     * Запоминает и возвращает название именного пространства для классов из
     * библиотеки модуля
     * 
     * @return string
     */
    protected function initNameSpaceValue(): static
    {
        $this->nameSpaceValue = preg_replace('/\.+/', '\\\\', ucwords($this->initModuleId()->MODULE_ID, '.'));
        return $this;
    }

    /**
     * Запоминает и возвращает код модуля, к которому относится текущий класс
     * 
     * @return string
     */
    protected function initModuleId(): static
    {
        $this->MODULE_ID = basename(dirname($this->moduleClassPath));
        return $this;
    }

    /**
     * Инициализирует переменные объекта класса, используя параметры из файла
     *      modules/<ID модуля>/install/version.php
     * и создает переменные объекта по правилу
     *      MODULE_<символьный код параметра> = <значение параметра>
     *
     * @return static
     */
    protected function initVersionTitles(): static
    {
        $versionFile = $this->moduleClassPath . '/version.php';
        if (!file_exists($versionFile)) {
            return $this;
        }

        $versionTitles = include $versionFile;
        if (empty($versionTitles) || !is_array($versionTitles)) {
            return $this;
        }

        foreach ($versionTitles as $versionParameterCode => $versionParameterValue) {
            $parameterCode = 'MODULE_' . strtoupper($versionParameterCode);
            $this->$parameterCode = $versionParameterValue;
        }
        return $this;
    }

    /**
     * Запоминает и возвращает кода сайта по-умолчанию
     * 
     * @return string
     */
    protected static function getDefaultSiteID()
    {
        if (self::$defaultSiteID) {
            return self::$defaultSiteID;
        }

        return self::$defaultSiteID = CSite::GetDefSite();
    }

    /**
     * По переданному имени возвращает значение константы текущего класса с учетом того, что эта константа
     * точно была (пере)объявлена в этом классе модуля. Конечно, получить значение константы класса можно
     * и через <название класса>::<название константы>, но такая запись не учитывает для дочерних классов,
     * что константа не была переобъявлена, тогда она может хранить ненужные старые данные, из-за чего требуется
     * ее переобъявлять, иначе дочерние модули начнут устанавливать то же, что и родительские, а переобъявление
     * требует дополнительного внимания к каждой константе и дополнительных строк в коде дочерних модулей
     * 
     * @param string $constName - название константы
     * @return array
     */
    protected function getModuleConstantValue(string $constName)
    {
        $constant = $this->moduleClass->getReflectionConstant($constName);
        if (
            ($constant === false)
            || ($constant->getDeclaringClass()->getName() != static::class)
        ) return [];

        return $constant->getValue();
    }

    /**
     * Подключает модуль и сохраняет созданные им константы
     * 
     * @return void
     */
    protected function initDefinedContants()
    {
        /**
         * array_keys нужен, так как в array_filter функция isset дает
         * лишнии результаты
         */
        $this->definedContants = array_keys(get_defined_constants());

        Loader::IncludeModule($this->MODULE_ID);
        $this->definedContants = array_filter(
            get_defined_constants(),
            function($key) {
                return !in_array($key, $this->definedContants);
            }, ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Проверяет наличие языковой константы и ее значение
     * 
     * @param $langCode - название языковой константы
     * @param string $prefixErrorCode - префикс к языковым конcтантам для ошибок без указания ERROR_
     * в начале, но который должен быть у самой константы
     * 
     * @param array $errorParams - дополнительные параметры для ошибок
     * @return string
     */
    protected static function checkLangCode($langCode, string $prefixErrorCode, array $errorParams = [])
    {
        if (!isset($langCode))
            throw new Exception(Loc::getMessage('ERROR_' . $prefixErrorCode . '_LANG', $errorParams));
        
        $value = Loc::getMessage($langCode);
        if (empty($value))
            throw new Exception(
                Loc::getMessage('ERROR_' . $prefixErrorCode . '_EMPTY_LANG', $errorParams + [
                        'LANG_CODE' => $langCode
                    ])
            );
        return $value;
    }

    /**
     * У переданного значения параметра $name убирает префикс, т.е. текст в значении, идущий до последней
     * точки. Возвращает либо то же самое значение, что было передано, если префикса не окажется, либо то,
     * что стоит после префикса
     *
     * @param string $name - название параметра
     *
     * @return string
     */
    protected static function getNameWithoutPrefix(string $name)
    {
        return preg_match('/\w+\.(\S+)/', $name, $nameParts) ? $nameParts[1] : $name;
    }

    /**
     * Создание типа инфоблока
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return void
     * @throws
     */
    protected function initIBlockTypesOptions(string $constName, array $optionValue)
    {
        if (!Loader::includeModule('iblock')) return;

        $iblockTypeID = constant($constName);
        if (CIBlockType::GetList([], ['ID' => $iblockTypeID])->Fetch())
            return;

        $title = self::checkLangCode($optionValue['LANG_CODE'], 'IBLOCK_TYPE', ['TYPE' => $constName]);
        $data = ['ID' => $iblockTypeID, 'LANG' => ['RU' => ['NAME' => $title]]]
              + array_filter($optionValue, function($key) {
                    return !in_array($key, ['LANG_CODE']);
                }, ARRAY_FILTER_USE_KEY)
              + ['SECTIONS' => 'Y'];

        $list = new CIBlockType();
        if (!$list->Add($data))
            throw new Exception(
                Loc::getMessage('ERROR_IBLOCK_TYPE_CREATING', ['TYPE' => $constName])
                . PHP_EOL . $list->LAST_ERROR
            );
    }

    /**
     * По значению в параметре $value возвращает либо само значение, если оно имеет численный тип или состоит только
     * из цифр, либо идентификатор элемента какой-то группы из константы OPTIONS у модуля, название которой указано
     * в параметре $category
     *
     * @param $value - название константы модуля
     * @param string $category - название категории группы настроек, которая используется в константе OPTIONS
     *
     * @return mixed
     */
    protected function getCategoryIDByValue($value, string $category)
    {
        $methodName = 'get' . $category;
        if (
            empty($value)
            || (!is_integer($value) && !is_string($value))
            || (
                (is_integer($value) || preg_match('/^\d+$/', $value))
                && (($IDValue = intval($value)) < 1)
            )
            || (
                is_string($value)
                && empty($IDValue = $this->getOptionParameter()->$methodName($value))
            )
        ) return false;

        return is_array($IDValue) && isset($IDValue['ID']) ? $IDValue['ID'] : $IDValue;
    }

    /**
     * На основе прав доступа к конкретным группам пользователей, указанных в входном параметре $permissions,
     * создает и возвращает готовый массив с правами доступа и идентификаторами конкретных пользовательских
     * групп.
     * В параметре $permissions права досупа указываются так
     *      "ключ" - либо идентификатор существующей в системе группы, либо строковые значение с именем
     *      константы, значение которой хранит либо идентификатор, либо массив идентификаторов пользовательских
     *      групп, либо
     *      "значение" - код права доступа
     *
     * @param array $permissions - права доступа
     * @return array
     */
    protected function prepareGroupPermissions(array $permissions)
    {
        $resultPermissions = [];
        foreach ($permissions as $groupId => $accessValue) {
            if (is_integer($groupId)) {
                $resultPermissions[$groupId] = $accessValue;

            } elseif (
                is_string($groupId) && !empty($groupId)
                && defined($groupId) && !empty($groupId = constant($groupId))
             ) {
                if (!is_array($groupId)) $groupId = [$this->getCategoryIDByValue($groupId, 'UserGroup') ?: $groupId];

                foreach ($groupId as $gID) {
                    $resultPermissions[$gID] = $accessValue;
                }
            }
        }
        return $resultPermissions;
    }

    /**
     * Создание инфоблока
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return integer
     * @throws
     */
    protected function initIBlocksOptions(string $constName, array $optionValue)
    {
        // Инфоблок может создаваться в другом типе инфоблока
        if (!Loader::includeModule('iblock')) return;

        $title = self::checkLangCode($optionValue['LANG_CODE'], 'IBLOCK', ['#IBLOCK#' => $constName]);
        $data = [
                    'ACTIVE' => 'Y',
                    'NAME' => $title,
                    'CODE' => constant($constName),
                    'IBLOCK_TYPE_ID' => constant($optionValue['IBLOCK_TYPE_ID']),
                    /**
                     * VERSION определяет способ хранения значений свойств элементов инфоблока
                     *     1 - в общей таблице
                     *     2 - в отдельной
                     * Но выбрано строго 2, так ка при работе с множественными значениями свойств
                     * инфоблока могут быть проблемы из-за того, что при запросе элементов через
                     * GetList на каждое значение свойства будет дан столько же раз тот же элемент
                     */
                    'VERSION' => 2
                ]
              + array_filter($optionValue, function($key) {
                    return !in_array($key, ['LANG_CODE', 'PERMISSIONS']);
                }, ARRAY_FILTER_USE_KEY)
              + [
                    'DETAIL_PAGE_URL' => '',
                    'LIST_PAGE_URL' => '',
                    'WORKFLOW' => 'N',
                    'BIZPROC' => 'N',
                    'SITE_ID' => self::getDefaultSiteID()
                ];

        $iblock = new CIBlock;
        $iblockId = $iblock->Add($data);
        if (!$iblockId)
            throw new Exception(
                Loc::getMessage('ERROR_IBLOCK_CREATING', ['#IBLOCK#' => $constName])
                . PHP_EOL . $iblock->LAST_ERROR
            );

        CIBlock::SetPermission(
            $iblockId,
            self::prepareGroupPermissions(
                array_replace([self::ADMIN_GROUP_ID => 'X', self::ALL_USER_GROUP_ID => 'R'], $optionValue['PERMISSIONS'] ?: [])
            )
        );

        return $iblockId;
    }

    /**
     * Создание значений для свойства инфоблока типа "Список"
     * 
     * @param int $propertyId - ID свойства инфоблока
     * @param array $propertyValues - список значений
     * @param string $langCode - префикс к языковым константам для названий значений
     * @return array
     */
    protected function addIBlockPropertyListValues(int $propertyId, array $propertyValues, string $langCode)
    {
        $values = [];
        $ids = [];
        $list = new CIBlockPropertyEnum;
        foreach ($propertyValues as $unit) {
            $value = Loc::getMessage(($langCode ? $langCode . '_' : '') . $unit['LANG_CODE']);
            $lowerCaseValue = strtolower($value);
            if (empty($value) || in_array($lowerCaseValue, $values))
                continue;

            $values[] = $lowerCaseValue;

            $listUnitId = intval(
                                $list->Add(
                                    ['PROPERTY_ID' => $propertyId, 'VALUE' => $value] +
                                    array_filter(
                                        $unit,
                                        function($key) {
                                            return !in_array($key, ['ID', 'LANG_CODE']);
                                        },
                                        ARRAY_FILTER_USE_KEY
                                    )
                                )
                            );

            if (!$listUnitId) {
                $property = CIBlockProperty::GetById($propertyId)->Fetch();
                throw new Exception(
                            Loc::getMessage(
                                'ERROR_BAD_IBLOCK_PROPERTY_LIST_CREATING',
                                [
                                    '#NAME#' => $value,
                                    '#PROPERTY_NAME#' => $property['NAME'],
                                    '#IBLOCK_NAME#' => CIBlock::GetById($property['IBLOCK_ID'])->Fetch()['NAME']
                                ]
                            )
                        );
            }

            $ids['VALUES'][] = $listUnitId;
            $ids[$unit['LANG_CODE'] . '_ID'] = $listUnitId;
        }
        return $ids;
    }

    /**
     * Создание свойств инфоблока
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return integer
     * @throws
     */
    protected function initIBlockPropertiesOptions(string $constName, array $optionValue)
    {
        if (!Loader::includeModule('iblock')) return;

        $title = self::checkLangCode($optionValue['LANG_CODE'], 'IBLOCK_PROPERTY', ['#PROPERTY#' => $constName]);
        $iblockID = $this->getCategoryIDByValue(constant($optionValue['IBLOCK_ID']), 'IBlocks');
        if (!$iblockID) throw new Exception(Loc::getMessage('ERROR_BAD_PROPERTY_IBLOCK', ['#PROPERTY#' => $constName]));

        $data = [
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => $iblockID,
                    'NAME' => $title,
                    'CODE' => self::getNameWithoutPrefix(constant($constName))
                ]
              + array_filter($optionValue, function($key) {
                    return !in_array($key, ['LANG_CODE', 'LIST_VALUES']);
                }, ARRAY_FILTER_USE_KEY)
              + [
                    'PROPERTY_TYPE' => 'S',
                    'IS_REQUIRED' => 'N',
                    'MULTIPLE' => 'N',
                    'MULTIPLE_CNT' => 5,
                    'LIST_TYPE' => 'L'
                ];

        if (array_key_exists('LINK_IBLOCK_ID', $data))
            $data['LINK_IBLOCK_ID'] = $this->getCategoryIDByValue(constant($data['LINK_IBLOCK_ID']), 'IBlocks');

        $property = new CIBlockProperty;
        $propertyId = $property->Add($data);
        if (!$propertyId)
            throw new Exception(
                Loc::getMessage('ERROR_IBLOCK_PROPERTY_CREATING', ['#PROPERTY#' => $constName])
                . PHP_EOL . $property->LAST_ERROR
            );

        $result = ['ID' => $propertyId];
        if (
            ($optionValue['PROPERTY_TYPE'] == 'L') && !$optionValue['USER_TYPE']
            && !empty($optionValue['LIST_VALUES'])
        ) $result += $this->addIBlockPropertyListValues(
                                    $result['ID'],
                                    $optionValue['LIST_VALUES'],
                                    $optionValue['LANG_CODE'] ?: ''
                                );
        return $result;
    }

    /**
     * Создания поля списка, в роли которого выступает инфоблок
     *
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     *
     * @return array
     * @throws
     */
    protected function initIBlockListFieldsOptions(string $constName, array $optionValue)
    {
        static $listFields = [];

        $fieldCode = self::getNameWithoutPrefix(trim(strval(constant($constName))));
        if (empty($fieldCode)) throw new Exception(Loc::getMessage('ERROR_LIST_FIELD_EMPTY_CODE', ['#CODE#' => $constName]));

        $iblockID = $this->getCategoryIDByValue(constant($optionValue['IBLOCK_ID']), 'IBlocks');
        if (!$iblockID) throw new Exception(Loc::getMessage('ERROR_BAD_LIST_FIELD_IBLOCK', ['#CODE#' => $constName]));

        $caption = isset($optionValue['LANG_CODE']) ? Loc::getMessage($optionValue['LANG_CODE']) : null;
        $sortValue = is_integer($optionValue['SORT']) && ($optionValue['SORT'] > 0) ? $optionValue['SORT'] : 10;

        if (empty($listFields[$iblockID])) $listFields[$iblockID] = new ListFieldList($iblockID);
        $result = $listFields[$iblockID]->setField($fieldCode, $caption, $sortValue);
        $listFields[$iblockID]->saveList();

        if (!$result) throw new Exception(Loc::getMessage('ERROR_BAD_LIST_IB_PROPERTY', ['#CODE#' => $constName]));

        return $result;
    }

    /**
     * Создание значений для пользовательского поля типа "Список"
     * 
     * @param int $fieldId - ID пользовательского поля
     * @param array $fieldValues - значения пользовательского поля
     * @param string $langCode - префикс к языковым константам для названий значений поля
     * @return array
     */
    protected function addListValues(int $fieldId, array $fieldValues, string $langCode)
    {
        $units = [];
        $values = [];
        $newN = 0;
        foreach ($fieldValues as $unit) {
            $value = Loc::getMessage(($langCode ? $langCode . '_' : '') . $unit['LANG_CODE']);
            if (empty($value)) continue;

            if (!in_array($value, $values)) {
                $units['n' . $newN] = ['VALUE' => $value]
                                    + array_filter($unit, function($key) {
                                                return !in_array(strtoupper($key), ['LANG_CODE', 'ID']);
                                            }, ARRAY_FILTER_USE_KEY);
                ++$newN;
            }

            $values[$unit['LANG_CODE']] = $value;
        }

        if (empty($units)) return [];

        (new CUserFieldEnum())->SetEnumValues($fieldId, $units);
        $ids = [];
        $savedUnits = CUserFieldEnum::GetList([], ['USER_FIELD_ID' => $fieldId]);
        while ($saved = $savedUnits->Fetch()) {
            foreach ($values as $key => $value) {
                if ($value != $saved['VALUE']) continue;

                $ids['VALUES'][] = intval($saved['ID']);
                $ids[$key . '_ID'] = intval($saved['ID']);
            }
        }
        return $ids;
    }

    /**
     * Добавляет новое пользовательское поле, прежде устанавливая дополнительные свойства поля,
     * которые не были указаны в переданных данных.
     * 
     * @param string $entityId - код поля
     * @param string $constName - название константы
     * @param array $fieldData - данные нового поля
     * @return array
     * @throws
     */
    protected function addUserField(string $entityId, string $constName, array $fieldData) 
    {
        global $APPLICATION;

        $fields = [
                'ENTITY_ID' => $entityId,
                'FIELD_NAME' => constant($constName),
                'USER_TYPE_ID' => $fieldData['TYPE']
            ] + $fieldData + [
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 500,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'N',
                'EDIT_IN_LIST' => 'N',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => []
            ];
        if (!preg_match('/^uf_/i', $fields['FIELD_NAME']))
            throw new Exception(Loc::getMessage('ERROR_BAD_USER_FIELD_NAME', ['NAME' => $constName]));

        if (!empty($fields['LANG_CODE'])) {
            $langValue = Loc::getMessage($fields['LANG_CODE']);
            unset($fields['LANG_CODE']);
            foreach ([
                        'EDIT_FORM_LABEL', 'LIST_COLUMN_LABEL', 'LIST_FILTER_LABEL',
                        'ERROR_MESSAGE', 'HELP_MESSAGE'
                    ] as $labelUnit) {

                $fields[$labelUnit] = ['ru' => $langValue, 'en' => ''];
            }
        }
        if ($fieldData['TYPE'] == 'vote') {
            if (
                empty($fields['SETTINGS']['CHANNEL_ID'])
                || !defined($fields['SETTINGS']['CHANNEL_ID'])
                || !($channelId = $this->getCategoryIDByValue(constant($fields['SETTINGS']['CHANNEL_ID']), 'VoteChannels'))
            ) throw new Exception(Loc::getMessage('ERROR_BAD_USER_FIELD_VOTE_CHANNEL', ['NAME' => $constName]));
            $fields['SETTINGS']['CHANNEL_ID'] = $channelId;

        } elseif (preg_match('/^iblock_(element|section)$/', $fieldData['TYPE'])) {
            if (
                empty($fields['SETTINGS']['IBLOCK_ID'])
                || !defined($fields['SETTINGS']['IBLOCK_ID'])
                || !($iblockId = $this->getCategoryIDByValue(constant($fields['SETTINGS']['IBLOCK_ID']), 'IBlocks'))
            ) throw new Exception(Loc::getMessage('ERROR_BAD_USER_FIELD_IBLOCK', ['NAME' => $constName]));
            $fields['SETTINGS']['IBLOCK_ID'] = $iblockId;

        } elseif (!in_array($fieldData['TYPE'], ['crm'])) {
            $fields['SETTINGS'] += [
                'DEFAULT_VALUE' => '',
                'SIZE' => '20',
                'ROWS' => '1',
                'MIN_LENGTH' => '0',
                'MAX_LENGTH' => '0',
                'REGEXP' => ''
            ];
        }

        $fieldEntity = new CUserTypeEntity();
        $fieldId = $fieldEntity->Add($fields);
        if (!$fieldId)
            throw new Exception(
                Loc::getMessage('ERROR_USER_FIELD_CREATING', ['NAME' => $constName]) . PHP_EOL .
                $APPLICATION->GetException()->GetString()
            );
        
        $result = ['ID' => intval($fieldId)];
        if (($fieldData['TYPE'] == 'enumeration') && !empty($fieldData['LIST_VALUES']))
            $result += $this->addListValues($result['ID'], $fieldData['LIST_VALUES'], $fieldData['LANG_CODE'] ?: '');

        return $result;
    }

    /**
     * Создает нужный highloadblock, если его нет, иначе вызывает исключение.
     * Возвращает ID созданного highloadblock.
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return void|integer
     * @throws
     */
    protected function initHighloadBlockOptions(string $constName, array $optionValue)
    {
        if (!Loader::includeModule('highloadblock')) return;

        $codeName = strtolower(constant($constName));
        $name = preg_replace_callback(
            '/(?:^|_)(\w)/',
            function($part) {
                return strtoupper($part[1]);
            },
            $codeName
        );
        $result = HighloadBlockTable::add(
            [
                'NAME' => $name,
                'TABLE_NAME' => preg_replace('/[^a-z\d]+/i', '', $codeName)
            ]
        );
        if (!$result->isSuccess(true))
            throw new Exception(
                Loc::getMessage('ERROR_HIGHLOAD_CREATING', ['NAME' => $optionName])
                . PHP_EOL . implode(PHP_EOL, $result->getErrorMessages())
            );
        $hlId = $result->GetId();
        if (
            !empty($optionValue['LANG_CODE'])
            && !empty($title = Loc::getMessage($optionValue['LANG_CODE']))
        ) HighloadBlockLangTable::add(['ID' => $hlId, 'LID' => LANGUAGE_ID, 'NAME' => $title]);

        return $hlId;
    }

    /**
     * Создает пользовательское поле для highloadblock
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return mixed
     */
    protected function initHighloadFieldsOptions(string $constName, array $optionValue) 
    {
        if (!defined($optionValue['HBLOCK_ID'])) return;

        $hlID = $this->getCategoryIDByValue(constant($optionValue['HBLOCK_ID']), 'HighloadBlock');
        if (empty($hlID))
            throw new Exception(Loc::getMessage('ERROR_BAD_HBLOCK_ID', ['#NAME#' => $constName]));

        $entityId = 'HLBLOCK_' . $hlID;
        return $this->addUserField(
                            $entityId, $constName,
                            array_filter(
                                $optionValue, function($key) {
                                    return $key != 'HBLOCK_ID';
                                }, ARRAY_FILTER_USE_KEY
                            )
                        );
    }

    /**
     * Создание пользовательского поля для пользователей
     * 
     * @param string $constName - название константы
     * @param array $optionValue - значение опции
     * @return mixed
     */
    protected function initUserFieldsOptions(string $constName, array $optionValue)
    {
        return $this->addUserField('USER', $constName, $optionValue);
    }
    
    /**
     * Создание всех опций
     *
     * @return  void
     */
    protected function initOptions() 
    {
        $savedData = json_decode(Option::get('main', 'saved.' . $this->MODULE_ID, false, \CSite::GetDefSite()), true)
                    ?: [];

        foreach ($this->getModuleConstantValue('OPTIONS') as $methodNameBody => $optionList) {
            $methodName = 'init' . $methodNameBody . 'Options';
            if (!method_exists($this, $methodName)) continue;

            foreach ($optionList as $constName => $optionValue) {
                if (!defined($constName)) continue;

                $constValue = constant($constName);
                $value = empty($savedData[$methodNameBody][$constValue])
                       ? $this->$methodName($constName, $optionValue)
                       : $savedData[$methodNameBody][$constValue];
                if (!isset($value)) continue;
                $optionMethod = 'add' . $methodNameBody;
                $this->getOptionParameter()->$optionMethod($constValue, $value);
            }
        }
    }

    /**
     * Выполняется основные операции по установке модуля
     * 
     * @return void
     */
    protected function runInstallMethods()
    {
        $this->initOptions();
    }

    /**
     * Проверяет у модуля наличие класса Employment в своем подпространстве имен EventHandles,
     * а так же наличие у него метода, название которого передано в параметре $methodName.
     * В случае успеха вызывает метод у своего Employment
     * 
     * @param string $methodName - название метода, который должен выступать как обработчик события
     * @return void
     */
    protected function checkAndRunModuleEvent(string $methodName)
    {
        $moduleEmployment = $this->nameSpaceValue . '\\EventHandles\\Employment';
        if (!class_exists($moduleEmployment) || !method_exists($moduleEmployment, $methodName))
            return;

        $moduleEmployment::$methodName();
    }

    /**
     * Функция, вызываемая при установке модуля
     *
     * @param bool $stopAfterInstall - указывает модулю остановить после
     * своей установки весь процесс установки
     * 
     * @return void
     */
    public function DoInstall(bool $stopAfterInstall = true) 
    {
        global $APPLICATION;
        RegisterModule($this->MODULE_ID);
        $this->initDefinedContants();

        try {
            if (!$this->getOptionParameter()) {
                throw new Exception(Loc::getMessage('ERROR_NO_OPTION_CLASS', ['#CLASS#' => $this->optionParameterClass]));
            }

            Employment::setBussy();
            $this->checkAndRunModuleEvent('onBeforeModuleInstallationMethods');
            $this->runInstallMethods();
            $this->getOptionParameter()->setConstants(array_keys($this->definedContants));
            $this->getOptionParameter()->setInstallShortData([
                'INSTALL_DATE' => date('Y-m-d H:i:s'),
                'VERSION' => $this->MODULE_VERSION,
                'VERSION_DATE' => $this->MODULE_VERSION_DATE,
            ]);
            $this->getOptionParameter()->save();
            $this->checkAndRunModuleEvent('onAfterModuleInstallationMethods');
            Employment::setFree();
            if ($stopAfterInstall) {
                $APPLICATION->IncludeAdminFile(
                    Loc::getMessage($this->subLocTitle . 'MODULE_WAS_INSTALLED'),
                    $this->moduleClassPath . '/step1.php'
                );
            }

        } catch (Exception $error) {
            $this->removeAll();
            $APPLICATION->ThrowException($error->getMessage());
            Employment::setFree();
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage($this->subLocTitle . 'MODULE_NOT_INSTALLED'),
                $this->moduleClassPath . '/error.php'
            );
        }
    }

    /**
     * Удаление пользовательского поля
     * 
     * @param string $entityId - код поля
     * @param string $constName - название константы с символьным кодом поля
     * @return void
     */
    protected function removeUserFields(string $entityId, string $constName) 
    {
        $entityField = new CUserTypeEntity();
        $userFields = CUserTypeEntity::GetList(
            [], ['ENTITY_ID' => $entityId, 'FIELD_NAME' =>  constant($constName)]
        );
        while ($field = $userFields->Fetch()) {
            $entityField->Delete($field['ID']);
        }
    }

    /**
     * Удаление highloadblock, созданного модулем при установке
     * 
     * @param string $constName - название константы
     * @return void
     */
    protected function removeHighloadBlockOptions(string $constName) 
    {
        if (!Loader::includeModule('highloadblock')) return;

        $codeName = strtolower(constant($constName));
        $name = preg_replace_callback(
            '/(?:^|_)(\w)/',
            function($part) {
                return strtoupper($part[1]);
            },
            $codeName
        );
        $hlUnt = HighloadBlockTable::GetList(['filter' => ['NAME' => $name]])->Fetch();
        if (!$hlUnt) return;

        HighloadBlockTable::delete($hlUnt['ID']);
    }

    /**
     * Удаление пользовательского поля для highloadblock. Метод нужен, только, если
     * добавляются пользовательские поля для уже существующих highloadblock, так как
     * поля для создаваемых модулем highloadblock автоматически удалятся вместе с самим
     * highloadblock
     * 
     * @param string $constName - название константы
     * @return void
     */
    protected function removeHighloadFieldsOptions(string $constName)
    {
        (new CUserTypeEntity())->Delete($this->getOptionParameter()->getHighloadFields(constant($constName))['ID']);
    }

    /**
     * Удаление пользовательского поля для пользователей
     * 
     * @param string $constName - название константы
     * @return void
     */
    protected function removeUserFieldsOptions(string $constName) 
    {
        $this->removeUserFields('USER', $constName);
    }

    /**
     * Удаление типа инфоблока
     * 
     * @param $constName - название константы
     * @return void
     */
    protected function removeIBlockTypesOptions(string $constName)
    {
        if (!Loader::includeModule('iblock')) return;

        $iblockTypeID = constant($constName);
        $iblocks = CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N']);
        while ($iblock = $iblocks->Fetch()) {
            if ($iblock['IBLOCK_TYPE_ID'] != $iblockTypeID) continue;

            CIBlock::Delete($iblock['ID']);
        }

        CIBlockType::Delete($iblockTypeID);
    }

    /**
     * Удаление инфоблока. Метод нужен, не смотря на то, что при удалении типа инфоблока
     * удаляются и все его инфоблоки, но модуль можно заставить создавать инфоблоки в
     * других типах инфоблоков
     * 
     * @param $constName - название константы
     * @return void
     */
    protected function removeIBlocksOptions(string $constName)
    {
        if (!Loader::includeModule('iblock')) return;

        $iblockID = $this->getCategoryIDByValue(constant($constName), 'IBlocks');
        if (empty($iblockID)) return;

        CIBlock::Delete($iblockID);
    }
    
    /**
     * Удаление свойств инфоблока
     * 
     * @param string $constName - название константы
     * @return void
     */
    protected function removeIBlockPropertiesOptions(string $constName)
    {
        if (
            !Loader::includeModule('iblock')
            || empty($iblockProperty = $this->getOptionParameter()->getIBlockProperties(constant($constName)))
        ) return;

        CIBlockProperty::Delete($iblockProperty['ID']);
    }

    /**
     * Удаление всех созданных модулем данных согласно прописанным настройкам в
     * OPTIONS
     * 
     * @return void
     */
    protected function removeOptions() 
    {
        $saveDataWhenDeleted = constant(get_called_class() . '::SAVE_OPTIONS_WHEN_DELETED') === true;
        $savedData = [];
        foreach (array_reverse($this->getModuleConstantValue('OPTIONS')) as $methodNameBody => $optionList) {
            $methodName = $saveDataWhenDeleted && !in_array(strtolower($methodNameBody), ['agents'])
                        ? 'get' . $methodNameBody
                        : 'remove' . $methodNameBody . 'Options';

            foreach ($optionList as $constName => $optionValue) {
                if (!defined($constName)) continue;

                if ($saveDataWhenDeleted) {
                    $constValue = constant($constName);
                    $data = $this->getOptionParameter()->$methodName($constValue);
                    if (empty($data)) continue;
                    $savedData[$methodNameBody][$constValue] = $data;

                } elseif (method_exists($this, $methodName)) {
                    $this->$methodName($constName, $optionValue);
                }
            }
        }
        if (!$saveDataWhenDeleted) {
            Option::delete('main', ['name' => 'saved.' . $this->MODULE_ID]);

        } elseif (!empty($savedData)) {
            Option::set('main', 'saved.' . $this->MODULE_ID, json_encode($savedData));
        }
    }

    /**
     * Выполняется основные операции по удалению модуля
     * 
     * @return void
     */
    protected function runRemoveMethods()
    {
        $this->removeOptions();
    }

    /**
     * Основной метод, очищающий систему от данных, созданных им
     * при установке
     * 
     * @return void
     */
    protected function removeAll()
    {
        if ($this->getOptionParameter()) {
            $this->definedContants = array_fill_keys($this->getOptionParameter()->getConstants() ?? [], '');
            array_walk($this->definedContants, function(&$value, $key) { $value = constant($key); });
            $this->runRemoveMethods();
        }
        UnRegisterModule($this->MODULE_ID); // удаляем модуль
    }

    protected function getOptionParameter()
    {
        if (!class_exists($this->optionParameterClass)) {
            return false;
        }

        if ($this->optionParameter) {
            return $this->optionParameter;
        }

        $optionParameterClass = $this->optionParameterClass;
        return $this->optionParameter = new $optionParameterClass($this->MODULE_ID);
    }

    /**
     * Функция, вызываемая при удалении модуля
     *
     * @param bool $stopAfterDeath - указывает модулю остановить после
     * своего удаления весь процесс удаления
     * 
     * @return void
     */
    public function DoUninstall(bool $stopAfterDeath = true) 
    {
        global $APPLICATION;
        Loader::IncludeModule($this->MODULE_ID);
        Employment::setBussy();
        $this->checkAndRunModuleEvent('onBeforeModuleRemovingMethods');
        $this->removeAll();
        Option::delete($this->MODULE_ID);
        $this->checkAndRunModuleEvent('onAfterModuleRemovingMethods');
        Employment::setFree();
        if ($stopAfterDeath)
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage($this->subLocTitle . 'MODULE_WAS_DELETED'),
                $this->moduleClassPath . '/unstep1.php'
            );
    }

}
