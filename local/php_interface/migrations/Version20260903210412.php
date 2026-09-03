<?php

namespace Sprint\Migration;


class Version20260903210412 extends Version
{
    protected $author = "admin";

    protected $description = "Веб-форма Обратный звонок";

    protected $moduleVersion = "5.13.0";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $formId = $helper->Form()->saveForm(array (
  'NAME' => 'Обратный звонок',
  'SID' => 'CALLBACK_FORM',
  'MAIL_EVENT_TYPE' => 'FORM_FILLING_CALLBACK_FORM',
  'FILTER_RESULT_TEMPLATE' => '',
  'TABLE_RESULT_TEMPLATE' => '',
  'arSITE' => 
  array (
    0 => 's1',
  ),
  'arMENU' => 
  array (
    'ru' => 'Обратный звонок',
    'en' => '',
  ),
  'arGROUP' => 
  array (
  ),
  'arMAIL_TEMPLATE' => 
  array (
  ),
));
        $helper->Form()->saveStatuses($formId, array (
  0 => 
  array (
    'CSS' => 'statusgreen',
    'TITLE' => 'DEFAULT',
  ),
));
        $helper->Form()->saveFields($formId, array (
  0 => 
  array (
    'TITLE' => 'Имя',
    'TITLE_TYPE' => 'text',
    'SID' => 'NAME',
    'REQUIRED' => 'Y',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => 'Имя',
    'RESULTS_TABLE_TITLE' => 'Имя',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  1 => 
  array (
    'TITLE' => 'Телефон',
    'TITLE_TYPE' => 'text',
    'SID' => 'PHONE',
    'C_SORT' => '200',
    'REQUIRED' => 'Y',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => 'Телефон',
    'RESULTS_TABLE_TITLE' => 'Телефон',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  2 => 
  array (
    'TITLE' => 'Комментарий',
    'TITLE_TYPE' => 'text',
    'SID' => 'COMMENT',
    'C_SORT' => '400',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => 'Комментарий',
    'RESULTS_TABLE_TITLE' => 'Комментарий',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'textarea',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  3 => 
  array (
    'TITLE' => 'Согласие на обработку данных',
    'TITLE_TYPE' => 'text',
    'SID' => 'CONSENT',
    'C_SORT' => '500',
    'REQUIRED' => 'Y',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => 'Согласие на обработку данных',
    'RESULTS_TABLE_TITLE' => 'Согласие на обработку данных',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => 'Да',
        'FIELD_TYPE' => 'checkbox',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
));
    }
}

