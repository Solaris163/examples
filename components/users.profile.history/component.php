<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * ссылка на класс компонента для IDE
 * @var $this AxbitUsersProfileHistory
 */

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Text\HtmlFilter;

$gridId = 'profiles_history';
$cancelSearch = false; // отменить поиск
$arUsers = [];
$arUsersIds = [];
$arHistory = [];

$grid_options = new GridOptions($gridId);
$sort = $grid_options->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$nav_params = $grid_options->GetNavParams();
$nav = new PageNavigation('request_list');
$nav->allowAllRecords(false)
    ->setPageSize($nav_params['nPageSize'])
    ->initFromUri();

// данные из фильтра на странице
$filterOption = new Bitrix\Main\UI\Filter\Options($gridId);
$filterData = $filterOption->getFilter([]);

// фильтр для запроса данных из таблицы БД
$arFilter = [];
if (!empty($filterData['DATE_from'])) {
    $arFilter[">=DATE_INSERT"] = $filterData['DATE_from'];
}
if (!empty($filterData['DATE_to'])) {
    $arFilter["<=DATE_INSERT"] = $filterData['DATE_to'];
}
if (!empty($filterData['USER_ID'])) {
    $arFilter["=USER_ID"] = $filterData['USER_ID'];
}
if (!empty($filterData['UPDATED_BY_ID'])) {
    $arFilter["=UPDATED_BY_ID"] = $filterData['UPDATED_BY_ID'];
}

// поиск по заданной фразе в имени и фамилии
if (!empty($filterData['FIND'])) {

    // получить пользователей, у которых в имени или фамилии содержится искомая фраза
    $arUsers = $this->getUsersBySearchPhrase($filterData['FIND']);

    if (!$arUsers) {
        $cancelSearch = true;
    }

    if (empty($arFilter['=USER_ID']) && empty($arFilter['=UPDATED_BY_ID'])) {
        $arFilter[] = array(
            'LOGIC' => 'OR',
            ['=USER_ID' => array_keys($arUsers)],
            ['=UPDATED_BY_ID' => array_keys($arUsers)],
        );
    } elseif (empty($arFilter['=USER_ID'])) {
        $arFilter['=USER_ID'] = array_keys($arUsers);
    } elseif (empty($arFilter['=UPDATED_BY_ID'])) {
        $arFilter['=UPDATED_BY_ID'] = array_keys($arUsers);
    }
}

$arRows = [];

if (!$cancelSearch) {
    $listParams = array(
        'filter' => $arFilter,
        'count_total' => true,
        'offset'      => $nav->getOffset(),
        'limit'       => $nav->getLimit(),
        'order'       => $sort['sort']
    );

    $historyObject = \Bitrix\Main\UserProfileHistoryTable::getList($listParams);

    $allSelectedCount = $historyObject->getCount();
    $nav->setRecordCount($allSelectedCount);

    $converter = \Bitrix\Main\Text\Converter::getHtmlConverter();
    while($row = $historyObject->fetch($converter)) {
        $arHistory[] = $row;
        if ($row['USER_ID'] && !in_array($row['USER_ID'], $arUsersIds)) {
            $arUsersIds[] = $row['USER_ID'];
        }
        if ($row['UPDATED_BY_ID'] && !in_array($row['UPDATED_BY_ID'], $arUsersIds)) {
            $arUsersIds[] = $row['UPDATED_BY_ID'];
        }
    }
}

// фильтр для вывода на странице
$ui_filter = [
    ['id' => 'DATE', 'name' => 'Дата', 'type'=>'date', 'default' => true],
    ['id' => 'USER_ID', 'name' => 'ID Пользователя', 'type'=>'text', 'default' => true],
    ['id' => 'UPDATED_BY_ID', 'name' => 'ID Кто изменил', 'type'=>'text', 'default' => true],
];

// заголовки столбцов грида
$columns = [];
$columns[] = ['id' => 'DATE_INSERT', 'name' => 'Дата', 'sort' => 'ID', 'default' => true]; // при клике на дату, сортировка будет по id записи
$columns[] = ['id' => 'USER_ID', 'name' => 'Пользователь', 'default' => true];
$columns[] = ['id' => 'EVENT_TYPE', 'name' => 'Событие', 'default' => true];
$columns[] = ['id' => 'UPDATED_BY_ID', 'name' => 'Кто изменил', 'default' => true];
$columns[] = ['id' => 'CHANGES', 'name' => 'Что изменено', 'default' => true];

// типы возможных событий для таблицы с изменениями профилей
$eventTypes = array(
    \Bitrix\Main\UserProfileHistoryTable::TYPE_ADD => 'Добавление',
    \Bitrix\Main\UserProfileHistoryTable::TYPE_UPDATE => 'Изменение',
    \Bitrix\Main\UserProfileHistoryTable::TYPE_DELETE => 'Удаление',
);


// получить имена пользователей
$arUsers = $this->getUsers($arUsersIds);

foreach ($arHistory as $row) {
    // что поменялось
    $strChanges = '';
    if($row["EVENT_TYPE"] == \Bitrix\Main\UserProfileHistoryTable::TYPE_UPDATE) {
        $records = \Bitrix\Main\UserProfileRecordTable::getList(array("filter" => array("=HISTORY_ID" => $row["ID"])));
        while($record = $records->fetch())
        {
            $strChanges .= HtmlFilter::encode($record["FIELD"]).': <span style="color:red">'.
                HtmlFilter::encode(var_export($record["DATA"]["before"], true)).'</span> => <span style="color:green">'.
                HtmlFilter::encode(var_export($record["DATA"]["after"], true)).'</span><br>';
        }
    }

    // ссылка на изменённый профиль
    $userLink = '<a href="/company/personal/user/' . $row['USER_ID'] . '/">' . '[' . $row['USER_ID'] . ']</a> ' . $arUsers[$row['USER_ID']]['NAME']
        . ' ' . $arUsers[$row['USER_ID']]['LAST_NAME'];

    // ссылка на профиль того, кто изменил
    if ($row['UPDATED_BY_ID']) {
        $updatedByLink = '<a href="/company/personal/user/' . $row['UPDATED_BY_ID'] . '/">' . '[' . $row['UPDATED_BY_ID'] . ']</a> ' . $arUsers[$row['UPDATED_BY_ID']]['NAME']
            . ' ' . $arUsers[$row['UPDATED_BY_ID']]['LAST_NAME'];
    } else {
        $updatedByLink = '';
    }

    $arRows[] = [
        'data' => [
            "DATE_INSERT" => $row['DATE_INSERT']->toString(),
            "USER_ID" => $userLink,
            "EVENT_TYPE" => $eventTypes[$row['EVENT_TYPE']],
            "UPDATED_BY_ID" => $updatedByLink,
            "CHANGES" => $strChanges,
        ],
    ];
}

$arResult['GRID_ID'] = $gridId;
$arResult['NAV_OBJECT'] = $nav;
$arResult['UI_FILTER'] = $ui_filter;
$arResult['COLUMNS'] = $columns;
$arResult['ROWS'] = $arRows;
$arResult['TOTAL_ROWS_COUNT'] = $allSelectedCount;

$this->IncludeComponentTemplate();