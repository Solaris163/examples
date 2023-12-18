<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;


class AxbitEdsRegisterAjax extends \Bitrix\Main\Engine\Controller
{
    private const EDS_REGISTER_IBLOCK_TYPE = 'custom';
    private const EDS_REGISTER_IBLOCK_CODE = 'eds_register';

    /**
     * метод добавляет/обновляет запись в реестр ЭЦП
     * @param string $id
     * @param $userId
     * @param $registrationDate
     * @param $expiryDate
     * @return array
     */
    public static function editSignatureAction($id, $userId, $registrationDate, $expiryDate)
    {
        $userId = preg_replace("/[^0-9]/", '', $userId);
        $error = '';

        Loader::includeModule('iblock');

        GLOBAL $USER;

        // получить id инфоблока реестра ЭЦП
        $iblockId = \Axbit\Tools\Iblocks::getOneIblockId(self::EDS_REGISTER_IBLOCK_TYPE, self::EDS_REGISTER_IBLOCK_CODE);
        if (!$iblockId) {
            return ['error' => 'Не найден id инфоблока'];
        }

        // проверка прав доступа
        // получение прав из инфоблока с правами доступа
        $usersCanEditEdsRegister = Axbit\Tools\AccessRights::getOneItem('eds_register', 'can_edit_eds_register');
        if (!in_array($USER->GetID(), $usersCanEditEdsRegister) && !$USER->IsAdmin()) {
            return ['error' => 'Нет доступа'];
        }

        // валидация
        if (!$userId) {
            $error .= 'Не заполнено поле Сотрудник<br>';
        }
        if (!$registrationDate) {
            $error .= 'Не заполнено поле Дата оформления<br>';
        } elseif (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $registrationDate)) {
            $error .= 'Дата оформления должна иметь формат ДД.ММ.ГГГГ<br>';
        }
        if (!$expiryDate) {
            $error .= 'Не заполнено поле Дата окончания<br>';
        } elseif (!preg_match('/^\d\d.\d\d.\d\d\d\d$/', $expiryDate)) {
            $error .= 'Дата окончания должна иметь формат ДД.ММ.ГГГГ<br>';
        }
        if ($registrationDate && $expiryDate && MakeTimeStamp($registrationDate) >= MakeTimeStamp($expiryDate)) {
            $error .= 'Дата оформления должна быть раньше даты окончания<br>';
        }


        $arLoadProductArray = [
            "NAME" => 'Элемент',
            "IBLOCK_ID" => $iblockId,
            "PROPERTY_VALUES" => [
                'USER_ID' => preg_replace("/[^0-9]/", '', $userId),
                'REGISTRATION_DATE' => $registrationDate,
                'EXPIRY_DATE' => $expiryDate,
            ],
        ];

        $el = new CIBlockElement;

        if (!$error) {
            if ($newId = $el->Add($arLoadProductArray)) {
                // найти и деактивировать предыдущие записи для данного сотрудника
                $arFilter = ["IBLOCK_ID" => $iblockId, "!ID" => $newId, 'PROPERTY_USER_ID' => $userId, 'ACTIVE' => 'Y'];
                $arSelect = ['ID'];
                $dbRes = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
                while ($arRes = $dbRes->Fetch()) {
                    $el->Update($arRes['ID'], ['ACTIVE' => 'N']);
                }

                return ['success' => true];
            }else{
                return ['error' => $el->LAST_ERROR];
            }
        }

        return ['error' => $error];
    }

    public function configureActions()
    {
        return [];
    }
}