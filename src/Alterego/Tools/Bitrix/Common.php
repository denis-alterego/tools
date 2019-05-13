<?php

namespace Alterego\Tools\Bitrix;

// пространство имен платформы Bitrix
use CUserFieldEnum;
use CIBlock;
use CIBlockElement;
use CIBlockPropertyEnum;
use CBitrixComponent;
//
use Alterego\Tools\Utility;
use Alterego\Tools\Exception\ {
    PathNotFoundException,
    ClassNotFoundException
};

class Common
{
    /**
     * получаем список значений пользовательского поля типа список
     *
     * @param int $fieldId Идентификатор поля
     * @return array
     */
    public static function getEnumValueListById(int $fieldId): array
    {
        $result = [];
        $rsRes = CUserFieldEnum::GetList([], ["USER_FIELD_ID" => $fieldId]);
        while ($arItem = $rsRes->GetNext())
            $result[] = $arItem;
        return $result;
    }

    /**
     * Получаем значение списка
     *
     * @param int $id Идентификатор значения
     * @param bool $getXml Если нужно получить XML поля
     * @return string|null
     */
    public static function getEnumValueById(int $id, bool $getXml = false): ?string
    {
        if (empty($id)) return null;

        $rsRes = CUserFieldEnum::GetList([], ["ID" => $id]);
        if ($arItem = $rsRes->GetNext())
            return $getXml ? $arItem["XML_ID"] : $arItem["VALUE"];
        return null;
    }

    /**
     * Получаем список вариантов для пользовательского поля по ID
     *
     * @param int $fieldId
     * @param array $listId
     * @return array
     */
    public static function getUserFieldVariantsById(int $fieldId, array $listId = []): array
    {
        $ret = [];
        $filter = ["USER_FIELD_ID" => $fieldId];
        if (!empty($listId) && is_array($listId))
            $filter['ID'] = $listId;

        $rs = CUserFieldEnum::GetList(['SORT' => 'ASC'], $filter);
        while ($var = $rs->GetNext()) {
            $ret[$var['ID']] = $var;
        }
        return $ret;
    }

    /**
     * Получаем список вариантов для пользовательского поля по объекту и символьному коду поля
     *
     * @param string $entityCode Символьный код сущности (объекта)
     * @param string $fieldCode Символьный код поля
     * @param array $listId Если нужно ограничить выборку по нескольким элементам списка
     * @param bool $getXmlKey Если нужно, чтобы ключи массива были XML_ID
     * @return array
     * @example getUserFieldVariants("HLBLOCK_1", "UF_REQUEST_TYPE");
     */
    public static function getUserFieldVariants(string $entityCode, string $fieldCode, array $listId = [], bool $getXmlKey = false): array
    {
        // за $GLOBALS["USER_FIELD_MANAGER"] отвечает Bitrix
        $arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($entityCode);
        $arItems = self::getUserFieldVariantsById($arUF[$fieldCode]['ID'], $listId);

        // если нужно, чтобы ключи массива были XML_ID
        if ($getXmlKey) {
            $_arItems = [];
            foreach ($arItems as $arItem) {
                $_arItems[$arItem['XML_ID']] = $arItem;
            }
            return $_arItems;
        }
        return $arItems;
    }

    /**
     * Получаем ID элемента списка по XML_ID
     *
     * @param string $entityId Символьный код сущности (объекта)
     * @param string $fieldCode Символьный код поля
     * @param string $xmlId Символьный код XML_ID списка
     * @param array $listId Если нужно ограничить выборку по нескольким элементам списка
     * @return int
     */
    public static function getEnumIdByXmlId(string $entityId, string $fieldCode, string $xmlId, array $listId = []): int
    {
        $items = self::getUserFieldVariants($entityId, $fieldCode, $listId);
        foreach ($items as $item)
            if ($item['XML_ID'] == $xmlId)
                return $item['ID'];
        return 0;
    }

    /**
     * Получаем название свойства инфоблока по коду
     *
     * @param int $iblockId Идентификатор инфоблока
     * @param int $elementId Идентификатор элемента
     * @param string $propertyCode Код свойства
     * @return string|null
     */
    public static function getPropertyLabel(int $iblockId, int $elementId, string $propertyCode): ?string
    {
        $propertyResult = CIBlockElement::GetProperty(
            $iblockId,
            $elementId,
            ['ID' => 'DESC'],
            ['CODE' => $propertyCode]
        );
        $property = $propertyResult->Fetch();
        return !empty($property['NAME']) ? $property['NAME'] : null;
    }

    /**
     * Получаем ID элемента свойства типа список по XML_ID
     *
     * @param int $iblockId Идентификатор инфоблока
     * @param string $fieldCode Символьный код свойства
     * @param string $xmlId Символьный код XML_ID списка
     * @return int
     */
    public static function getPropertyEnumIdByXmlId(int $iblockId, string $fieldCode, string $xmlId): int
    {
        $propertyEnums = CIBlockPropertyEnum::GetList(["DEF" => "DESC", "SORT" => "ASC"], ["IBLOCK_ID" => $iblockId, "CODE" => $fieldCode]);
        while ($item = $propertyEnums->Fetch())
            if ($item['XML_ID'] == $xmlId)
                return $item['ID'];
        return 0;
    }

    /**
     * Получаем XML_ID элемента свойства типа список по ID
     *
     * @param int $iblockId Идентификатор инфоблока
     * @param string $fieldCode Символьный код свойства
     * @param int $id Идентификатор элемента списка
     * @return string
     */
    public static function getPropertyEnumXmlIdById(int $iblockId, string $fieldCode, int $id): string
    {
        $propertyEnums = CIBlockPropertyEnum::GetList(["DEF" => "DESC", "SORT" => "ASC"], ["IBLOCK_ID" => $iblockId, "CODE" => $fieldCode]);
        while ($item = $propertyEnums->Fetch())
            if ($item['ID'] == $id)
                return $item['XML_ID'];
        return '';
    }

    /**
     * Получаем идентификатор инфоблока по символьному коду
     *
     * @param string $code Символьный код инфоблока
     * @param int $defaultId Идентификатор по умолчанию
     * @param bool $checkPermission Проверять права
     * @return int
     */
    public static function getIblockIdByCode(string $code, int $defaultId = 0, bool $checkPermission = false): int
    {
        $res = CIBlock::GetList($ar = [], ['=CODE' => $code, 'CHECK_PERMISSIONS' => ($checkPermission ? 'Y' : 'N')], false);
        if ($item = $res->Fetch())
            return $item['ID'];
        return $defaultId;
    }

    /**
     * Получаем идентификатор элемента инфоблока по символьному коду
     *
     * @param string $code Символьный код элемента инфоблока
     * @param int $defaultId Идентификатор по умолчанию
     * @param bool $checkPermission Проверять права
     * @return int
     */
    public static function getIblockElementIdByCode(string $code, int $defaultId = 0, bool $checkPermission = false): int
    {
        $res = CIBlockElement::GetList($ar = [], ['=CODE' => $code, 'CHECK_PERMISSIONS' => ($checkPermission ? 'Y' : 'N')], false);
        if ($item = $res->Fetch())
            return $item['ID'];
        return $defaultId;
    }

    /**
     * Получаем данные компонента
     *
     * @param string $component
     * @return ComponentInterface
     * @throws PathNotFoundException
     */
    public static function initComponent(string $component): ComponentInterface
    {
        // получаем пространство имен и имя компонента
        list($nameSpace, $component) = explode(':', $component);

        // получаем путь к компоненту
        $path = Utility\Common::getFirstExistsPath(
            $_SERVER['DOCUMENT_ROOT'] . "/local/components/{$nameSpace}/{$component}/",
            $_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/{$nameSpace}/{$component}/"
        );

        $classPath = $path . '/class.php';
        $componentPath = $path . '/component.php';

        try {
            // подключаем класс компонента и определяем его имя
            $componentClass = Utility\Common::includeAndGetComponentClass($classPath);
        } catch (\ReflectionException | ClassNotFoundException $e) {
            $componentClass = '';
        }
        // если компонент не имеет класс, определяем класс по умолчанию
        if (!class_exists($componentClass)) {
            $componentClass = 'Alterego\Tools\Bitrix\DefClass';
        }

        return self::initComponentObject($componentPath, $componentClass);
    }

    /**
     * Создаем объект компонента
     *
     * @param string $componentPath
     * @param string $componentClass
     * @return ComponentInterface
     */
    private static function initComponentObject(string $componentPath, string $componentClass): ComponentInterface
    {
        // т.к. не можем наследоваться от переменной, задаем алиас для класса
        class_alias($componentClass, 'ComponentClass');

        return new class($componentPath) extends \ComponentClass implements ComponentInterface
        {
            private $componentPath;

            public function __construct(string $componentPath)
            {
                $this->componentPath = $componentPath;
            }

            public function exec(): array
            {
                global $DB, $USER, $APPLICATION;

                $arResult = [];

                // если есть файл компонента, подключаем его
                if (file_exists($this->componentPath))
                    include_once $this->componentPath;

                // на случай если в классе компонента определены значения для свойства
                return array_merge($arResult, $this->arResult);
            }
        };
    }
}