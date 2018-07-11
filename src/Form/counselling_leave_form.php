<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\EmailManager;
use Drupal\counselling\CounsellingManager;

/**
 * Form callback: Leave counselling service.
 */
function counselling_leave_form($form, &$form_state, $account) {
  // Add the breadcrumb for the form's location.
  counselling_leave_set_breadcrumb($account->uid);
  
  global $user;
  
  $message = '<div class="form-message">You are going to disable your counselling service account. Please click "Back" if you want to go back.</div>';
  
  $form['message'] = [
    '#type' => 'markup',
    '#markup' => t($message),
  ];
  
  
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
    '#value' => t('Back'),
    '#submit' => $submit + ['counselling_leave_form_submit'],
    '#limit_validation_errors' => [],
    '#name' => 'back',
  );
  
  $form['actions']['leave'] = array(
    '#type' => 'submit',
    '#value' => t('Confirm'),
    '#submit' => $submit + ['counselling_leave_form_submit'],
    '#name' => 'leave'
  );


  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('counselling_leave_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Appointment form.
 */
function counselling_leave_form_validate(&$form, &$form_state) {

}

/**
 * Form API submit callback for the Appointment form.
 */
function counselling_leave_form_submit(&$form, &$form_state) {
  
  global $user;
  $trigger = $form_state['triggering_element']['#name'];
  switch ($trigger) {
    case 'leave':
      $patient = $form_state['patient'];
      $patient->status = 0;
      $patient->save();
      $form_state['redirect'] = 'community';
      break;
    case 'back':
      $form_state['redirect'] = 'user/' . $user->uid . '/counselling/patient/appointments';
      break;
  }
  
}

/**
 * Sets the breadcrumb
 */
function counselling_leave_set_breadcrumb($uid) {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Online bereavement counselling profile'), 'user/' . $uid . '/counselling/patient'),
    t('Leave counselling service')
  );
  
  drupal_set_breadcrumb($breadcrumb);
}

