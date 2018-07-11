<?php

use Drupal\counselling\Storage;
use Drupal\patient\Entity\Patient;

/**
 * Form callback: create a Patient.
 */
function patient_register_form($form, &$form_state, Patient $patient) {
  
  // Add the breadcrumb for the form's location.
  patient_register_set_breadcrumb();
  
  global $user;
  $user = user_load($user->uid);
  $user_wrapper = entity_metadata_wrapper('user', $user);
  
  
  if (!empty($patient->email)) {
    $default_email = $patient->email;
  }
  else {
    $default_email = $user->mail;
  }
  
  if (!empty($patient->first_name)) {
    $default_first_name = $patient->first_name;
  }
  else {
    if(isset($user_wrapper->field_user_first_name)) {
      $default_first_name = $user_wrapper->field_user_first_name->value();
    }
  }
  
  if (!empty($patient->last_name)) {
    $default_last_name = $patient->last_name;
  }
  else {
    if(isset($user_wrapper->field_user_last_name)) {
      $default_last_name = $user_wrapper->field_user_last_name->value();
    }
  }
  
  // Add the default field elements.
  $form['email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email address'),
    '#default_value' => $default_email,
    '#description' => t('We need your email address so that we can send you your appointment confirmations as well as appointment reminders if you opt in below. Our counsellor may also email you if they need to contact you regarding an appointment. You won’t be emailed by anyone else for any other reason.'),
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  
  $form['first_name'] = array(
    '#type' => 'textfield',
    '#title' => t('First name'),
    '#default_value' => $default_first_name,
    '#maxlength' => 255,
    '#required' => TRUE,
  );

  $form['last_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Last name'),
    '#default_value' => $default_last_name,
    '#maxlength' => 255,
    '#required' => TRUE,
  );

  $reminderEmailPrefix = '<div class="form-item form-item-reminder-email-prefix"><label>' . t('Appointment reminders') . '<span class="form-optional"> (optional)</span></label><div class="description">' . t('We can send you appointment reminders by email both 24hrs and then again 30 minutes before each appointment. Email reminders will be sent to the email address you choose above.') . '</div></div>';

  $form['reminder_email'] = array(
    '#type' => 'checkbox',
    '#prefix' => $reminderEmailPrefix,
    '#title' => t('I agree to be contacted by email to remind me ahead of each appointment I have booked.'),
    '#default_value' => isset($patient->reminder_email) ? $patient->reminder_email : '',
    '#required' => FALSE,
  );

  // Add the field related form elements.
  $form_state['patient'] = $patient;
  field_attach_form('patient', $patient, $form, $form_state);
  
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
    '#submit' => $submit + ['patient_register_form_submit'],
    '#limit_validation_errors' => [],
    '#name' => 'back',
    '#value' => t('Back'),
  );
  
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Continue to step 2'),
    '#submit' => $submit + array('patient_register_form_submit'),
    '#name' => 'continue',
  );
  
  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('patient_register_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Patient form.
 */
function patient_register_form_validate(&$form, &$form_state) {
  $values = $form_state['values'];
  
  /** @var Patient $patient */
  $patient = $form_state['patient'];
  
  if (patient_load_from_email($values['email'])) {
    $message = 'The email "' . $values['email'] . '" already exists in our system. Please choose another email or get in contact wih Sue Ryder';
    form_set_error('email', t($message));
  }
  
  if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
    $message = 'The email "' . $values['email'] . '" is not valid. Please enter a valid email';
    form_set_error('email', t($message));
  }
  
  // Notify field widgets to validate their data.
  field_attach_form_validate('patient', $patient, $form, $form_state);
}

/**
 * Form API submit callback for the Patient form.
 */
function patient_register_form_submit(&$form, &$form_state) {
  
  $trigger = $form_state['triggering_element']['#name'];
  
  switch ($trigger) {
    case 'continue':
  
      // Save the entity and go back to the list of Patient.
      $patient = entity_ui_controller('patient')->entityFormSubmitBuildEntity($form, $form_state);
  
      // Add in created and changed times.
      $is_new_entity = $patient->is_new = isset($patient->is_new) ? $patient->is_new : 0;
      if ($is_new_entity) {
        $patient->created = time();
      }
  
      $patient->changed = time();
  
      global $user;
      $patient->uid = $user->uid;
  
      $storage = new Storage('patient_booking');
      $storage->set('patient', $patient);
  
      $form_state['redirect'] = 'bereavement-counselling/register/your-assessment';
      
      break;
    case 'back':
    
      $form_state['redirect'] = 'bereavement-counselling';
      $storage = new Storage('patient_booking');
      $storage->clear();
      
      break;
  }
}



/**
 * Sets the breadcrumb for administrative Patient pages.
 */
function patient_register_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Advice & support '), '/'),
    l(t('Community'), '/community'),
    l(t('Online bereavement counselling service'), '/bereavement-counselling'),
    t('Register'),
  );
  drupal_set_breadcrumb($breadcrumb);
}
