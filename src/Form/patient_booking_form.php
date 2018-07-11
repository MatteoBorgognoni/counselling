<?php

use Drupal\counselling\Storage;
use Drupal\patient\Entity\Patient;
use Drupal\counselling\CounsellingManager;
use Drupal\appointment\AppointmentManager;
use Drupal\appointment\Entity\Appointment;
/**
 * Form callback: create a Patient.
 */
function patient_booking_form($form, &$form_state, $type, Patient $patient) {
  
  // Add the breadcrumb for the form's location.
  patient_booking_set_breadcrumb($type);
  
  $counselingManager = new CounsellingManager();
  
  // Add the field related form elements.
  $form_state['patient'] = $patient;
  $form_state['appointment_type'] = $type;

  $appointments = $counselingManager->getAvailableAppointments($type);
  
  $practitioner_type = 'counsellor';
  
  
  $continue_button_text = 'Continue to step 3';
  
  if($type == 'counselling') {
    $practitioner_type = $patient->type() ? $patient->type() : NULL;
    $continue_button_text = 'Continue to step 2';
  }
  
  $practitioners = $counselingManager->getAvailablePractitioners($practitioner_type, $type);
  
  $storage = new Storage('patient_booking');
  if($appointment = $storage->get('appointment')) {
    $default_value = [
      'appointment_id' => $appointment->id,
      'practitioner_id' => $appointment->practitioner,
    ];
    $js_settings = [
      'eventId' => $appointment->id,
      'selectedDate' => date('Y-m-d',$appointment->date),
      'practitionerId' => $appointment->practitioner,
      'appointmentType' => $type,
    ];
    drupal_add_js(['counselling' => $js_settings], 'setting');
  }
  else {
    $default_value = NULL;
  }
  
  $form['appointment'] = [
    '#type' => 'counselling_calendar',
    '#title' => 'Calendar',
    '#default_value' => $default_value,
    '#appointments' => $appointments,
    '#practitioners' => $practitioners,
    '#appointment_type' => $type,
    '#prefix' => '<div id="booking-calendar">',
    '#suffix' => '</div>',
  ];
  
  
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
    '#submit' => $submit + ['patient_booking_form_submit'],
    '#limit_validation_errors' => [],
    '#name' => 'back',
    '#value' => t('Back'),
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#name' => 'continue',
    '#value' => t($continue_button_text),
    '#submit' => $submit + ['patient_booking_form_submit'],
  );

  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('patient_booking_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Patient form.
 */
function patient_booking_form_validate(&$form, &$form_state) {
  $patient = $form_state['patient'];
  $values = $form_state['values'];
  $appointment_id = $values['appointment']['appointment_id'];
  
  $trigger = $form_state['triggering_element']['#name'];
  
  switch ($trigger) {
    case 'continue':
      /** @var Appointment $appointment */
      $appointment = appointment_load($appointment_id);
      $appointment_type = $form_state['appointment_type'];
  
      if($appointment_type == 'counselling') {
        /** @var AppointmentManager $appointmentManager */
        $appointmentManager = new AppointmentManager($appointment);
        if ($form_state['submitted'] && $appointmentManager->appointmentOverlaps('patient', $patient->identifier())) {
          form_set_error('time_start', 'You have already booked an appointment at this time. Please change the date and/or time.');
        }
      }
      break;
    case 'back':
      break;
  }
}

/**
 * Form API submit callback for the Patient form.
 */
function patient_booking_form_submit(&$form, &$form_state) {
  $trigger = $form_state['triggering_element']['#name'];
  $values = $form_state['values'];
  $appointment_type = $form_state['appointment_type'];
  
  switch ($trigger) {
    case 'continue':
      if(isset($values['appointment']) && isset($values['appointment']['appointment_id'])) {
        $id = $values['appointment']['appointment_id'];
        $storage = new Storage('patient_booking');
        /** @var \Drupal\appointment\Entity\Appointment $appointment */
        $appointment =  appointment_load($id);
        $appointment->setTypeValue($appointment_type);
        $storage->set('appointment', $appointment);
      }

      switch ($appointment_type) {
        case 'assessment':
          $form_state['redirect'] = 'bereavement-counselling/register/confirm';
          break;
        case  'counselling':
          $form_state['redirect'] = 'bereavement-counselling/book/confirm';
          break;
        default:
          break;
      }
      
      break;
    case 'back':
      
      switch ($appointment_type) {
        case 'assessment':
          $form_state['redirect'] = 'bereavement-counselling/register/your-details';
          break;
        case 'counselling':
          global $user;
          $storage = new Storage('patient_booking');
          $storage->clear();
          $form_state['redirect'] = 'user/' . $user->uid . '/counselling/patient';
          break;
      }
      
      break;
  }

}



/**
 * Sets the breadcrumb for administrative Patient pages.
 */
function patient_booking_set_breadcrumb($type) {
  
  switch ($type) {
    case 'counselling':
      $title = 'Book your video support session';
      break;
    case 'assessment':
      $title = 'Book your assessment';
      break;
    default:
      $title = 'Book appointment';
      break;
  }
  
  drupal_set_title(t($title));
  
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Advice & support '), '/'),
    l(t('Community'), '/community'),
    l(t('Online bereavement counselling service'), '/bereavement-counselling'),
    t($title),
  );
  drupal_set_breadcrumb($breadcrumb);
}
