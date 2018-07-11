<?php

use Drupal\patient\Entity\Patient;

/**
 * Form callback: edit a Patient.
 */
function patient_manage_form($form, &$form_state, Patient $patient, $op = 'edit', $user = NULL) {
  
  // Add the default field elements.
  $form['email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email address'),
    '#default_value' => isset($patient->email) ? $patient->email : '',
    '#description' => t('We need your email address so that we can send you your appointment confirmations as well as appointment reminders if you opt in below. Our counsellor may also email you if they need to contact you regarding an appointment. You won’t be emailed by anyone else for any other reason.'),
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  
  $form['first_name'] = array(
    '#type' => 'textfield',
    '#title' => t('First name'),
    '#default_value' => isset($patient->first_name) ? $patient->first_name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  
  $form['last_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Last name'),
    '#default_value' => isset($patient->last_name) ? $patient->last_name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  
  $reminderEmailPrefix = '<div class="form-item form-item-reminder-email-prefix"><label>' . t('Appointment reminders') . '</label><div class="description">' . t('We can send you appointment reminders by email both 24hrs and then again 30 minutes before each appointment. Email reminders will be sent to the email address you choose above.') . '</div></div>';
  
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
  
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save profile'),
    '#submit' => $submit + array('patient_manage_form_submit'),
  );
  
  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('patient_manage_form_validate');
  
  return $form;

}

/**
 * Form API validate callback for the patient form.
 */
function patient_manage_form_validate(&$form, &$form_state) {
  $patient = $form_state['patient'];
  
  // Notify field widgets to validate their data.
  field_attach_form_validate('patient', $patient, $form, $form_state);
}

/**
 * Form API submit callback for the patient form.
 */
function patient_manage_form_submit(&$form, &$form_state) {
  // Save the entity and go back to the list of patient.
  $patient = entity_ui_controller('patient')->entityFormSubmitBuildEntity($form, $form_state);
  
  // Add in created and changed times.
  $is_new_entity = $patient->is_new = isset($patient->is_new) ? $patient->is_new : 0;
  if ($is_new_entity) {
    $patient->created = time();
  }
  
  $patient->changed = time();
  
  
  $patient->save();
  
  // Send feedback message to the user.
  $message = t("patient :label updated.", array(':label' => $patient->email));
  
  if ($is_new_entity) {
    $message = t("patient :label created.", array(':label' => $patient->email));
  }
  
  drupal_set_message($message);
  
  $form_state['redirect'] = 'user/' . $patient->uid . '/counselling/patient';
}

