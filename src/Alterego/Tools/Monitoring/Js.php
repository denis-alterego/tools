<?php

namespace Alterego\Tools\Monitoring;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LogstashFormatter;

class Js
{
    /*
     * @var string идентификато COOKIE
     */
    private $cookieKey = '';
    /**
     * @var string Уникальный идентификатор пользователя
     */
    private $userUniq = '';
    /**
     * @var string Путь к обработчику запроса
     */
    private $handler = '';
    /**
     * @var string Путь для записи логов
     */
    private $logPath = '';
    /**
     * @var int Идентификатор текущего пользователя
     */
    private $userId = 0;

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
        $this->userUniq = $_COOKIE[$this->cookieKey] ? htmlspecialchars($_COOKIE[$this->cookieKey]) : '';
        $this->handler = $params['handler'] ?? '/js_server.php';
        $this->logPath = $params['logPath'] ?? '/upload/logs/monolog/kibana/app.log';
        $this->userId = $params['userId'] ?? 0;
    }

    /**
     * Получить JS код для вставки на сайт
     *
     * @return string
     */
    public function getJs()
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
     */
    public function handler()
    {
        // Отправляем нужные заголовки
        $this->initHeader();

        // Если запрос подходит по условиям
        if ($this->check()) {
            // записуем в лог
            $this->writeLog();
        }
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
     * @return bool
     */
    private function check()
    {
        if (
            strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) == false // Не нашли домена в referer пропускаем такой запрос
            ||
            strpos($_REQUEST['useragent'], "Googlebot") !== false // Отсеиваем Гуглобота
            ||
            strpos($_REQUEST['useragent'], "Google-Adwords") !== false // Отсеиваем Гуглобота
            ||
            strpos($_REQUEST['useragent'], "YandexBot") !== false // Отсеиваем Яндексбота
            ||
            empty($_REQUEST['url']) // По каким-то причинам url ошибки неизвестен
            ||
            $_REQUEST['url'] == 'undefined' // По каким-то причинам url ошибки неизвестен
            ||
            strpos($_REQUEST['message'], "Uncaught InvalidPointerId") !== false // Ошибки pointer id не логируем
        ) {
            return fasle;
        }

        return true;
    }

    /**
     * Пишем в лог
     */
    private function writeLog()
    {
        $uniq = $_REQUEST['user_uniq'];
        // Если ничего не пришло, то генерим пользователю новый уникальный идентификатор и пишем его в куки
        if (empty($uniq)) {
            // А давай попробуем посмотреть в сессии и в $_COOKIES
            $uniq = !empty($_COOKIE[$this->cookieKey]) ? $_COOKIE[$this->cookieKey] : $_SESSION[$this->cookieKey];
            // Все еще ничего не нашли?
            if (empty($uniq)) {
                $uniq = md5($_REQUEST['platform'] . $_REQUEST['useragent'] . $_REQUEST['innerHeight'] . time());
                setcookie($this->cookieKey, $uniq, time() + 3600 * 999, "/", $_SERVER['SERVER_NAME'], 1);
                $_SESSION[$this->cookieKey] = $uniq;
            }
        }

        $userIp = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $errorHash = md5($_REQUEST['url'] . $_REQUEST['line'] . SITE_ID);

        $data = [
            "UF_MESSAGE" => htmlspecialchars($_REQUEST['message']),
            "UF_LINE" => htmlspecialchars($_REQUEST['line']),
            "UF_URL" => htmlspecialchars($_REQUEST['url']),
            "UF_REFERER" => htmlspecialchars($_REQUEST['referer']),
            "UF_USER_ID" => htmlspecialchars($_REQUEST['user_id']),
            "UF_TOUCH_POINTS" => htmlspecialchars($_REQUEST['maxTouchPoints']),
            "UF_PLATFORM" => htmlspecialchars($_REQUEST['platform']),
            "UF_USERAGENT" => htmlspecialchars($_REQUEST['useragent']),
            "UF_VENDOR" => htmlspecialchars($_REQUEST['vendor']),
            "UF_VIEWPORT" => htmlspecialchars($_REQUEST['innerWidth']) . "х" . htmlspecialchars($_REQUEST['innerHeight']),
            "UF_TIME" => date("Y-m-d H:i:s"),
            "UF_UNIQ" => htmlspecialchars($uniq),
            "UF_SITE" => htmlspecialchars(SITE_ID),
            "UF_SECONDS" => htmlspecialchars(time()),
            "UF_HASH" => htmlspecialchars($errorHash),
            "UF_IP" => htmlspecialchars($userIp),
        ];

        $log = new Logger('kibana');
        $stream = new StreamHandler($_SERVER['DOCUMENT_ROOT'] . $this->logPath);
        $stream->setFormatter(new LogstashFormatter);
        $log->pushHandler($stream);
        $log->warning('frontend_error', $data);
    }
}
