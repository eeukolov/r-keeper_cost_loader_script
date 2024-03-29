# r-keeper_cost_loader_script
Простой скрипт на php для автоматической загрузки себестоимости блюд в тип цены из Store House 5 в R-Keeper 7.

## Идея скрипта
Скрипт через API Store House 5 запрашивает из системы отчет "Предполагаемая стоимость" по товарной группе Меню ресторана. 
Данные в отчете конвертируются в запрос обновления цен в меню через API R-Keeper. Скрипт можно добавить в планировщик заданий Windows,
для автоматического запуска по расписанию. 

## Зачем
В R-Keeper встроен такой функционал, но для загрузки требуется участие пользователя. Кроме того, если в меню есть блюда разных подразделений, 
то при каждом запуске нужно выбирать категорию блюд и склад. То есть, если складов 2, то запускать выгрузку приходится минимум два раза.
Возникают ошибки, пользователи злятся. UCS обещал реализовать автоматичесую загрузку в будущих версиях, но чтобы сейчас снять напряжение
можно использовать этот скрипт.

## Подготовка R-Keeper
### Категории блюд
На предприятии себестоимость разных блюд "хранится" на разных подразделениях (складах). Например, себестоимость блюд кухни может хранятся на 
подразделении "Кухня" в SH5, а позиции бара на подразделении "Бар". Для корректной загрузки себестоимости, в R-Keeper необходимо добавить
новую классификацию, например, с названием "Подразделение SH5", куда в качестве категорий добавить названия всех подразделений SH5, 
с которых будет загружаться себестоимость блюд. Для того чтобы не было ошибок, рекомендуется сделать эту классификацию обязательной к назначению.
Все блюда в меню нужно разспеределить по категориям созданной классификации. Если в меню есть блюда, или услуги, себестоимость которых не нужно
загружать, можно либо назначить таким позициям категорию "Прочее", либо вообще не назначать таким позициям категорию.
Для дальнейшней настройки скрипта понадобится идентификатор классификации, а также идентификаторы категорий блюд, которые участвуют в загрузке 
себестоимости. 

### HTTP API
Для обновления цен в R-Keeper используется HTTP API. Интерфейс должен быть настроен и доступен по ссылке. Также должен быть создан пользователь
от имени которого будут делаться запросы. Имя пользователя API и пароль лучше заводить либо на англ. языке, либо цифрами.
Подробнее о настройке HTTP-интерфейса можно прочитать на [docs.rkeeper.ru](https://docs.rkeeper.ru/rk7/latest/ru/api/xml-api-opisanie-i-primery/xml-interfejs-r_keeper-7)

### Тип цены для себестоимости
Скрипт может загружать себестоимость в любой тип цены, идентификатор которого указан в настройках. Можно либо создать новый тип цены, либо использовать 
предустановленный. Главное указать в настройках его идентификатор.

## Подготовка Store House 5
### Подразделения
В настройках потребуется установить связь между категориями блюд R-Keeper и подразделениями Store House. Для этого нужно посмотреть 
RID (идентификаторы) подразделений Store House. Самый простой способ их узнать - выполнить через SDBman процедуру Departs. Результатом выполнения
процедуры будет список подразделений и их RID. Эти данные понадобятся для дальнейшей настройки.

### Товарная группа Меню ресторана
В настройках скрипта потребуется указать RID (код) и GUID товарной группы Меню ресторана, в которую выгружаются блюда из R-Keeper. Эти данные
доступны через просмотр свойств этой товарной группы.
 
 ## Настройка скрипта
 Настройки скрипта хранятся в виде констант PHP в файле **_config.php**. Не следует редактировать файл через стандартное приложение Блокнот Windows.
 Лучше использовать для этого Notepad++, AkelPad или другой продвинутый редактор.

```php
const SH5_API_URL = 'http://127.0.0.1:9797/api/sh5exec'; // URL для подключения к API Store House. 
const SH5_API_USER = 'Admin'; // Пользователь Store House от имени которого строится отчет "Предполагаемая стоимость" 
const SH5_API_PASSWORD = '1111'; // Пароль пользователя 

const SH5_GROUP_GUID = '{9C4C2BA9-2E05-4E48-BEF4-7A59D0DD2C4D}'; // GUID товарной группы Меню ресторана
const SH5_REPORT_GROUP_RID = 2; // rid (код) товарной группы Меню ресторана
const SH5_REPORT_OFFSET_DATE = 0; // смещение даты отчета в днях, относительно текущей (+1, -1, 0 и т.д.) дата на которую будет построен отчет
const SH5_REPORT_COST_TYPE = 1; // 0 - Fifo, 1 - послед.прих, 2 - средневзв. - тип расчет себестоимости
const SH5_REPORT_MUNITS_TYPE = 0; // 0  - базовые, 1 - для заявок, 2 - для отчетов - тип единиц измерения блюд в Store House, для которых будет рассчитана себестоимость
const SH5_REPORT_COST_PER_SETS = 1; // 1 - себестоимость по комплектам - использовать или нет расчет себестоимости по комплектам (параметр отчета Предполагаемая стоимость)
const SH5_COST_WITH_TAX = 1; // 1 - себестоимость включая налоги - какую колонку брать из отчета Предполагаемая стоимость - без налогов или включая налоги

const RK7_API_URL = 'https://127.0.0.1:8060/rk7api/v0/xmlinterface.xml'; // URL для подключения к API R-Keeper
const RK7_API_USER = 'Admin'; // Пользователь R-Keeper от имени которого будет выполняься запрос к API 
const RK7_API_PASSWORD = '1'; // Пароль пользователя

const RK7_CLASSIFICATION_ID = 3840; // Идентификатор классификации блюд для сопоставления подразделений - см. раздел "Подготовка R-Keeper"
const RK7_COST_PRICE_TYPE_ID = 4;  // Идентификатор тип цены для себестоимости - см. раздел "Подготовка R-Keeper"

// Сопоставление идентификаторов категорий RK7 и RID подразделений SH5
// в R-Keeper создаются категории внутри классификации (параметр RK7_CLASSIFICATION_ID), каждая категория сопоставляется со своим складом в SH5
// так скрипт поймет с какого склада брать себестоимость для каждого блюда
// Представляет собой массив PHP где ключ - это ID категории блюд из R-Keeper, а значение - RID подразделения Store House
const DEPART_CATEGORY_COMPARISION = array(
                                            3842 => 12582914, 
                                            3841 => 8388609
                                          );

```

## Запуск скрипта
Нужно запустить файл **start_script.bat** и подождать. Скрипт может выполняться достаточно долгое время - на среднем по размеру меню, работает
примерно 1,5 минуты. Этот же файл можно запускать по расписанию через планировщик Windows.

После выполнения скрипта (если все прошло хорошо) в логах будет примерно следующее содержание:

```
2019-11-28 09:57:35 Запуск скрипта
2019-11-28 09:57:35 Запрашиваем отчет "Предполагаемая стоимость"
2019-11-28 09:57:35 Отправляем запрос к API SH5
2019-11-28 09:57:35 Ответ от SH5 пришел без ошибок
2019-11-28 09:57:35 Парсим отчет "Предполагаемая стоимость"
2019-11-28 09:57:35 Парсим JSON
2019-11-28 09:57:35 Запрашиваем дерево товаров
2019-11-28 09:57:35 Отправляем запрос к API SH5
2019-11-28 09:57:36 Ответ от SH5 пришел без ошибок
2019-11-28 09:57:36 Парсим дерево товаров
2019-11-28 09:57:36 Парсим JSON
2019-11-28 09:57:36 Получаем меню RK7
2019-11-28 09:57:36 Отправляем запрос к API RK7
2019-11-28 09:57:37 Формируем XML для записи цен в RK7
2019-11-28 09:57:37 Отправляем запрос к API RK7
2019-11-28 09:58:47 Ответ:
2019-11-28 09:58:47 <?xml version="1.0" encoding="utf-8"?>
<RK7QueryResult ServerVersion="7.6.2.114" XmlVersion="244" NetName="RK7SRV" Status="Ok" Processed="1">
  <CommandResult CMD="SetRefData" Status="Ok" ErrorText="" DateTime="2019-11-28T09:57:37" WorkTime="15"/>
</RK7QueryResult>
```
 
