<?php


/**
 * Form callback: create or edit a Practitioner.
 */
function practitioner_form($form, &$form_state, $practitioner, $op = 'edit', $entity_type) {
  // Add the breadcrumb for the form's location.
  practitioner_set_breadcrumb();
  $form['#prefix'] = '<div id="practitioner-form-wrapper">';
  $form['#suffix'] = '</div>';
  
  $default_email = isset($practitioner->email) ? $practitioner->email : '';
  $default_first_name = isset($practitioner->first_name) ? $practitioner->first_name : '';
  $default_last_name = isset($practitioner->last_name) ? $practitioner->last_name: '';
  
  if ($op == 'add') {
    $default_uid = '';
    if (isset($form_state['values'])) {
      $values = $form_state['values'];
      if(isset($values['uid']) && !empty($values['uid'])) {
        $user = user_load($values['uid']);
        $user_wrapper = entity_metadata_wrapper('user', $user);
        $default_uid = $user->uid;
        $practitioner->email = $user->mail;
        $practitioner->first_name = $user_wrapper->field_user_first_name->value();
        $practitioner->last_name = $user_wrapper->field_user_last_name->value();
      }
    }
    
    $form['uid'] = [
      '#type' => 'select',
      '#title' => 'Select user',
      '#empty_option' => ' - Please select - ',
      '#options' => practitioner_get_eligible_users(),
      '#default_value' => $default_uid,
      '#ajax' => [
        'callback' => 'practitioner_form_ajax_callback',
        'wrapper' => 'practitioner-form-wrapper',
        'method' => 'replace',
      ],
    ];
  }
  
  $form['type'] = [
    '#type' => 'select',
    '#title' => 'Select practitioner type',
    '#empty_option' => ' - Please select - ',
    '#required' => TRUE,
    '#options' => [
      'counsellor' => 'Counsellor',
      'volunteer' => 'Support worker',
    ],
    '#default_value' => isset($practitioner->type) ? $practitioner->type : '',
  ];
  
  // Add the default field elements.
  $form['email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email address'),
    '#default_value' => isset($practitioner->email) ? $practitioner->email : '',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  
  $form['first_name'] = array(
    '#type' => 'textfield',
    '#title' => t('First name'),
    '#default_value' => isset($practitioner->first_name) ? $practitioner->first_name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  
  $form['last_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Last name'),
    '#default_value' => isset($practitioner->last_name) ? $practitioner->last_name: '',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  
  if ($op == 'add') {
    $form['email']['#value'] = isset($practitioner->email) ? $practitioner->email : '';
    $form['first_name']['#value'] = isset($practitioner->first_name) ? $practitioner->first_name : '';
    $form['last_name']['#value'] = isset($practitioner->last_name) ? $practitioner->last_name : '';
  }
  
  $form['room'] = array(
    '#type' => 'textfield',
    '#title' => t('Room url'),
    '#default_value' => isset($practitioner->room) ? $practitioner->room : '',
    '#maxlength' => 255,
    '#required' => TRUE,
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
    '#value' => t('Save Practitioner'),
    '#submit' => $submit + array('practitioner_edit_form_submit'),
  );
  
  if (!empty($practitioner->email)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Practitioner'),
      '#suffix' => l(t('Cancel'), 'admin/content/practitioner'),
      '#submit' => $submit + array('practitioner_form_submit_delete'),
      '#weight' => 45,
    );
  }
  
  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('practitioner_edit_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Practitioner form.
 */
function practitioner_edit_form_validate(&$form, &$form_state) {
  $practitioner = $form_state['practitioner'];
  // Notify field widgets to validate their data.
  field_attach_form_validate('practitioner', $practitioner, $form, $form_state);
}

/**
 * Form API submit callback for the Practitioner form.
 */
function practitioner_edit_form_submit(&$form, &$form_state) {
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

  if (user_access('edit any practitioner profile')) {
    $form_state['redirect'] = 'admin/counselling/practitioners';
  }
  else {
    $form_state['redirect'] = 'admin/content/practitioners';
  }
}

/**
 * Form API submit callback for the delete button.
 */
function practitioner_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/practitioner/manage/' . $form_state['practitioner']->id . '/delete';
}

function practitioner_form_ajax_callback($form, $form_state) {
  return $form;
}

/**
 * Sets the breadcrumb for administrative Practitioner pages.
 */
function practitioner_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('Content'), 'admin/content'),
    l(t('Practitioner'), 'admin/content/practitioner'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}
