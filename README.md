Для генерации JavaScript обработчика ошибок
```
$options = [
    'cookieKey' => 'jsmonitor',
    'handler' => '/js_server.php',    
    'userId' => 1000,
];
$jsObj = new Js($options);
$jsObj->getJs();
```
Обработка на стороне сервера
```
$options = [
    'cookieKey' => 'jsmonitor',
    'siteId' => 's1',
    'appName' => 'Test',
    'logPath' => '/upload/logs/monolog/kibana/app.log',
];
$jsObj = new Js($options);
$jsObj->handler($_REQUEST);
```