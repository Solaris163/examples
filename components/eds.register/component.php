<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * ссылка на класс компонента для IDE
 * @var $this AxbitEdsRegister
 */

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

global $USER;


// найти id инфоблока "Реестр ЭЦП"
$EDSRegisterIblockId = \Axbit\Tools\Iblocks::getOneIblockId('custom', 'eds_register');
if (!$EDSRegisterIblockId) die('Не найден id инфоблока');
// найти id свойств для инфоблока "Реестр ЭЦП"
$arProperties = \Axbit\Tools\Iblocks::getProperties($EDSRegisterIblockId);
$PROPERTY_USER_ID_ID = $arProperties['USER_ID']['ID'] ?? false; // id сотрудника
$PROPERTY_REGISTRATION_DATE_ID = $arProperties['REGISTRATION_DATE']['ID'] ?? false; // дата оформления
$PROPERTY_EXPIRY_DATE_ID = $arProperties['EXPIRY_DATE']['ID'] ?? false; // дата окончания


$gridId = 'eds_register';
$navigationId = 'eds_register_navigation';

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$arQueryList = $request->getQueryList()->toArray();


// данные из фильтра на странице
$filterOption = new Bitrix\Main\UI\Filter\Options($gridId);
$filterData = $filterOption->getFilter([]);

// грид и навигация
$grid_options = new GridOptions($gridId);
$sort = $grid_options->GetSorting(['sort' => ['USER_LAST_NAME' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$nav_params = $grid_options->GetNavParams();
$nav = new PageNavigation($navigationId);
$nav->allowAllRecords(false)
    ->setPageSize($nav_params['nPageSize'])
    ->initFromUri();


$offset = $nav->getOffset();
$pageSize = $nav->getLimit();

// при запросе файла .xlsx брать параметры навигации из UserOptions
if (isset($arQueryList['type']) && $arQueryList['type'] == 'xlsx') {
    $arNavData = \CUserOptions::GetOption('custom', $navigationId.'_data');
    $offset = $arNavData['offset'] ?? '';
    $pageSize = $arNavData['pageSize'] ?? '';
} else {
    // сохранить параметры в UserOptions, чтобы использовать их при запросе файла .xlsx
    \CUserOptions::SetOption('custom', $navigationId.'_data', ['offset' => $offset, 'pageSize' => $pageSize]);
}


$sort = $sort['sort'];

$arItems = [];
$arRuntime = [
    // получить свойство "Сотрудник" в инфоблоке "Реестр ЭЦП"
    new \Bitrix\Main\Entity\ReferenceField(
        'PROPERTY_USER_ID',
        '\Bitrix\Iblock\ElementPropertyTable',
        array(
            '=this.ID' => 'ref.IBLOCK_ELEMENT_ID',
            '=ref.IBLOCK_PROPERTY_ID' => new \Bitrix\Main\DB\SqlExpression('?i', $PROPERTY_USER_ID_ID)
        )
    ),
    // свойство REGISTRATION_DATE
    new \Bitrix\Main\Entity\ReferenceField(
        'PROPERTY_REGISTRATION_DATE', //
        '\Bitrix\Iblock\ElementPropertyTable',
        array(
            '=this.ID' => 'ref.IBLOCK_ELEMENT_ID',
            '=ref.IBLOCK_PROPERTY_ID' => new \Bitrix\Main\DB\SqlExpression('?i', $PROPERTY_REGISTRATION_DATE_ID)
        )
    ),
    // свойство EXPIRY_DATE
    new \Bitrix\Main\Entity\ReferenceField(
        'PROPERTY_EXPIRY_DATE', //
        '\Bitrix\Iblock\ElementPropertyTable',
        array(
            '=this.ID' => 'ref.IBLOCK_ELEMENT_ID',
            '=ref.IBLOCK_PROPERTY_ID' => new \Bitrix\Main\DB\SqlExpression('?i', $PROPERTY_EXPIRY_DATE_ID)
        )
    ),
    // сотрудник
    new \Bitrix\Main\Entity\ReferenceField(
        'USER', //
        '\Bitrix\Main\UserTable',
        array(
            '=this.PROPERTY_USER_ID.VALUE' => 'ref.ID',
        )
    ),
];


$arFilter = array('IBLOCK_ID' => $EDSRegisterIblockId, 'ACTIVE' => 'Y');


// сотрудник
if (!empty($filterData['USER_ID'])) {
    $arFilter["PROPERTY_USER_ID.VALUE"] = $filterData['USER_ID'];
}
// дата оформления
if (!empty($filterData['REGISTRATION_DATE_from'])) {
    $arFilter[">=PROPERTY_REGISTRATION_DATE_VALUE"] = $DB->FormatDate($filterData['REGISTRATION_DATE_from'], FORMAT_DATETIME, 'YYYY-MM-DD');
}
if (!empty($filterData['REGISTRATION_DATE_to'])) {
    $arFilter["<=PROPERTY_REGISTRATION_DATE_VALUE"] = $DB->FormatDate($filterData['REGISTRATION_DATE_to'], FORMAT_DATETIME, 'YYYY-MM-DD');
}
// дата окончания
if (!empty($filterData['EXPIRY_DATE_from'])) {
    $arFilter[">=PROPERTY_EXPIRY_DATE_VALUE"] = $DB->FormatDate($filterData['EXPIRY_DATE_from'], FORMAT_DATETIME, 'YYYY-MM-DD');
}
if (!empty($filterData['EXPIRY_DATE_to'])) {
    $arFilter["<=PROPERTY_EXPIRY_DATE_VALUE"] = $DB->FormatDate($filterData['EXPIRY_DATE_to'], FORMAT_DATETIME, 'YYYY-MM-DD');
}


// получить элементы
$dbRes = \Bitrix\Iblock\ElementTable::getList(array(
    'limit' => $pageSize,
    'offset' => $offset,
    'count_total' => 1,
    'order' => $sort,
    'select' => array(
        'ID',
        'PROPERTY_REGISTRATION_DATE_VALUE' => 'PROPERTY_REGISTRATION_DATE.VALUE',
        'PROPERTY_EXPIRY_DATE_VALUE' => 'PROPERTY_EXPIRY_DATE.VALUE',
        'USER_NAME' => 'USER.NAME',
        'USER_LAST_NAME' => 'USER.LAST_NAME',
        'USER_SECOND_NAME' => 'USER.SECOND_NAME',
    ),
    'filter' => $arFilter,
    'runtime' => $arRuntime,
));
$allSelectedCount = $dbRes->getCount();
$nav->setRecordCount($allSelectedCount);
while ($arRes = $dbRes->fetch()) {
    $arItems[$arRes['ID']] = $arRes;
}

// фильтр для вывода на странице
$ui_filter = [
    [
        // фильтр по сотруднику
        'id' => 'USER_ID',
        'name' => 'Сотрудник',
        'type' => 'entity_selector',
        'default' => true,
        'params' => [
            'multiple' => 'N',
            'dialogOptions' => [
                'height' => 240,
                'context' => 'filter',
                'entities' => [
                    [
                        'id' => 'user',
                        'options' => [
                            'inviteEmployeeLink' => false
                        ],
                    ],
                ]
            ],
        ],
    ],
    [
        // фильтр по дате оформления
        'id' => 'REGISTRATION_DATE',
        'name' => 'Дата оформления',
        'type'=>'date',
        'default' => true,
        "exclude" => array(
            \Bitrix\Main\UI\Filter\DateType::YESTERDAY, // исключить вчера
            \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY, // исключить сегодня
            \Bitrix\Main\UI\Filter\DateType::TOMORROW, // исключить завтра
            \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK, // исключить текущая неделя
            \Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
            \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
        )
    ],
    [
        // фильтр по дате окончания
        'id' => 'EXPIRY_DATE',
        'name' => 'Дата окончания',
        'type'=>'date',
        'default' => true,
        "exclude" => array(
            \Bitrix\Main\UI\Filter\DateType::YESTERDAY, // исключить вчера
            \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY, // исключить сегодня
            \Bitrix\Main\UI\Filter\DateType::TOMORROW, // исключить завтра
            \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK, // исключить текущая неделя
            \Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
            \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
            \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
        )
    ],
];

// заголовки столбцов грида
$columns = [];
$columns[] = ['id' => 'NAME', 'name' => 'ФИО владельца', 'default' => true, 'width' => 300, 'sort' => 'USER_LAST_NAME'];
$columns[] = ['id' => 'REGISTRATION_DATE', 'name' => 'Дата оформления', 'default' => true, 'width' => 180, 'align' => 'center', 'sort' => 'PROPERTY_REGISTRATION_DATE_VALUE'];
$columns[] = ['id' => 'EXPIRY_DATE', 'name' => 'Дата окончания', 'default' => true, 'width' => 180, 'sort' => 'PROPERTY_EXPIRY_DATE_VALUE'];

$arRows = [];

foreach ($arItems as $arItem) {
    $arRows[] = [
        'data' => [
            "NAME" => $arItem['USER_LAST_NAME'] . ' ' . $arItem['USER_NAME'] . ' ' . $arItem['USER_SECOND_NAME'],
            "REGISTRATION_DATE" => $DB->FormatDate($arItem['PROPERTY_REGISTRATION_DATE_VALUE'], "YYYY-MM-DD", "DD.MM.YYYY"),
            "EXPIRY_DATE" => $DB->FormatDate($arItem['PROPERTY_EXPIRY_DATE_VALUE'], "YYYY-MM-DD", "DD.MM.YYYY"),
        ],
    ];
}

// скачать файл
if (isset($arQueryList['type']) && $arQueryList['type'] == 'xlsx') {
    $arSpreadsheet = $this::prepareSpreadsheet($columns, $arRows);
    $arSizes = [37, 18, 18];
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Axbit/Tools/GetSpreadsheet.php';
    Axbit\Tools\getSpreadsheet::getSpreadsheet($arSpreadsheet, 'Реестр ЭЦП', $arSizes);
}


$arResult['GRID_ID'] = $gridId;
$arResult['NAV_OBJECT'] = $nav;
$arResult['UI_FILTER'] = $ui_filter;
$arResult['COLUMNS'] = $columns;
$arResult['ROWS'] = $arRows;
$arResult['TOTAL_ROWS_COUNT'] = $allSelectedCount;

$this->IncludeComponentTemplate();