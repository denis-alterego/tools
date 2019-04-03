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
    'siteId' => 's1',
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