<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\EmailManager;
use Drupal\counselling\Storage;

/**
 * Form callback: create or edit a Appointment.
 */
function appointment_assign_form($form, &$form_state, Appointment $appointment) {

  // Add the field related form elements.
  $form_state['appointment'] = $appointment;
  
  $manager = new AppointmentManager($appointment);
  
  $appointment_type = $appointment->type;
  
  $form['#prefix'] = '<div id="appointment-form-wrapper">';
  $form['#suffix'] = '</div>';
  
  if($appointment_type == 'assessment|counselling') {
    $form['type'] = [
      '#type' => 'radios',
      '#title' => 'Assign to',
      '#options' => [
        'assessment' => 'Assessment',
        'counselling' => 'Counselling',
      ],
      '#required' => TRUE,
      '#weight' => 0,
      '#ajax' => [
        'callback' => 'appointment_assign_form_ajax_callback',
        'wrapper' => 'appointment-form-wrapper',
        'method' => 'replace',
      ],
    ];
  }
  else {
    $form['type'] = [
      '#type' => 'hidden',
      '#value' => $appointment_type,
    ];
  }
  
  if(isset($form_state['values']) && isset($form_state['values']['type'])) {
    $appointment_type = $form_state['values']['type'];
  }
  
  $form['patient'] = [
    '#type' => 'select',
    '#title' => 'Patient',
    '#options' => $manager->getPatientOptions($appointment_type),
    '#required' => TRUE,
    '#default_value' => $appointment->patient,
    '#empty_option' => ' - Please select a patient -',
    '#weight' => 1,
  ];
  
  if($appointment_type == 'assessment|counselling') {
    $form['patient']['#states'] = [
      'visible' => [
        'input[name="type"]' => ['checked' => TRUE],
      ],
    ];
  }

  
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
    '#submit' => $submit + ['appointment_assign_form_submit'],
    '#limit_validation_errors' => [],
    '#name' => 'back',
  );
  
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Assign Appointment'),
    '#submit' => $submit + ['appointment_assign_form_submit'],
    '#name' => 'assign'
  );
  
  return $form;
}

/**
 * Form API validate callback for the Appointment form.
 */
function appointment_assign_form_validate(&$form, &$form_state) {
  $appointment = $form_state['appointment'];
  $patient_id = $form_state['values']['patient'];
  $manager = new AppointmentManager($appointment);
  if($manager->appointmentOverlaps('patient', $patient_id)) {
    form_set_error('patient', 'This patient has already booked an appointment for this date / time');
  }
}

/**
 * Form API submit callback for the Appointment form.
 */
function appointment_assign_form_submit(&$form, &$form_state) {
  
  global $user;
  $trigger = $form_state['triggering_element']['#name'];
  switch ($trigger) {
    case 'assign':
      // Save the entity and go back to the list of Appointment.
      $appointment = entity_ui_controller('appointment')->entityFormSubmitBuildEntity($form, $form_state);
      $appointment->changed = time();
      $appointment->uid = $user->uid;
      $appointment->state = 2;
      $appointment->save();
  
      $send_mail = new EmailManager();
      $send_mail->bookingManager($appointment);
      $send_mail->bookingPractitioner($appointment);
      $send_mail->bookingPatient($appointment);
      
      break;
  }
  $form_state['redirect'] = 'admin/counselling/appointments';
}


function appointment_assign_form_ajax_callback($form, $form_state) {
  return $form;
}
