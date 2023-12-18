<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss('/local/assets/css/ord.css'); // для кнопок добавить новую запись и скачать файл excel


?>
<div class="pagetitle_sd-wrap" style="margin: 15px;">
    <div class="pagetitle_sd-inner-container">
        <div class="pagetitle_sd pagetitle_sd-lnk">
            <a href="/servicedesk/" class="pagetitle_sd-lnk__lnk">
                <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                            d="M0.292893 7.29289C-0.097631 7.68342 -0.097631 8.31658 0.292893 8.70711L6.65685 15.0711C7.04738 15.4616 7.68054 15.4616 8.07107 15.0711C8.46159 14.6805 8.46159 14.0474 8.07107 13.6569L2.41421 8L8.07107 2.34315C8.46159 1.95262 8.46159 1.31946 8.07107 0.928932C7.68054 0.538408 7.04738 0.538408 6.65685 0.928932L0.292893 7.29289ZM2 7H1L1 9H2L2 7Z"
                            fill="#535C69" />
                </svg>
            </a>
            <span id="pagetitle" class="pagetitle_sd-item">
                Реестр ЭЦП
                <small>Сервис Деск</small>
            </span>
        </div>

        <div>
            <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                'FILTER_ID' => $arResult['GRID_ID'],
                'GRID_ID' => $arResult['GRID_ID'],
                'FILTER' => $arResult['UI_FILTER'],
                'ENABLE_LIVE_SEARCH' => true,
                'ENABLE_LABEL' => true,
                'DISABLE_SEARCH' => true,
            ]);?>
            <div title="Добавить запись" style="margin-top: 18px; margin-bottom: 18px" onclick="AxbitEdsRegister.showSignatureEditForm();" class="ord-add-button">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z"></path>
                </svg>
            </div>
            <a title="Скачать файл .xlsx" style="margin-top: 18px; margin-bottom: 18px" href="/servicedesk/eds-register/?type=xlsx" id="download_xlsx_file_btn"
               target="_blank" class="ord-add-button">
                <svg width="24" height="24" viewBox="0 0 84 84" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65.4987 8.09734C65.4226 4.09876 62.1706 0.886645 58.1648 0.875012L8.21875 0.875L8.09734 0.87633C4.09876 0.952384 0.886645 4.20444 0.875012 8.21022L0.875 72.8438L0.876876 73.0137C0.983828 78.6102 5.53784 83.1082 11.1475 83.125L71.375 83.125L71.5684 83.1234C77.9767 83.0195 83.125 77.7921 83.125 71.375V22.9062C83.125 18.8504 79.8371 15.5625 75.7812 15.5625H65.5V8.21875L65.4987 8.09734ZM65.5 21.4375V71.3493V71.375C65.5 74.6197 68.1303 77.25 71.375 77.25L71.4732 77.2491L71.569 77.2469C74.6949 77.146 77.199 74.6034 77.2492 71.4711L77.25 71.375V22.9062C77.25 22.0951 76.5924 21.4375 75.7812 21.4375H65.5ZM61.1969 77.25C60.1972 75.5217 59.625 73.5152 59.625 71.375V71.3493V18.5L59.625 18.4924V8.22728C59.6227 7.42775 58.9873 6.77732 58.2017 6.75083L58.1562 6.75L8.22728 6.74999C7.42775 6.75231 6.77732 7.3877 6.75083 8.17327L6.75 8.21875L6.74999 72.835C6.75719 75.246 8.69043 77.2032 11.0836 77.2492L11.1562 77.25H61.1969ZM50.8125 15.5625C52.4348 15.5625 53.75 16.8777 53.75 18.5C53.75 20.103 52.466 21.4061 50.8703 21.4369L50.8125 21.4375H39.0625C37.4402 21.4375 36.125 20.1223 36.125 18.5C36.125 16.897 37.409 15.5939 39.0047 15.5631L39.0625 15.5625H50.8125ZM53.75 30.25C53.75 28.6277 52.4348 27.3125 50.8125 27.3125H39.0625L39.0047 27.3131C37.409 27.3439 36.125 28.647 36.125 30.25C36.125 31.8723 37.4402 33.1875 39.0625 33.1875H50.8125L50.8703 33.1869C52.466 33.1561 53.75 31.853 53.75 30.25ZM50.8125 39.0625C52.4348 39.0625 53.75 40.3777 53.75 42C53.75 43.603 52.466 44.9061 50.8703 44.9369L50.8125 44.9375H15.5625C13.9402 44.9375 12.625 43.6223 12.625 42C12.625 40.397 13.909 39.0939 15.5047 39.0631L15.5625 39.0625H50.8125ZM53.75 53.75C53.75 52.1277 52.4348 50.8125 50.8125 50.8125H15.5625L15.5047 50.8131C13.909 50.8439 12.625 52.147 12.625 53.75C12.625 55.3723 13.9402 56.6875 15.5625 56.6875H50.8125L50.8703 56.6869C52.466 56.6561 53.75 55.353 53.75 53.75ZM50.8125 62.5625C52.4348 62.5625 53.75 63.8777 53.75 65.5C53.75 67.103 52.466 68.4061 50.8703 68.4369L50.8125 68.4375H15.5625C13.9402 68.4375 12.625 67.1223 12.625 65.5C12.625 63.897 13.909 62.5939 15.5047 62.5631L15.5625 62.5625H50.8125ZM15.5625 33.1875H27.3125C28.9348 33.1875 30.25 31.8723 30.25 30.25V18.5C30.25 16.8777 28.9348 15.5625 27.3125 15.5625H15.5625C13.9402 15.5625 12.625 16.8777 12.625 18.5V30.25C12.625 31.8723 13.9402 33.1875 15.5625 33.1875Z" fill="#222428"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<div style="padding: 0 15px">
    <?$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['COLUMNS'],
        'ROWS' => $arResult['ROWS'],
        'SHOW_ROW_CHECKBOXES' => false,
        'NAV_OBJECT' => $arResult['NAV_OBJECT'],
        "TOTAL_ROWS_COUNT" => $arResult['TOTAL_ROWS_COUNT'],
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' =>  [
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU'     => false, // убрал колонку слева
        'SHOW_GRID_SETTINGS_MENU'   => false, // убрал настройки и колонку слева
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => false, // количество строк, отмеченных галочкой
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => true,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N' // Y - для того чтобы изменять адрес url при запросе
    ]);?>
</div>

