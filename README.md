Для генерации JavaScript обработчика ошибок
```
use Alterego\Tools\Monitoring\Js;
use Alterego\Tools\Monitoring\Options;

$options = [
    'cookieKey' => 'jsmonitor',
    'handler' => '/js_server.php',    
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
use Alterego\Tools\Monitoring\Js;
use Alterego\Tools\Monitoring\Data;
use Alterego\Tools\Monitoring\Options;

$options = [
    'cookieKey' => 'jsmonitor',
    'appName' => 'Test',
    'logPath' => $_SERVER['DOCUMENT_ROOT'] . '/test/upload/logs/monolog/kibana/app.log',
];
$jsObj = new Js(
    new Options($options)
);

$data = Data::createFromArray($_REQUEST);
// При необходимости
$data->setSiteId('s1');

$jsObj->handler($data);
```

Логирование

```
use Alterego\Tools\Logger\Logger;

$logger = new Logger($dirLog);// по умолчанию $_SERVER['DOCUMENT_ROOT'] . '/upload/logs/'

// пример вызова
$logger->addLog($pointname, $data = []);
// psr
$logger->debug($message, $context = []);
$logger->info($message, $context = []);
$logger->notice($message, $context = []);
$logger->warning($message, $context = []);
$logger->error($message, $context = []);
$logger->critical($message, $context = []);
$logger->alert($message, $context = []);
$logger->emergency($message, $context = []);
```

Методы для работы и инфоблоками и свойствами 1C-Bitrix

```
use Alterego\Tools\Bitrix\Common;

// получаем список значений пользовательского поля типа список
Common::getEnumValueListById(int $fieldId): array

// Получаем значение списка
Common::getEnumValueById(int $id, bool $getXml = false): ?string

// Получаем список вариантов для пользовательского поля по ID
Common::getUserFieldVariantsById(int $fieldId, array $listId = []): array

// Получаем список вариантов для пользовательского поля по объекту и символьному коду поля
Common::getUserFieldVariants(string $entityId, string $fieldCode, array $listId = [], bool $getXmlKey = false): array

// Получаем ID элемента списка по XML_ID
Common::getEnumIdByXmlId(string $entityId, string $fieldCode, string $xmlId, array $listId = []): int

// Получаем название свойства инфоблока по коду
Common::getPropertyLabel(int $iblockId, int $elementId, string $propertyCode): ?string

// Получаем ID элемента свойства типа список по XML_ID
Common::getPropertyEnumIdByXmlId(int $iblockId, string $fieldCode, string $xmlId): int

// Получаем XML_ID элемента свойства типа список по ID 
Common::getPropertyEnumXmlIdById(int $iblockId, string $fieldCode, int $id): string

// Получаем идентификатор инфоблока по символьному коду
Common::getIblockIdByCode(string $code, int $defaultId = 0, bool $checkPermission = false): int

// Получаем идентификатор элемента инфоблока по символьному коду
Common::getIblockElementIdByCode(string $code, int $defaultId = 0, bool $checkPermission = false): int
```

Методы для работы и инфоблоками и свойствами Bitrix24

```
use Alterego\Tools\Bitrix24\Common;

// Получаем название стадии сделки по идентификатору стадии
Common::getStageNameById($stageId): ?string

// Получаем первое значение мультиполя сущности crm
Common::getMultiField(string $entity, string $field, int $id): ?string

// Получаем список значений мультиполя сущности crm
Common::getMultiFields(string $entity, string $field, int $id): array

// Ищем сущность по номеру телефона (должен начинаться на +7|7|8)
Common::getContactsByPhone(string $phone, string $entity): array

// Получаем список статусов / стадий в зависимости от справочника
// STATUS / DEAL_STAGE / SOURCE / INVOICE_STATUS и др
Common::getCrmStatusByEntityId(string $entityId): array
```

Общее

```
use Alterego\Tools\Utility\Common;

// Вывод суммы прописью
Common::amount2str(1050.10, true);

// Очистка номера телефона от лишних символов
Common::clearPhone('9(25)000-00-00');// 79250000000
Common::clearPhone('89(25)000-00-00', '+');// +79250000000

// Начало слова с заглавной буквы для мультибайтовой кодировки
Common::mbucfirst('текст');// Текст
```

Тестирование компонентов Bitrix
```
use Alterego\Tools\Bitrix\Common;
use Alterego\Tools\Exception\PathNotFoundException;

try {
    $componentObj = Common::initComponent('namespace:componentName');
    $arResult = $componentObj->exec();
} catch (PathNotFoundException $e){
    
}
```