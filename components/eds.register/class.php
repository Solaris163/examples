<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class AxbitEdsRegister extends CBitrixComponent
{
    /**
     * метод подготавливает массив данных для получения таблицы excel
     * @param $columns
     * @param $arRows
     * @return array
     */
    public static function prepareSpreadsheet($columns, $arRows) {
        $arSpreadsheet = [];
        $arIndexes = []; // массив индексов колонок таблицы

        // шапка таблицы
        $arNewRow = [];
        $columnNumber = 0;
        // перебрать шапку таблицы, чтобы заполнить первую строку и закрепить за каждым столбцом его индекс (1, 2 и т.д.)
        foreach ($columns as $column) {
            $columnNumber++;
            $arIndexes[$column['id']] = $columnNumber; // закрепить букву за данным столбцом
            $arNewRow[$columnNumber] = $column['name'];
        }
        $arSpreadsheet[] = $arNewRow;

        // перебрать строки и вписать каждую строку в таблицу excel, так чтобы данные каждого столбца вставлялись в закрепленную за ним букву
        foreach ($arRows as $row) {
            $arNewRow = [];
            foreach ($row['data'] as $columnId => $value) {
                // найти индекс, закрепленный за столбцом
                $columnNumber = $arIndexes[$columnId];
                $arNewRow[$columnNumber] = $value;
            }
            $arSpreadsheet[] = $arNewRow;
        }

        return $arSpreadsheet;
    }
}