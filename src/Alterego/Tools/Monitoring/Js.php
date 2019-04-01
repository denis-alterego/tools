<?php

namespace Alterego\Tools\Monitoring;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LogstashFormatter;

class Js
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
     * Массив ключей для проверки входных данных
     * @access private
     * @var array
     */
    private $dataKeys = [
        'useragent',
        'url',
        'message',
        'user_uniq',
        'platform',
        'innerWidth',
        'innerHeight',
        'line',
        'referer',
        'user_id',
        'maxTouchPoints',
        'vendor',
    ];

    /**
     * Данные для логирования
     *
     * @access private
     * @var array
     */
    private $data = [];

    /**
     *
     * @param array $params Параметры настроек
     */
    public function __construct(array $params = [])
    {
        // Инициализируем параметры по умолчанию
        $this->initParams($params);
    }

    /**
     * Инициализируем параметры
     *
     * @param array $params Параметры настроек
     */
    private function initParams(array $params)
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
     * Получить JS код для вставки на сайт
     *
     * @return string
     */
    public function getJs(): string
    {
        $js = <<<JS
        var user_uniq = '{$this->userUniq}';
        user_uniq = user_uniq.toString();
        
        var error_log_url = '{$this->handler}';
        var user_id = {$this->userId};
        
JS;

        return $js . file_get_contents(__DIR__ . '/monitoring.js');
    }

    /**
     * Обработчик запроса логирования
     *
     * @param array $data Данные для логирования
     */
    public function handler(array $data = [])
    {
        // Отправляем нужные заголовки
        $this->initHeader();

        // Инициализируем данные по умолчанию
        $this->initData($data);

        // Если запрос подходит по условиям
        if ($this->checkInputData($data)) {
            // записуем в лог
            $this->writeLog();
        }
    }

    /**
     * Формируем массив данных для логирования
     *
     * @param array $data
     */
    private function initData(array $data)
    {
        $_data = [];
        foreach ($this->dataKeys as $key) {
            $_data[$key] = $data[$key] ? htmlspecialchars($data[$key]) : '';
        }

        $this->data = $_data;
    }

    /**
     * Отправляем необходимые заголовки
     */
    private function initHeader()
    {
        header("HTTP/1.0 204 No Content");
    }

    /**
     * Проверяем параметры, не бот ли
     *
     * @param array $data Входные данные
     * @return bool
     */
    private function checkInputData(array $data): bool
    {
        if (
            strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) == false // Не нашли домена в referer пропускаем такой запрос
            ||
            strpos($this->data['useragent'], "Googlebot") !== false // Отсеиваем Гуглобота
            ||
            strpos($this->data['useragent'], "Google-Adwords") !== false // Отсеиваем Гуглобота
            ||
            strpos($this->data['useragent'], "YandexBot") !== false // Отсеиваем Яндексбота
            ||
            empty($this->data['url']) // По каким-то причинам url ошибки неизвестен
            ||
            $this->data['url'] == 'undefined' // По каким-то причинам url ошибки неизвестен
            ||
            strpos($this->data['message'], "Uncaught InvalidPointerId") !== false // Ошибки pointer id не логируем
        ) {
            return false;
        }

        return true;
    }

    /**
     * Пишем в лог
     */
    private function writeLog()
    {
        $uniq = $this->data['user_uniq'];

        // Если ничего не пришло, то генерим пользователю новый уникальный идентификатор и пишем его в куки
        if (empty($uniq)) {
            // А давай попробуем посмотреть в сессии и в $_COOKIES
            $uniq = !empty($_COOKIE[$this->cookieKey]) ? $_COOKIE[$this->cookieKey] : !empty($_SESSION[$this->cookieKey]) ? $_SESSION[$this->cookieKey] : '';
            // Все еще ничего не нашли?
            if (empty($uniq)) {
                $uniq = md5($this->data['platform'] . $this->data['useragent'] . $this->data['innerHeight'] . time());
                setcookie($this->cookieKey, $uniq, time() + 3600 * 999, "/", $_SERVER['SERVER_NAME'], 1);
                $_SESSION[$this->cookieKey] = $uniq;
            }
        }

        $userIp = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $errorHash = md5($this->data['url'] . $this->data['line'] . $this->siteId);

        $data = [
            "UF_MESSAGE" => $this->data['message'],
            "UF_LINE" => $this->data['line'],
            "UF_URL" => $this->data['url'],
            "UF_REFERER" => $this->data['referer'],
            "UF_USER_ID" => $this->data['user_id'],
            "UF_TOUCH_POINTS" => $this->data['maxTouchPoints'],
            "UF_PLATFORM" => $this->data['platform'],
            "UF_USERAGENT" => $this->data['useragent'],
            "UF_VENDOR" => $this->data['vendor'],
            "UF_VIEWPORT" => $this->data['innerWidth'] . "х" . $this->data['innerHeight'],
            "UF_TIME" => date("Y-m-d H:i:s"),
            "UF_UNIQ" => $uniq,
            "UF_SITE" => $this->siteId,
            "UF_SECONDS" => time(),
            "UF_HASH" => $errorHash,
            "UF_IP" => $userIp,
        ];

        $log = new Logger('kibana');
        $stream = new StreamHandler($_SERVER['DOCUMENT_ROOT'] . $this->logPath);
        $stream->setFormatter(new LogstashFormatter($this->appName));
        $log->pushHandler($stream);
        $log->warning('frontend_error', $data);
    }
}
