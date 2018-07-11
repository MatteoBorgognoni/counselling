<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\Storage;

/**
 * Form callback: create or edit a Appointment.
 */
function appointment_feedback_form($form, &$form_state, Appointment $appointment) {
  // Add the breadcrumb for the form's location.
  
  global $user;
  appointment_feedback_set_breadcrumb($user->uid);
  
  $form['#attached']['libraries_load'][] = [
    'barrating',
  ];
  
  $form['#attached']['js'][] = [
    'data' => drupal_get_path('module', 'counselling') . '/js/rating.js',
    'type' => 'file',
    'weight' => 500,
  ];
  
  $form['score'] = [
    '#type' => 'select',
    '#title' => t('Was it easy to log in and use the video chat today? Rate how well the video chat worked.'),
    '#required' => TRUE,
    '#chosen' => FALSE,
    '#options' => [
      1 => 1,
      2 => 2,
      3 => 3,
      4 => 4,
      5 => 5
    ],
    '#default_value' => $appointment->score,
    '#attributes' => ['class' => ['counselling-score']],
  ];
  
  $form['feedback'] = [
    '#type' => 'textarea',
    '#title' => t('Tell us more about what worked well or didn\'t.'),
    '#rows' => 9,
    '#default_value' => $appointment->feedback,
  ];
  
  // Add the field related form elements.
  $form_state['appointment'] = $appointment;
  
  $form['actions'] = [
    '#type' => 'container',
    '#attributes' => ['class' => ['form-actions']],
    '#weight' => 400,
  ];
  
  // We add the form #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = [];
  
  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }
  
  $form['actions']['back'] = [
    '#type' => 'submit',
    '#value' => t('Back'),
    '#submit' => $submit + ['appointment_feedback_form_submit'],
    '#limit_validation_errors' => [],
    '#name' => 'back',
  ];
  
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Leave feedback'),
    '#submit' => $submit + ['appointment_feedback_form_submit'],
    '#name' => 'save',
  ];
  
  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = ['appointment_feedback_form_validate'];
  
  return $form;
}

/**
 * Form API validate callback for the Appointment form.
 */
function appointment_feedback_form_validate(&$form, &$form_state) {}

/**
 * Form API submit callback for the Appointment form.
 */
function appointment_feedback_form_submit(&$form, &$form_state) {
  $op = $form_state['triggering_element']['#name'];
  global $user;
  
  switch ($op) {
    case 'back':
      break;
    case 'save':
      // Save the entity and go back to the list of Appointment.
      $appointment = entity_ui_controller('appointment')->entityFormSubmitBuildEntity($form, $form_state);
      $appointment->changed = time();
      $appointment->uid = $user->uid;
      $appointment->save();
      break;
  }
  
  if (user_access('administer appointment')) {
    $form_state['redirect'] = 'admin/content/appointment';
  }
  else {
    $storage = new Storage('thank_you');
    $storage->set('visited', TRUE);
    $form_state['redirect'] = 'bereavement-counselling/thank-you/feedback';
    //$form_state['redirect'] = 'user/' . $user->uid . '/counselling/patient/appointments';
  }
  
}

/**
 * Sets the breadcrumb for administrative Appointment pages.
 */
function appointment_feedback_set_breadcrumb($uid) {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Advice & support '), '/'),
    l(t('Community'), '/community'),
    l(t('Online bereavement counselling profile'), 'user/' . $uid . '/counselling/patient/appointments'),
    t('Update appointment'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}

