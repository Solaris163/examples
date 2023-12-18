<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


class AxbitUsersProfileHistory extends CBitrixComponent
{
    /**
     * метод возвращает массив сотрудников
     * @param $arUsersIds - массив id сотрудников
     * @return array
     */
    public static function getUsers($arUsersIds) {
        $arUsers = [];
        if (is_array($arUsersIds) && $arUsersIds) {
            $strUsersIds = implode('|', $arUsersIds);
            $arFilter = array('ID' => $strUsersIds);
            $arParams = array(
                'SELECT' => array(),
                'FIELDS' => array('ID', 'NAME', 'LAST_NAME')
            );
            $rsUsers = \CUser::GetList(($by="id"), ($order="asc"), $arFilter, $arParams);
            while ($arUser = $rsUsers->GetNext()) {
                $arUsers[$arUser['ID']] = $arUser;
            }
        }
        return $arUsers;
    }

    /**
     * метод возвращает массив сотрудников в имени или фамилии которых встречается искомая фраза
     * @param $str - искомая фраза
     * @return array
     */
    public static function getUsersBySearchPhrase($str) {
        $arUsers = [];
        if ($str) {
            $arFilter = array(
                array(
                    'LOGIC' => 'OR',
                    ['NAME' => '%' . $str . '%'],
                    ['LAST_NAME' => '%' . $str . '%'],
                )
            );
            $rsUsers = Bitrix\Main\UserTable::getList([
                "select"=>['ID', 'NAME', 'LAST_NAME'],
                "filter"=>$arFilter,
            ]);
            while ($arUser = $rsUsers->fetch()) {
                $arUsers[$arUser['ID']] = $arUser;
            }
        }
        return $arUsers;
    }
}