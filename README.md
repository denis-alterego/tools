Для генерации JavaScript обработчика ошибок
```
$options = [
    'cookieKey' => 'jsmonitor',
    'handler' => '/js_server.php',
    'logPath' => '/upload/logs/monolog/kibana/app.log',
    'userId' => 1000,
];
$jsObj = new Js(
    new Options($options)
);
```
Вывод JS в шаблоне
```
echo $jsObj->getJs();
```

Обработка на стороне сервера
```
$options = [
    'cookieKey' => 'jsmonitor',
    'appName' => 'Test',
    'logPath' => '/upload/logs/monolog/kibana/app.log',
];
$jsObj = new Js(
    new Options($options)
);

$data = Data::createFromArray($_REQUEST);
// При необходимости
$data->setSiteId('s1');

$jsObj->handler($data);
```

Вывод суммы прописью
```
Common::amount2str(1050.10, true);
```

Логирование
```
$logger = new Logger($dirLog);// по умолчанию $_SERVER['DOCUMENT_ROOT'] . '/upload/logs/'

// пример вызова
$logger->addLog($pointname, $data = []);
// psr
$logger->debug($message, $context = [])
$logger->info($message, $context = [])
$logger->notice($message, $context = [])
$logger->warning($message, $context = [])
$logger->error($message, $context = [])
$logger->critical($message, $context = [])
$logger->alert($message, $context = [])
$logger->emergency($message, $context = [])
```