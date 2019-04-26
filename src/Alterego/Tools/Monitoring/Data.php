<?php

namespace Alterego\Tools\Monitoring;

class Data implements DataInterface
{
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

    public static function createFromArray(array $data): Data
    {
        $obj = new self();
        $obj->initData($data);

        return $obj;
    }

    /**
     * Формируем массив данных для логирования
     *
     * @param array $data
     */
    private function initData(array $data): void
    {
        $_data = [];
        foreach ($this->dataKeys as $key) {
            $_data[$key] = $data[$key] ? htmlspecialchars($data[$key]) : '';
        }

        $this->data = $_data;
    }

    /**
     * Проверяем параметры, не бот ли
     *
     * @return bool
     */
    public function checkInputData(): bool
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

    public function setSiteId(string $siteId): void
    {
        $this->data['siteId'] = $siteId;
    }

    public function getSiteId(): string
    {
        return $this->data['siteId'] ?? '';
    }

    public function toArray(): array
    {
        $uniq = $this->data['user_uniq'];

        // Если ничего не пришло, то генерим пользователю новый уникальный идентификатор и пишем его в куки
        if (empty($uniq)) {
            // А давай попробуем посмотреть в сессии и в $_COOKIES
            $cookieKey = $this->options->getCookieKey();
            $uniq = !empty($_COOKIE[$cookieKey]) ? $_COOKIE[$cookieKey] : !empty($_SESSION[$cookieKey]) ? $_SESSION[$cookieKey] : '';
            // Все еще ничего не нашли?
            if (empty($uniq)) {
                $uniq = md5($this->data['platform'] . $this->data['useragent'] . $this->data['innerHeight'] . time());
                setcookie($cookieKey, $uniq, time() + 3600 * 999, "/", $_SERVER['SERVER_NAME'], 1);
                $_SESSION[$cookieKey] = $uniq;
            }
        }

        $siteId = $this->getSiteId();
        $userIp = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $errorHash = md5($this->data['url'] . $this->data['line'] . $siteId);

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
            "UF_SITE" => $siteId,
            "UF_SECONDS" => time(),
            "UF_HASH" => $errorHash,
            "UF_IP" => $userIp,
        ];

        return $data;
    }
}