<?php


namespace Alterego\Tools\Bitrix\Db;


class Db implements DbInterface
{
    public function Query(string $strSql, bool $bIgnoreErrors = false, string $error_position = "", array $arOptions = []): DbResultInterface
    {
        global $DB;
        $res = $DB->Query($strSql, $bIgnoreErrors, $error_position, $arOptions);

        return new DbResult($res);
    }
}