<?php

use Drupal\counselling\Storage;
use Drupal\patient\Entity\Patient;
use Drupal\counselling\CounsellingManager;
use Drupal\appointment\Entity\Appointment;
use Drupal\counselling\EmailManager;

/**
 * Form callback: create a Patient.
 */
function patient_confirm_form($form, &$form_state, $type, Patient $patient, Appointment $appointment) {
 
  // Add the breadcrumb for the form's location.
  patient_confirm_set_breadcrumb($type);
  
  
  // Add the field related form elements.
  $form_state['patient'] = $patient;
  $form_state['appointment'] = $appointment;
  
  if($type == 'assessment') {
    
    $form['terms'] = [
      '#type' => 'fieldset',
      '#title' => t('Terms of service'),
      '#collapsible' => FALSE,
    ];
    $label = 'I have read and agree to the Sue Ryder bereavement video support <a href="' . TERMS_CONDITIONS_PATH . '" target="_blank">Terms of Service</a>';

    $form['terms']['accepted'] = [
      '#type' => 'checkbox',
      '#title' => t($label),
      '#attributes' => [
        'required' => 'required',
      ],
    ];
    
    $form['terms']['privacy'] = [
      '#type' => 'markup',
      '#markup' => '<p>To see how we use and protect your information please see our <a href="https://www.sueryder.org/privacy-summary-and-privacy-policy" target="_blank">Privacy Policy</a></p>'
    ];
  }
  
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
    '#value' => t('Book my appointment'),
    '#submit' => $submit + ['patient_confirm_form_submit'],
    '#name' => 'book',
  );

  $form['#validate'] = ['patient_confirm_form_validate'];
  return $form;
}

/**
 * Form API validate callback for the Patient form.
 */
function patient_confirm_form_validate(&$form, &$form_state) {

}

/**
 * Form API submit callback for the Patient form.
 */
function patient_confirm_form_submit(&$form, &$form_state) {
  $trigger = $form_state['triggering_element']['#name'];
  
  switch ($trigger) {
    case 'book':
      /** @var Patient $patient */
      $patient = $form_state['patient'];
      $appointment = $form_state['appointment'];
  
      $patient->save();
      
      $patient_role = user_role_load_by_name('patient');
      if($patient_role) {
        user_multiple_role_edit([$patient->uid], 'add_role', $patient_role->rid);
      }
  
      $appointment->patient = $patient->id;
      $appointment->state = APPOINTMENT_STATE_BOOKED;
      $appointment->save();
  
      $send_mail = new EmailManager();
      $send_mail->bookingManager($appointment);
      $send_mail->bookingPractitioner($appointment);
      $send_mail->bookingPatient($appointment);
  
      $storage = new Storage('patient_booking');
      $storage->clear();
  
      $storage = new Storage('booking_confirmation');
      $storage->set('appointment', $appointment->id);
  
      $form_state['redirect'] = 'bereavement-counselling/booking-confirmation';
      break;
  }
  

}

/**
 * Sets the breadcrumb for administrative Patient pages.
 */
function patient_confirm_set_breadcrumb($type) {
  
  switch ($type) {
    case 'counselling':
      $title = 'Confirm your video support session';
      break;
    case 'assessment':
      $title = 'Confirm your video support assessment';
      break;
    default:
      $title = 'Confirm your booking';
      break;
  }
  
  drupal_set_title(t($title));
  
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Advice & support '), '/'),
    l(t('Community'), '/community'),
    l(t('Online bereavement counselling service'), '/bereavement-counselling'),
    t('Summary'),
  );
  drupal_set_breadcrumb($breadcrumb);
}
