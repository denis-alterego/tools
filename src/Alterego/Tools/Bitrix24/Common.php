<?php

namespace Alterego\Tools\Bitrix24;

use CCrmStatus;
use CCrmFieldMulti;
use Alterego\Tools\Utility;

class Common
{
    /**
     * Получаем название стадии сделки по идентификатору стадии
     *
     * @param string $stageId
     * @return string|null
     */
    public static function getStageNameById($stageId): ?string
    {
        $resObj = CCrmStatus::GetList($arSort = [], $arFilter = ['ENTITY_ID' => 'DEAL_STAGE', 'STATUS_ID' => $stageId]);
        return ($item = $resObj->Fetch()) ? $item['NAME'] : null;
    }

    /**
     * Получаем первое значение мультиполя сущности crm
     *
     * @param string $entity Символьный код сущности
     * @param string $field Символьный код поля
     * @param int $id Идентификатор элемента сущности
     * @return string|null
     */
    public static function getMultiField(string $entity, string $field, int $id): ?string
    {
        $res = CCrmFieldMulti::GetList(
            ['ID' => 'ASC'],
            [
                'ENTITY_ID' => $entity,
                'TYPE_ID' => $field,
                "CHECK_PERMISSIONS" => "N",
                'ELEMENT_ID' => $id
            ]
        );

        if ($arItem = $res->getNext())
            return ($field === 'PHONE') ? $arItem['~VALUE'] : $arItem['~VALUE'];
        return null;
    }

    /**
     * Получаем список значений мультиполя сущности crm
     *
     * @param string $entity Символьный код сущности
     * @param string $field Символьный код поля
     * @param int $id Идентификатор элемента сущности
     * @return array
     */
    public static function getMultiFields(string $entity, string $field, int $id): array
    {
        $res = CCrmFieldMulti::GetList(
            ['ID' => 'ASC'],
            [
                'ENTITY_ID' => $entity,
                'TYPE_ID' => $field,
                "CHECK_PERMISSIONS" => "N",
                'ELEMENT_ID' => $id
            ]
        );
        $result = [];
        while ($arItem = $res->getNext()) {
            $result[] = ($field === 'PHONE') ? $arItem['~VALUE'] : $arItem['~VALUE'];
        }
        return $result;
    }

    /**
     * Ищем сущность по номеру телефона
     *
     * @param string $phone
     * @param string $entity CONTACT | LEAD
     * @return array
     */
    public static function getContactsByPhone(string $phone, string $entity): array
    {
        $phone_regex = '#^(\+?[\d]) ?\(?([\d]{3})\)? ?([\d]{3})[- ]?([\d]{2})[- ]?([\d]{2})$#';

        preg_match($phone_regex, Utility\Common::clearPhone($phone), $matches);
        unset($matches[0]);
        unset($matches[1]);

        $contact_filter = [
            'ENTITY_ID' => $entity,
            'TYPE_ID' => 'PHONE',
            '%VALUE' => '(7|"+"7|8)%' . implode('%', $matches),
            'CHECK_PERMISSIONS' => 'N'
        ];

        $dbCrmFieldMulti = CCrmFieldMulti::GetList(
            ['ID' => 'ASC'],
            $contact_filter
        );

        $contacts = [];
        while ($arItem = $dbCrmFieldMulti->Fetch()) {
            $contacts[] = $arItem['ELEMENT_ID'];
        }
        return $contacts;
    }

    /**
     * Получаем список статусов / стадий в зависимости от справочника
     *
     * @param string $entityId Символьный код справочника STATUS / DEAL_STAGE / SOURCE / INVOICE_STATUS и др
     * @return array
     */
    public static function getCrmStatusByEntityId(string $entityId): array
    {
        return CCrmStatus::GetStatusListEx($entityId);
    }
}