<?php

namespace Alterego\Tools\Monitoring;

interface OptionsInterface
{
    public function createFromArray(array $params): void;

    public function getCookieKey(): string;

    public function getUserUniq(): string;

    public function getHandler(): string;

    public function getLogPath(): string;

    public function getUserId(): int;

    public function getSiteId(): string;

    public function getAppName(): string;
}