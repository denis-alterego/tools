<?php

namespace Alterego\Tools\Logger;

use Psr\Log\AbstractLogger;

define(__NAMESPACE__ . '\DS', DIRECTORY_SEPARATOR);

class Logger extends AbstractLogger implements LoggerInterface
{
    private static $fileExtension = '.log';
    public static $errorStack = [];
    private $dirLog = '';

    public function __construct(string $dirLog = null)
    {
        $this->dirLog = $dirLog ?: $_SERVER['DOCUMENT_ROOT'] . DS . 'upload' . DS . 'logs' . DS;
    }

    public function log($level, $message, array $context = [])
    {
        $this->addLog("Log-{$level}", $message, $context);
    }

    /**
     * @param string $dirName
     * @return string
     * @throws LoggerException
     */
    private static function initDir(string $dirName): string
    {
        if (empty($dirName) === true || \is_string($dirName) === false) {
            $mess = 'Invalid DirName: ' . $dirName;
            self::$errorStack[] = $mess;
            throw new LoggerException($mess);
        }
        $dirLogger = $dirName . self::getActualDirName() . DS;
        if (\file_exists($dirLogger) === false) {
            if (\mkdir($dirLogger, 0755, true) === false) {
                $mess = 'Not Create Folder: ' . $dirLogger;
                self::$errorStack[] = $mess;
                throw new LoggerException($mess);
            }
        }
        return $dirLogger;
    }

    public function addLog(/*args*/): bool
    {
        $args = \func_get_args();
        if (\count($args) < 2) {
            self::$errorStack[] = 'Must Be Two Or More Parameters';
            return false;
        }
        $key = \array_shift($args);
        foreach ($args as &$arg) {
            $arg = \print_r($arg, true);
        }
        try {
            $dirLogger = self::initDir($this->dirLog . $key . DS);

            $debug_backtrace = \debug_backtrace();
            \array_shift($debug_backtrace);
            if (is_array($debug_backtrace) === true && empty($debug_backtrace) === false) {
                if (is_array($debug_backtrace) === true) {
                    foreach ([1, 0] as $i) {
                        if (isset($debug_backtrace[$i])) {
                            \array_unshift($args, 'METHOD ' . ($i + 1) . ': ' . $debug_backtrace[$i]['class'] . $debug_backtrace[$i]['type'] . $debug_backtrace[$i]['function']);
                            \array_unshift($args, 'FILE   ' . ($i + 1) . ': ' . $debug_backtrace[$i]['file']);
                            \array_unshift($args, 'LINE   ' . ($i + 1) . ': ' . $debug_backtrace[$i]['line']);
                        }
                    }
                }
            }
            unset($debug_backtrace);
        } catch (\Exception $e) {
            return false;
        }

        \array_unshift($args, date('c'));
        $argsToSave = \implode("\r\n", $args) . "\r\n" . '--------------------------------------------------------------------' . "\r\n\n";
        $fileFullPath = $dirLogger . \date('H') . self::getFileExtension();
        if (\file_put_contents($fileFullPath, \print_r($argsToSave, true), FILE_APPEND) === false) {
            self::$errorStack[] = 'Not Write To File: ' . $fileFullPath;
            return false;
        }
        return true;
    }

    private static function getActualDirName($format = 'Y-m-d'): string
    {
        return \date($format);
    }

    private static function getFileExtension(): string
    {
        return self::$fileExtension;
    }
}