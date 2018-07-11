<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\EmailManager;

/**
 * Form callback: create or edit a Appointment.
 */
function appointment_cancel_form($form, &$form_state, Appointment $appointment, $is_admin = FALSE) {
  // Add the breadcrumb for the form's location.
  
  global $user;
  $current_state = (int) $appointment->state;
  
  if ($current_state !== APPOINTMENT_STATE_BOOKED) {
    $message = '<div class="form-message">You cannot cancel this appointment. Please click "Back" to return to the appointment list.</div>';
  }
  else {
    $message = '<div class="form-message">You are going to cancel this appointment. Please click "Back" if you want to go back.</div>';
  }

  
  if(patient_has_profile($user->uid)) {
    $appointment->state = APPOINTMENT_STATE_CANCELED_PATIENT;
    appointment_cancel_set_breadcrumb('patient', $user->uid);
  }
  elseif(practitioner_has_profile($user->uid)) {
    $appointment->state = APPOINTMENT_STATE_CANCELED_PRACTITIONER;
    appointment_cancel_set_breadcrumb('practitioner', $user->uid);
  }
  elseif($is_admin) {
    $appointment->state = APPOINTMENT_STATE_CANCELED_ADMIN;
  }
  else {
    return $form;
  }

  $form['message'] = [
    '#type' => 'markup',
    '#markup' => t($message),
  ];
  
  // Add the field related form elements.
  $form_state['appointment'] = $appointment;
  // Add the field related form elements.
  $form_state['is_admin'] = $is_admin;
  
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
    '#submit' => $submit + ['appointment_cancel_form_submit'],
    '#limit_validation_errors' => [],
    '#name' => 'back',
  );
  
  if ($current_state == APPOINTMENT_STATE_BOOKED) {
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel Appointment'),
      '#submit' => $submit + ['appointment_cancel_form_submit'],
      '#name' => 'cancel'
    );
  }

  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('appointment_cancel_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Appointment form.
 */
function appointment_cancel_form_validate(&$form, &$form_state) {
  $appointment = $form_state['appointment'];
}

/**
 * Form API submit callback for the Appointment form.
 */
function appointment_cancel_form_submit(&$form, &$form_state) {
  
  global $user;
  
  $is_admin = $form_state['is_admin'];
  
  $trigger = $form_state['triggering_element']['#name'];
  switch ($trigger) {
    case 'cancel':
      // Save the entity and go back to the list of Appointment.
      $appointment = entity_ui_controller('appointment')->entityFormSubmitBuildEntity($form, $form_state);
      $appointment->changed = time();

      $appointment->uid = $user->uid;
  
      $appointment->save();
  
      $send_mail = new EmailManager();
      $send_mail->cancelled($appointment);
      
      switch ($appointment->state) {
        case APPOINTMENT_STATE_CANCELED_PRACTITIONER:
        case APPOINTMENT_STATE_CANCELED_ADMIN:
          $send_mail->cancelledByPractitioner($appointment);
          break;
        case APPOINTMENT_STATE_CANCELED_PATIENT:
          $send_mail->cancelledByPatient($appointment);
          $appointment_manager = new AppointmentManager($appointment);
          $appointment_manager->duplicate();
          break;
      }
      
      // Send feedback message to the user.
      $message = t("Appointment cancelled.");
  
      drupal_set_message($message);
      break;
    case 'back':
      break;
  }
  
  if (user_access('administer appointment') || $is_admin) {
    $form_state['redirect'] = 'admin/counselling/appointment';
  } else {
    if (practitioner_has_profile($user->uid)) {
      $form_state['redirect'] = 'user/' . $user->uid . '/counselling/practitioner/appointments';
    }
    if (patient_has_profile($user->uid)) {
      $form_state['redirect'] = 'user/' . $user->uid . '/counselling/patient/appointments';
    }
  }
  
}

/**
 * Sets the breadcrumb for administrative Appointment pages.
 */
function appointment_cancel_set_breadcrumb($type, $uid) {
  
  switch ($type) {
    case 'patient':
      $profile_url = 'user/' . $uid . '/counselling/patient/appointments';
      break;
    case 'practitioner':
      $profile_url = 'user/' . $uid . '/counselling/practitioner/appointments';
      break;
    default:
      $profile_url = 'user/' . $uid;
      break;
  }
  
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Advice & support '), '/'),
    l(t('Community'), '/community'),
    l(t('Online bereavement counselling profile'), $profile_url),
    t('Change appointment'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}

