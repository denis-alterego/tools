<?php

namespace Alterego\Tools\Monitoring;

class Options implements OptionsInterface
{
    /*
     * идентификато COOKIE
     *
     * @access private
     * @var string
     */
    private $cookieKey = '';

    /**
     * Уникальный идентификатор пользователя
     *
     * @access private
     * @var string
     */
    private $userUniq = '';

    /**
     * Путь к обработчику запроса
     *
     * @access private
     * @var string
     */
    private $handler = '';

    /**
     * Путь для записи логов
     *
     * @access private
     * @var string
     */
    private $logPath = '';

    /**
     * Идентификатор текущего пользователя
     *
     * @access private
     * @var int
     */
    private $userId = 0;

    /**
     * Идентификатор сайта
     *
     * @access private
     * @var string
     */
    private $siteId = '';

    /**
     * Имя проекта
     *
     * @access private
     * @var string
     */
    private $appName = '';

    /**
     * Параметры настроек
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        // Инициализируем параметры
        $this->createFromArray($params);
    }

    /**
     * Инициализируем параметры
     *
     * @param array $params Параметры настроек
     */
    public function createFromArray(array $params): void
    {
        $this->cookieKey = $params['cookieKey'] ?? 'jsmonitor';
        $this->userUniq = isset($_COOKIE[$this->cookieKey]) ? htmlspecialchars($_COOKIE[$this->cookieKey]) : '';
        $this->handler = $params['handler'] ?? '/js_server.php';
        $this->logPath = $params['logPath'] ?? '/upload/logs/monolog/kibana/app.log';
        $this->userId = $params['userId'] ?? 0;
        $this->siteId = $params['siteId'] ?? '';
        $this->appName = $params['appName'] ?? '';
    }

    /**
     * @return string
     */
    public function  getCookieKey(): string
    {
        return $this->cookieKey;
    }

    /**
     * @return string
     */
    public function  getUserUniq(): string
    {
        return $this->userUniq;
    }

    /**
     * @return string
     */
    public function  getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function  getLogPath(): string
    {
        return $this->logPath;
    }

    /**
     * @return int
     */
    public function  getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function  getSiteId(): string
    {
        return $this->siteId;
    }

    /**
     * @return string
     */
    public function  getAppName(): string
    {
        return $this->appName;
    }
}