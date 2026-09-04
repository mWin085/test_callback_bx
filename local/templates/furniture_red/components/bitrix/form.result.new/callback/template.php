<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

?>

<div
    id="<?=htmlspecialcharsbx('callback-form-' . (int) $arResult['arForm']['ID'])?>"
    class="callback-form-root"
    data-callback-form="<?=htmlspecialcharsbx($arResult['CALLBACK_CONFIG_JSON'])?>"
></div>
