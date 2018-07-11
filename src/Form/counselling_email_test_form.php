<?php

use Drupal\counselling\EmailManager;

/**
 * Implement hook_form
 */
function counselling_email_test_form($form, &$form_state) {
  
  $form['test_mail'] = [
    '#type' => 'fieldset',
    '#title' => t('Test email'),
    '#collapsible' => FALSE,
    '#tree' => TRUE,
  ];
  
  $form['test_mail']['addressee_type'] = [
    '#type' => 'radios',
    '#title' => 'Addressee type',
    '#default_value' => '',
    '#options' => [
      'single' => 'Single',
      'roles' => 'Roles',
    ],
  ];
  
  $form['test_mail']['to'] = array(
    '#type' => 'textfield',
    '#title' => t('To'),
    '#size' => 60,
    '#default_value' => '',
    '#states' => [
      'visible' => [
        'input[name="test_mail[addressee_type]"]' => ['value' => 'single'],
      ],
    ],
  );
  
  $roles = user_roles(TRUE);
  $options = [];
  foreach ($roles as $role) {
    $options[$role] = $role;
  }
  $form['test_mail']['roles'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Roles'),
    '#options' => $options,
    '#default_value' => [],
    '#states' => [
      'visible' => [
        'input[name="test_mail[addressee_type]"]' => ['value' => 'roles'],
      ],
    ],
  );
  
  $form['test_mail']['subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Subject'),
    '#size' => 60,
    '#default_value' => '',
  );
  
  $form['test_mail']['message'] = array(
    '#type' => 'text_format',
    '#title' => t('Message'),
    '#rows' => 9,
    '#default_value' => '',
  );
  
  $form['test_mail']['from'] = array(
    '#type' => 'textfield',
    '#title' => t('From'),
    '#size' => 60,
    '#default_value' => variable_get('site_mail'),
  );
  
  $form['actions'] = array(
    '#type' => 'container',
    '#attributes' => ['class' => array('form-actions')],
    '#weight' => 400,
  );
  
  // We add the form #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = array();
  
  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }
  
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Send'),
    '#submit' => $submit + ['counselling_email_test_form_submit'],
  );
  
  return $form;
}

function counselling_email_test_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  
  $send = new EmailManager();
  $send->test($values['test_mail']);
  
}