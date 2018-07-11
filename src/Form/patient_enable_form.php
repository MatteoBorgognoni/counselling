<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\EmailManager;
use Drupal\counselling\CounsellingManager;

/**
 * Form callback: Re-enable counselling profile.
 */
function patient_enable_form($form, &$form_state, $account) {
  // Add the breadcrumb for the form's location.
  patient_enable_set_breadcrumb($account->uid);
  
  global $user;

  // Add the field related form elements.
  $form_state['patient'] = $account->patient;
  
  $form['actions'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('form-actions')),
    '#weight' => 400,
  );
  
  // We add the form #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = array();
  
  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }
  
  $form['actions']['back'] = array(
    '#type' => 'submit',
    '#limit_validation_errors' => [],
    '#submit' => $submit + ['patient_enable_form_submit'],
    '#value' => t('Back'),
    '#name' => 'back',
  );
  
  $form['actions']['enable'] = array(
    '#type' => 'submit',
    '#value' => t('Re-enable account'),
    '#submit' => $submit + ['patient_enable_form_submit'],
    '#name' => 'enable'
  );


  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('patient_enable_form_validate');
  
  return $form;
}

function patient_enable_form_validate(&$form, &$form_state) {

}

function patient_enable_form_submit(&$form, &$form_state) {
  
  global $user;
  $trigger = $form_state['triggering_element']['#name'];
  switch ($trigger) {
    case 'enable':
      $patient = $form_state['patient'];
      $patient->status = 1;
      $patient->save();
      $form_state['redirect'] = 'user/' . $user->uid . '/counselling/patient';
      break;
    case 'back':
      $form_state['redirect'] = 'community';
      break;
  }
  
}

/**
 * Sets the breadcrumb
 */
function patient_enable_set_breadcrumb($uid) {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Counselling'), 'user/' . $uid . '/counselling/patient'),
    t('Re-enable account')
  );
  
  drupal_set_breadcrumb($breadcrumb);
}

