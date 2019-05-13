<?php


namespace Alterego\Tools\Bitrix\Db;

use CDBResult;

class DbResult implements DbResultInterface
{
    private $dbResult;

    public function __construct(CDBResult $dbResult)
    {
        $this->dbResult = $dbResult;
    }

    public function Fetch()
    {
        return $this->dbResult->Fetch();
    }
}