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

    public function log($level, $message, array $context = array())
    {
        $this->addLog("Log-{$level}", $message, $context);
    }

    private static function initDir($dirName)
    {
        if (empty($dirName) === true || \is_string($dirName) === false) {
            self::$errorStack[] = 'Invalid DirName: ' . $dirName;
            return false;
        }
        $dirLogger = $dirName . self::getActualDirName() . DS;
        if (\file_exists($dirLogger) === false) {
            if (\mkdir($dirLogger, 0755, true) === false) {
                self::$errorStack[] = 'Not Create Folder: ' . $dirLogger;
                return false;
            }
        }
        return $dirLogger;
    }

    public function addLog(/*args*/)
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
        $dirLogger = self::initDir($this->dirLog . $key . DS);
        if ($dirLogger === false)
            return false;

        try {
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
            //
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

    private static function getActualDirName($format = 'Y-m-d')
    {
        return \date($format);
    }

    private static function getFileExtension()
    {
        return self::$fileExtension;
    }
}