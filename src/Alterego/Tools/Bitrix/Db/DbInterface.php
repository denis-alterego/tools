<?php


namespace Alterego\Tools\Bitrix\Db;

use CDBResult;

interface DbInterface
{
    public function Query(string $strSql, bool $bIgnoreErrors = false, string $error_position = "", array $arOptions = []): DbResultInterface;
}