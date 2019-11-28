<?php

require_once '_config.php';
require_once 'logger.php';


function sh5json_parse($json, $mode = 0)
{
    logger('Парсим JSON');
    $arr = json_decode($json, true);
    $array = array();
    if (!isset($arr['shTable'])) {
        return [];
    }
    if ($arr['shTable'][0]['head'] == "") {
        unset($arr['shTable'][0]);
    }
    foreach ($arr['shTable'] as $num_dataset => $dataset) {
        foreach ($dataset['values'] as $num_value_set => $value_set) {
            foreach ($value_set as $num_value => $value) {
                $array['head' . $dataset['head']][$num_value][$dataset[($mode == 0 ? 'fields' : 'original')][$num_value_set]] = $value;
            }
        }
    }
    return $array;
}

function get_report_RptPreCost()
{
    logger('Запрашиваем отчет "Предполагаемая стоимость"');
    $date = new DateTime(SH5_REPORT_OFFSET_DATE . ' days');

    $head = array(
        'head' => '108',
        'original' => ['1', '30', '6', '7', '209\\1', '11', '3'],
        'values' => [
            [$date->format('Y-m-d')],
            [SH5_REPORT_COST_PER_SETS === 0 ? 0 : 2],
            [SH5_REPORT_MUNITS_TYPE],
            [SH5_REPORT_COST_TYPE],
            [SH5_REPORT_GROUP_RID],
            [0],
            [1]
        ]
    );


    $filter_departs = array(
        'head' => '106#10',
        'original' => ['1'],
        'values' => [
            array_values(DEPART_CATEGORY_COMPARISION)
        ]
    );


    $params = array("UserName" => SH5_API_USER, "Password" => SH5_API_PASSWORD, "procName" => "RptPreCost", "input" => array(
        $head,
        $filter_departs
    )
    );
    return postCURL_SH5($params);
}

function proc_GoodsTree()
{
    logger('Запрашиваем дерево товаров');
    $head = array(
        'head' => '209',
        'original' => ['1'],
        'values' => [
            [SH5_REPORT_GROUP_RID],
        ]
    );

    $params = array("UserName" => SH5_API_USER, "Password" => SH5_API_PASSWORD, "procName" => "GoodsTree", "input" => array(
        $head,
    )
    );
    return postCURL_SH5($params);
}

function parse_GoodTree($json)
{
    logger('Парсим дерево товаров');
    $data_arr = sh5json_parse($json, 1);

    if (!isset($data_arr['head210'])) {
        return [];
    }
    foreach ($data_arr['head210'] as $key => $data) {
        $array[$data['1']] = $data['4'];

    }
    return $array;
}


function postCURL_SH5($payload)
{
    logger('Отправляем запрос к API SH5');
    $data_string = json_encode($payload);
    $ch = curl_init(SH5_API_URL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $result = curl_exec($ch);
    if ($result === false) {
        $error = '{"errMessage": "Ошибка запроса к SH5 Api: ' . curl_error($ch) . '"}';
        logger($error);
        curl_close($ch);
        exit;
    } else {
        curl_close($ch);
        logger('Ответ от SH5 пришел без ошибок');
      //  logger($result);
        return $result;
    }

}

function parse_report_RptPreCost($json)
{
    logger('Парсим отчет "Предполагаемая стоимость"');
    $data_arr = sh5json_parse($json, 1);

    if (!isset($data_arr['head306'])) {
        return [];
    }
    foreach ($data_arr['head306'] as $key => $data) {
        $array[$data['52']][] = array(
            'rid' => $data['210\\1'],
            'name' => $data['210\\3'],
            'cost' => SH5_COST_WITH_TAX === 1 ? round($data['43'],2) : round($data['40'], 2)

        );
    }
    return $array;


}

function guidXOR($guid1, $guid2)
{
    $guid1 = str_ireplace(str_split('-{}'), '', $guid1);
    $guid2 = str_ireplace(str_split('-{}'), '', $guid2);
    $hash = bin2hex(hex2bin($guid1) ^ hex2bin($guid2));
    $guid = substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 4) .
        '-' .
        substr($hash, 12, 4) .
        '-' .
        substr($hash, 16, 4) .
        '-' .
        substr($hash, 20, 12);

    return strtoupper('{' . $guid . '}');
}

