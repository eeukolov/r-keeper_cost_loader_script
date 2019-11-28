<?php
require_once '_config.php';
require_once 'sh5.php';
require_once 'rk7.php';
require_once 'logger.php';

if (!file_exists('log')) {
    mkdir('log', 0777, true);
}


logger('Запуск скрипта');
$sh5_report_json = get_report_RptPreCost();
$sh5_report_parsed = parse_report_RptPreCost($sh5_report_json);

$all_goods_json = proc_GoodsTree();
$all_goods = parse_GoodTree($all_goods_json);

$menu = get_menu();


if (empty($menu) or empty($sh5_report_parsed)) {
    logger('Получено пустое меню либо отчет по себестоимости из SH5');
    exit;
}

foreach ($sh5_report_parsed as $depart => $goods) {
    foreach ($goods as $key => $good) {
        $sh5_report_parsed[$depart][$key]['guid_sh'] = $all_goods[$good['rid']];
        $sh5_report_parsed[$depart][$key]['guid_rk'] = guidXOR($all_goods[$good['rid']], SH5_GROUP_GUID);
    }
    $sh5_report_parsed[$depart] = array_column($sh5_report_parsed[$depart], 'cost', 'guid_rk');
}


logger('Формируем XML для записи цен в RK7');

$XML = new SimpleXMLElement("<RK7Query></RK7Query>");
$command = $XML->addChild('RK7Command');
$command->addAttribute('CMD', 'SetRefData');
$command->addAttribute('RefName', 'MenuItems');
$items = $command->addChild('Items');
foreach ($menu as $dish) {
    if (!isset($sh5_report_parsed[DEPART_CATEGORY_COMPARISION[$dish['categ']]][$dish['guid']])) {
        continue;
    }
    $item = $items->addChild('Item');
    $item->addAttribute('GUIDString', $dish['guid']);
    $item->addAttribute('PRICETYPES-' . RK7_COST_PRICE_TYPE_ID, ($sh5_report_parsed[DEPART_CATEGORY_COMPARISION[$dish['categ']]][$dish['guid']]) * 100);
}
Header('Content-type: text/xml');

$resultXML = postCURL_RK7($XML->asXML());
logger('Ответ:');
logger(formatXml($resultXML));

Header('Content-type: text/xml');
echo(formatXml($resultXML));


