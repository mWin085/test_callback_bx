<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Web\Json;

/** @var array $arParams */
/** @var array $arResult */

$fieldNameMap = [
    'text' => static fn(array $answer): string => 'form_text_' . $answer['ID'],
    'email' => static fn(array $answer): string => 'form_email_' . $answer['ID'],
    'url' => static fn(array $answer): string => 'form_url_' . $answer['ID'],
    'password' => static fn(array $answer): string => 'form_password_' . $answer['ID'],
    'textarea' => static fn(array $answer): string => 'form_textarea_' . $answer['ID'],
    'radio' => static fn(array $answer, string $sid): string => 'form_radio_' . $sid,
    'checkbox' => static fn(array $answer, string $sid): string => 'form_checkbox_' . $sid . '[]',
    'dropdown' => static fn(array $answer, string $sid): string => 'form_dropdown_' . $sid,
    'multiselect' => static fn(array $answer, string $sid): string => 'form_multiselect_' . $sid . '[]',
    'hidden' => static fn(array $answer): string => 'form_hidden_' . $answer['ID'],
];

$fields = [];

foreach ($arResult['QUESTIONS'] as $sid => $question) {
    $answers = array_values($question['STRUCTURE'] ?? []);
    if (!$answers) {
        continue;
    }

    $firstAnswer = $answers[0];
    $type = strtolower((string) $firstAnswer['FIELD_TYPE']);
    if (!isset($fieldNameMap[$type])) {
        continue;
    }

    $name = $fieldNameMap[$type]($firstAnswer, (string) $sid);
    $label = trim(html_entity_decode(strip_tags((string) $question['CAPTION'])));
    $isPhone = $sid === 'PHONE';
    $isName = $sid === 'NAME';
    $inputType = $type === 'email' ? 'email' : ($isPhone ? 'tel' : 'text');
    $options = [];

    foreach ($answers as $answer) {
        $options[] = [
            'value' => (string) $answer['ID'],
            'label' => trim(strip_tags((string) ($answer['MESSAGE'] ?? ''))),
        ];
    }

    $fields[] = [
        'key' => (string) $sid,
        'sid' => (string) $sid,
        'name' => $name,
        'type' => $type,
        'label' => $label,
        'required' => ($question['REQUIRED'] ?? 'N') === 'Y',
        'inputType' => $inputType,
        'autocomplete' => $isPhone ? 'tel' : ($isName ? 'name' : ($type === 'email' ? 'email' : 'off')),
        'placeholder' => $isPhone ? '+7 (___) ___-__-__' : '',
        'validation' => $isPhone ? 'phone' : '',
        'options' => $options,
        'answerValue' => (string) $firstAnswer['ID'],
    ];
}

$arResult['CALLBACK_CONFIG'] = [
    'formId' => (int) $arResult['arForm']['ID'],
    'formSid' => (string) $arResult['arForm']['SID'],
    'title' => trim((string) $arResult['FORM_TITLE']) ?: 'Заказать обратный звонок',
    'description' => trim(strip_tags((string) $arResult['FORM_DESCRIPTION'])),
    'submitLabel' => trim((string) $arResult['arForm']['BUTTON']) ?: 'Отправить',
    'endpoint' => '/local/ajax/callback-form.php',
    'sessid' => bitrix_sessid(),
    'delay' => (int) ($arParams['AUTO_OPEN_DELAY_MS'] ?? 20000),
    'storageKey' => 'callback-form-auto-opened',
    'fields' => $fields,
];

$arResult['CALLBACK_CONFIG_JSON'] = Json::encode(
    $arResult['CALLBACK_CONFIG'],
    JSON_UNESCAPED_UNICODE,
);
