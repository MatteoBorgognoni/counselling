<?php


/**
 * Form callback: create or edit a Practitioner.
 */
function practitioner_manage_form($form, &$form_state, $practitioner, $op = 'edit', $user = NULL) {
  switch ($op) {
    case 'add':
      $user_wrapper = entity_metadata_wrapper('user', $user);
      $default_email = $user->mail;
      $default_first_name = $user_wrapper->field_user_first_name->value();
      $default_last_name = $user_wrapper->field_user_last_name->value();
      break;
    default:
      $default_email = $practitioner->email;
      $default_first_name = $practitioner->first_name;
      $default_last_name = $practitioner->last_name;
      break;
  }
  
  $practitioner->uid = $user->uid;
  
  // Add the default field elements.
  $form['email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email address'),
    '#default_value' => $default_email,
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
  
  $form['room'] = array(
    '#type' => 'textfield',
    '#title' => t('Room url'),
    '#default_value' => isset($practitioner->room) ? $practitioner->room : '',
    '#maxlength' => 255,
  );
  
  // Add the field related form elements.
  $form_state['practitioner'] = $practitioner;
  field_attach_form('practitioner', $practitioner, $form, $form_state);
  
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
    '#value' => t('Save Profile'),
    '#submit' => $submit + array('practitioner_manage_form_submit'),
  );
  

  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('practitioner_manage_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Practitioner form.
 */
function practitioner_manage_form_validate(&$form, &$form_state) {
  $practitioner = $form_state['practitioner'];
  
  // Notify field widgets to validate their data.
  field_attach_form_validate('practitioner', $practitioner, $form, $form_state);
}

/**
 * Form API submit callback for the Practitioner form.
 */
function practitioner_manage_form_submit(&$form, &$form_state) {
  // Save the entity and go back to the list of Practitioner.
  $practitioner = entity_ui_controller('practitioner')->entityFormSubmitBuildEntity($form, $form_state);
  
  // Add in created and changed times.
  $is_new_entity = $practitioner->is_new = isset($practitioner->is_new) ? $practitioner->is_new : 0;
  if ($is_new_entity) {
    $practitioner->created = time();
  }
  
  $practitioner->changed = time();
  
  
  $practitioner->save();
  
  // Send feedback message to the user.
  $message = t("Practitioner :label updated.", array(':label' => $practitioner->email));
  
  if ($is_new_entity) {
    $message = t("Practitioner :label created.", array(':label' => $practitioner->email));
  }
  
  drupal_set_message($message);
  
  $form_state['redirect'] = 'user/' . $practitioner->uid . '/counselling/practitioner';
}

