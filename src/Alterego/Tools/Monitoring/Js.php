<?php

namespace Alterego\Tools\Monitoring;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LogstashFormatter;

class Js
{
    /*
     * Для работы с параметрами
     *
     * @access private
     * @var OptionsInterface
     */
    private $options = null;

    /**
     * Параметры настроек
     *
     * @param OptionsInterface $options
     * @param DataInterface $data
     */
    public function __construct(OptionsInterface $options)
    {
        // Инициализируем параметры по умолчанию
        $this->options = $options;
    }

    /**
     * Получить JS код для вставки на сайт
     *
     * @return string
     */
    public function getJs(): string
    {
        $js = <<<JS
        var user_uniq = '{$this->options->getUserId()}';
        user_uniq = user_uniq.toString();
        
        var error_log_url = '{$this->options->getHandler()}';
        var user_id = '{$this->options->getUserId()}';
        
JS;

        return $js . file_get_contents(__DIR__ . '/monitoring.js');
    }

    /**
     * Обработчик запроса логирования
     *
     * @param Data $data Данные для логирования
     */
    public function handler(Data $data): void
    {
        // Отправляем нужные заголовки
        $this->initHeader();

        // Если запрос подходит по условиям
        if ($data->checkInputData()) {
            // записуем в лог
            $this->writeLog($data);
        }
    }

    /**
     * Отправляем необходимые заголовки
     */
    private function initHeader(): void
    {
        header("HTTP/1.0 204 No Content");
    }

    /**
     * Пишем в лог
     *
     * @param Data $data
     */
    private function writeLog(Data $data): void
    {
        $log = new Logger('kibana');
        $stream = new StreamHandler($_SERVER['DOCUMENT_ROOT'] . $this->options->getLogPath());
        $stream->setFormatter(new LogstashFormatter($this->options->getAppName()));
        $log->pushHandler($stream);
        $log->warning('frontend_error', $data->toArray());
    }
}
