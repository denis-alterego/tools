<?php

namespace Alterego\Tools\Monitoring;

interface DataInterface
{
    public static function createFromArray(array $data): Data;

    public function checkInputData(): bool;

    public function toArray(): array;

    public function setSiteId(string $siteId);

    public function getSiteId(): string;
}