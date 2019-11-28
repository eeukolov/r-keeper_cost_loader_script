<?php

require_once '_config.php';
require_once 'logger.php';


function postCURL_RK7($payload)
{
    logger('Отправляем запрос к API RK7');
    $ch = curl_init(RK7_API_URL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, RK7_API_USER . ':' . RK7_API_PASSWORD);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml')
    );

    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        $error = "Ошибка запроса к RK7 Api: " . $curl_error;
        logger($error);
        exit;
    }

    return new SimpleXMLElement($result);
}

function get_menu()
{
    logger('Получаем меню RK7');
    $cmd = '<?xml version="1.0" encoding="UTF-8"?>
<RK7Query>
    <RK7Command CMD="GetRefData" RefName="MenuItems" WithMacroProp ="1" WithChildItems="0" IgnoreDefaults="1"></RK7Command>
</RK7Query>
';

    $result = postCURL_RK7($cmd);

    $menu = [];
    if (isset($result->CommandResult->RK7Reference->Items)) {
        foreach ($result->CommandResult->RK7Reference->Items->Item as $item) {
            $cur = current($item);
            if ($cur['GUIDString'] !== '' and $cur['SaleObjectType'] === 'sotMenuItem' and isset($cur['CLASSIFICATORGROUPS-' . RK7_CLASSIFICATION_ID]) and $cur['CLASSIFICATORGROUPS-' . RK7_CLASSIFICATION_ID] != 0) {
                $menu[] = array(
                    'name' => $cur['Name'],
                    'guid' => $cur['GUIDString'],
                    'categ' => $cur['CLASSIFICATORGROUPS-' . RK7_CLASSIFICATION_ID]
                );
            }
        }
        return $menu;
    }
}

function formatXml($simpleXMLElement)
{
    $xmlDocument = new DOMDocument('1.0');
    $xmlDocument->preserveWhiteSpace = false;
    $xmlDocument->formatOutput = true;
    $xmlDocument->loadXML($simpleXMLElement->asXML());

    return $xmlDocument->saveXML();
}

