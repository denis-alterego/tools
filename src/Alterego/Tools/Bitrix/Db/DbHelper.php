<?php


namespace Alterego\Tools\Bitrix\Db;


class HighLoadHelper
{
    /**
     * Объект для работы с базой данных
     * @var
     */
    private $db;

    public function __construct(DbInterface $db = null)
    {
        if ($db)
            $this->setDb($db);
    }

    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }

    public function getDb()
    {
        return $this->db;
    }

    /**
     * Проверяем являются ли указанные поля индексами
     *
     * @param string $table Таблица в которой проверять поля
     * @param array $fields Список полей
     * @return array Возвращаем массив полей с информацией
     */
    public function checkTableFields(string $table, array $fields): array
    {
        $sql = "DESCRIBE {$table}";
        $res = $this->db->Query($sql);

        $returnData = [];
        // проходимся по всем полям и проверяем тип и индекс
        while ($item = $res->Fetch()) {
            if (in_array($item['Field'], $fields)) {
                $returnData[$item['Field']] = [
                    'type' => $item['Type'],
                    'index' => $item['Key'],
                    'recommends' => []
                ];
                // проверяем тип поля
                if (stripos($item['Type'], 'int') === false)
                    $returnData[$item['Field']]['recommends'][] = 'Если поле хранит идентификатор записи, необходимо изменить тип поля';
                // проверяем установлен ли индекс
                if (empty($item['Key']))
                    $returnData[$item['Field']]['recommends'][] = 'Необходимо создать индекс';
            }
        }

        return $returnData;
    }

    /**
     * проверяем все таблицы, которые используются в HighLoad Bitrix
     *
     * @return array Возвращаем массив таблиц с полями
     */
    public function checkHighLoadTables(): array
    {
        $sql = 'SELECT `ID`, `TABLE_NAME` FROM `b_hlblock_entity`';
        $res = $this->db->Query($sql);

        $tableFieldsInfo = [];
        while ($item = $res->Fetch()) {
            // Получаем поля используемые для связи с другими сущностями
            $fields = $this->getRelationFields($item['ID']);
            // получаем информацию по полям
            $tableFieldsInfo[$item['TABLE_NAME']] = $this->checkTableFields($item['TABLE_NAME'], $fields);
        }

        return $tableFieldsInfo;
    }

    /**
     * Получаем поля используемые для связи с другими сущностями
     *
     * @param int $entityId
     * @return array
     */
    private function getRelationFields(int $entityId): array
    {
        $sql = "SELECT `FIELD_NAME` FROM `b_user_field` WHERE `ENTITY_ID` = 'HLBLOCK_{$entityId}' AND `MULTIPLE` = 'N' AND `USER_TYPE_ID` IN ('iblock_section', 'employee', 'crm_status', 'crm', 'hlblock', 'iblock_element')";
        $res = $this->db->Query($sql);

        $fields = [];
        while ($item = $res->Fetch()) {
            $fields[] = $item['FIELD_NAME'];
        }
        return $fields;
    }
}