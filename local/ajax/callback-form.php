<?php

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('DisableEventsCheck', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

header('Content-Type: application/json; charset=UTF-8');

$respond = static function (array $payload, int $status = 200): never {
    http_response_code($status);
    echo Json::encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    die();
};

/** @var \Bitrix\Main\HttpRequest $request */
$request = Context::getCurrent()->getRequest();

$formSid = trim((string) $request->getPostList()->get('form_sid'));
$form = null;

if (
    $request->isPost()
    && check_bitrix_sessid()
    && Loader::includeModule('form')
    && $formSid === 'CALLBACK_FORM'
) {
    $form = CForm::GetBySID($formSid)->Fetch();
}

if (!$form || (int) CForm::GetPermission((int) $form['ID']) < 10) {
    $respond(['success' => false, 'message' => 'Не удалось обработать запрос.'], 400);
}

$values = $_POST;
$errors = CForm::Check((int) $form['ID'], $values, false, 'Y', 'Y');

if ((is_array($errors) && $errors) || (is_string($errors) && $errors !== '')) {
    $fieldErrors = is_array($errors)
        ? array_map(static fn($error): string => trim(strip_tags((string) $error)), $errors)
        : ['_FORM' => trim(strip_tags($errors))];

    $respond([
        'success' => false,
        'message' => 'Проверьте правильность заполнения полей.',
        'errors' => $fieldErrors,
    ], 400);
}

$resultId = CFormResult::Add((int) $form['ID'], $values);
if (!$resultId) {
    $respond([
        'success' => false,
        'message' => $GLOBALS['strError'] ?: 'Не удалось сохранить заявку. Попробуйте ещё раз.',
    ], 500);
}

CFormCrm::onResultAdded((int) $form['ID'], $resultId);
CFormResult::SetEvent($resultId);
CFormResult::Mail($resultId);

$respond([
    'success' => true,
    'message' => 'Спасибо! Заявка отправлена, мы скоро вам перезвоним.',
    'resultId' => (int) $resultId,
]);
