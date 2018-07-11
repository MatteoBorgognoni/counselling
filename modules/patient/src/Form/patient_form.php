<?php


/**
 * Form callback: create or edit a Patient.
 */
function patient_form($form, &$form_state, $patient, $op = 'edit', $entity_type) {
  // Add the breadcrumb for the form's location.
  patient_set_breadcrumb();
  
  // Add the default field elements.
  $form['email'] = [
    '#type' => 'textfield',
    '#title' => t('Email address'),
    '#default_value' => isset($patient->email) ? $patient->email : '',
    '#maxlength' => 255,
    '#required' => TRUE,
  ];
  
  $form['first_name'] = [
    '#type' => 'textfield',
    '#title' => t('First name'),
    '#default_value' => isset($patient->first_name) ? $patient->first_name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
  ];
  
  $form['last_name'] = [
    '#type' => 'textfield',
    '#title' => t('Last name'),
    '#default_value' => isset($patient->last_name) ? $patient->last_name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
  ];

  $form['reminder_email'] = [
    '#type' => 'checkbox',
    '#title' => t('Appointment reminders'),
    '#description' => t('We can send you appointment reminders by email both 24hrs and then again 30 minutes before each appointment. Email reminders will be sent to the email address you choose above.'),
    '#label' => t('I agree to be contacted by email to remind me ahead of each appointment I have booked.'),
    '#default_value' => isset($patient->reminder_email) ? $patient->reminder_email : '',
    '#required' => FALSE,
  ];

  // Add the field related form elements.
  $form_state['patient'] = $patient;
  field_attach_form('patient', $patient, $form, $form_state);
  
  if(user_access('administer patient')) {
    
    $form['uid'] = [
      '#type' => 'select',
      '#title' => t('User'),
      '#options' => patient_get_user_options(),
      '#default_value' => isset($patient->uid) ? $patient->uid : NULL,
      '#required' => TRUE,
      '#weight' => 28,
    ];
    
    $form['type'] = [
      '#type' => 'radios',
      '#title' => t('Patient type'),
      '#options' => [
        'volunteer' => 'Support Worker',
        'counsellor' => 'Counsellor',
      ],
      '#default_value' => isset($patient->type) ? $patient->type : NULL,
      '#weight' => 29,
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
  
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Patient'),
    '#submit' => $submit + array('patient_edit_form_submit'),
  );
  
  if (!empty($patient->email)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Patient'),
      '#suffix' => l(t('Cancel'), 'admin/content/patient'),
      '#submit' => $submit + array('patient_form_submit_delete'),
      '#weight' => 45,
    );
  }
  
  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('patient_edit_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Patient form.
 */
function patient_edit_form_validate(&$form, &$form_state) {
  $patient = $form_state['patient'];
  
  // Notify field widgets to validate their data.
  field_attach_form_validate('patient', $patient, $form, $form_state);
}

/**
 * Form API submit callback for the Patient form.
 */
function patient_edit_form_submit(&$form, &$form_state) {
  // Save the entity and go back to the list of Patient.
  $patient = entity_ui_controller('patient')->entityFormSubmitBuildEntity($form, $form_state);
  
  // Add in created and changed times.
  $is_new_entity = $patient->is_new = isset($patient->is_new) ? $patient->is_new : 0;
  if ($is_new_entity) {
    $patient->created = time();
  }
  
  $patient->changed = time();
  
  if(!isset($form_state['values']['uid'])) {
    global $user;
    $patient->uid = $user->uid;
  }
  
  $patient->save();
  
  // Send feedback message to the user.
  $message = t("Patient :label updated.", array(':label' => $patient->email));
  
  if ($is_new_entity) {
    $message = t("Patient :label created.", array(':label' => $patient->email));
  }
  
  drupal_set_message($message);
  
  $form_state['redirect'] = 'admin/content/patient';
}

/**
 * Form API submit callback for the delete button.
 */
function patient_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/patient/manage/' . $form_state['patient']->id . '/delete';
}

/**
 * Sets the breadcrumb for administrative Patient pages.
 */
function patient_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('Content'), 'admin/content'),
    l(t('Patient'), 'admin/content/patient'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}

function patient_get_user_options() {
  
  $query = db_select('users', 'u');
  $query->condition('status', 1);
  $query->fields('u', ['uid', 'name']);
  $query->orderBy('name');
  
  $options = $query->execute()->fetchAllKeyed();
  return $options;
}