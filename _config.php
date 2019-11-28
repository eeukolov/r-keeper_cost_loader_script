<?php

const SH5_API_URL = 'http://127.0.0.1:9798/api/sh5exec';
const SH5_API_USER = 'Admin';
const SH5_API_PASSWORD = '3651340';

const SH5_GROUP_GUID = '{9C4C2BA9-2E05-4E48-BEF4-7A59D0DD2C4D}'; // GUID товарной группы Меню ресторана
const SH5_REPORT_GROUP_RID = 2; // rid (код) товарной группы Меню ресторана
const SH5_REPORT_OFFSET_DATE = 0; // смещение даты отчета в днях, относительно текущей (+1, -1, 0 и т.д.)
const SH5_REPORT_COST_TYPE = 1; // 0 - Fifo, 1 - послед.прих, 2 - средневзв.
const SH5_REPORT_MUNITS_TYPE = 0; // 0  - базовые, 1 - для заявок, 2 - для отчетов
const SH5_REPORT_COST_PER_SETS = 1; // 1 - себестоимость по комплектам
const SH5_COST_WITH_TAX = 1; // 1 - себестоимость включая налоги

const RK7_API_URL = 'https://127.0.0.1:8060/rk7api/v0/xmlinterface.xml';
const RK7_API_USER = '9006';
const RK7_API_PASSWORD = '1';

const RK7_CLASSIFICATION_ID = 3840;
const RK7_COST_PRICE_TYPE_ID = 4;

// сопоставление идентификатров категорий RK7 и RID подразделений SH5
// в R-Keeper создаются категории внутри классификации (RK7_CLASSIFICATION_ID) каждая категория сопоставляется со своим складом в SH5
// так скрипт поймет с какого склада брать себестоимость для каждого блюда
const DEPART_CATEGORY_COMPARISION = array(3842 => 12582914, 3841 => 8388609);


