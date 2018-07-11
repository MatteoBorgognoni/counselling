<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\EmailManager;
use Drupal\counselling\Storage;

/**
 * Form callback: create or edit a Appointment.
 */
function appointment_change_form($form, &$form_state, Appointment $appointment) {
  
  global $user;
  // Add the breadcrumb for the form's location.
  appointment_change_set_breadcrumb($user->uid);
  
  $message = '<div class="form-message">You are going to change this appointment. Please click "Cancel" if you want to go back.</div>';
  
  $form['message'] = [
    '#type' => 'markup',
    '#markup' => t($message),
  ];
  
  // Add the field related form elements.
  $form_state['appointment'] = $appointment;
  
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
    '#submit' => $submit + ['appointment_change_form_submit'],
    '#limit_validation_errors' => [],
    '#name' => 'back',
  );
  
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Change Appointment'),
    '#submit' => $submit + ['appointment_change_form_submit'],
    '#name' => 'change'
  );
  
  return $form;
}

/**
 * Form API validate callback for the Appointment form.
 */
function appointment_change_form_validate(&$form, &$form_state) {
}

/**
 * Form API submit callback for the Appointment form.
 */
function appointment_change_form_submit(&$form, &$form_state) {
  
  global $user;
  $trigger = $form_state['triggering_element']['#name'];
  switch ($trigger) {
    case 'change':
      // Save the entity and go back to the list of Appointment.
      $appointment = entity_ui_controller('appointment')->entityFormSubmitBuildEntity($form, $form_state);
      $appointment->changed = time();

      $appointment->uid = $user->uid;
  
      $appointment->state = APPOINTMENT_STATE_CHANGED;
      $appointment->save();
  
      $url = 'community';
  
      switch ($appointment->type()) {
        case 'assessment':
          $storage = new Storage('patient_booking');
          $storage->set('patient', $appointment->patient());
      
          $url = 'bereavement-counselling/register/your-assessment';
          break;
        case 'counselling':
          $url = 'bereavement-counselling/book/new';
          break;
      }
  
      $appointment_manager = new AppointmentManager($appointment);
      $appointment_manager->duplicate();
      
      $form_state['redirect'] = $url;
      break;
    case 'back':
      $form_state['redirect'] = 'user/' . $user->uid . '/counselling/patient/appointments';
      break;
  }
  
}

/**
 * Sets the breadcrumb for administrative Appointment pages.
 */
function appointment_change_set_breadcrumb($uid) {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Advice & support '), '/'),
    l(t('Community'), '/community'),
    l(t('Online bereavement counselling service'), 'user/' . $uid . '/counselling/patient/appointments'),
    t('Change appointment'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}

