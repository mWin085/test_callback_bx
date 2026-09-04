<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Web\Json;

/** @var array $arParams */
/** @var array $arResult */

$fields = [];

$arResult['CALLBACK_CONFIG'] = [
    'formId' => (int) $arResult['arForm']['ID'],
    'formSid' => (string) $arResult['arForm']['SID'],
    'title' => trim((string) $arResult['FORM_TITLE']) ?: 'Заказать обратный звонок',
    'description' => trim(strip_tags((string) $arResult['FORM_DESCRIPTION'])),
    'submitLabel' => trim((string) $arResult['arForm']['BUTTON']) ?: 'Отправить',
    'sessid' => bitrix_sessid(),
    'delay' => (int) $arParams['AUTO_OPEN_DELAY_MS'] ?? 20000,
    'fields' => $fields,
];

$arResult['CALLBACK_CONFIG_JSON'] = Json::encode(
    $arResult['CALLBACK_CONFIG'],
    JSON_UNESCAPED_UNICODE,
);