<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */


if (empty($arResult['CALLBACK_CONFIG']['fields'])) {
    return;
}

?>

<div
    id="<?=htmlspecialchars('callback-form-' . (int) $arResult['arForm']['ID'])?>"
    class="callback-form-root"
    data-callback-form="<?=htmlspecialchars($arResult['CALLBACK_CONFIG_JSON'])?>"
></div>
