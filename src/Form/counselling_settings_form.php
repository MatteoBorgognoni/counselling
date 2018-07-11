<?php

/**
 * Implement hook_form
 */
function counselling_settings_form($form, &$form_state) {
  $form['counselling_settings'] = array(
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Sue Ryder - counselling settings') . '</small></h2>',
  );
  
  // Email.
  
  $form['counselling_settings_mail'] = [
    '#type' => 'fieldset',
    '#title' => t('Email Settings'),
    '#group' => 'counselling_settings',
    '#tree' => TRUE,
  ];
  
  $keys = [
    'assessment_manager' => 'Assessment confirmation - Community Manager',
    'assessment_practitioner' => 'Assessment confirmation - Practitioner',
    'assessment_patient' => 'Assessment confirmation - Patient',
    'booking_manager' => 'Booking confirmation - Community Manager',
    'booking_practitioner' => 'Booking confirmation - Practitioner',
    'booking_patient' => 'Booking confirmation - Patient',
    'canceled' => 'Appointment cancelled',
    'canceled_by_patient' => 'Appointment cancelled by Patient',
    'canceled_by_practitioner' => 'Appointment cancelled by Practitioner',
    'reminder_hour' => 'Appointment reminder - hour',
    'reminder_day' => 'Appointment reminder - day',
    'feedback_request_counsellor' => 'Feedback request - Patient counsellor',
    'feedback_request_volunteer' => 'Feedback request - Patient volunteer',
    'support_approved_counsellor' => 'Support approved - Patient counsellor',
    'support_approved_volunteer' => 'Support approved - Patient volunteer',
  ];
  
  foreach ($keys as $key => $title) {
    
    $mail = variable_get('counselling_settings_mail__' . $key, []);
    
    $form['counselling_settings_mail'][$key] = [
      '#type' => 'fieldset',
      '#title' => t($title),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    
    $form['counselling_settings_mail'][$key]['addressee_type'] = [
      '#type' => 'radios',
      '#title' => 'Addressee type',
      '#default_value' => isset($mail['addressee_type']) ? $mail['addressee_type'] : '',
      '#options' => [
        'single' => 'Single',
        'roles' => 'Roles',
      ],
    ];
    
    $form['counselling_settings_mail'][$key]['to'] = array(
      '#type' => 'textfield',
      '#title' => t('To'),
      '#size' => 60,
      '#default_value' => isset($mail['to']) ? $mail['to'] : '',
      '#states' => [
        'visible' => [
          'input[name="counselling_settings_mail['. $key .'][addressee_type]"]' => ['value' => 'single'],
        ],
      ],
    );
    
    $roles = user_roles(TRUE);
    $options = [];
    foreach ($roles as $role) {
      $options[$role] = $role;
    }
    $form['counselling_settings_mail'][$key]['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Roles'),
      '#options' => $options,
      '#default_value' => isset($mail['roles']) ? $mail['roles'] : [],
      '#states' => [
        'visible' => [
          'input[name="counselling_settings_mail['. $key .'][addressee_type]"]' => ['value' => 'roles'],
        ],
      ],
    );
    
    $form['counselling_settings_mail'][$key]['subject'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject'),
      '#size' => 60,
      '#default_value' => isset($mail['subject']) ? $mail['subject'] : '',
    );
    
    $form['counselling_settings_mail'][$key]['message'] = array(
      '#type' => 'text_format',
      '#title' => t('Message'),
      '#rows' => 9,
      '#default_value' => isset($mail['message']) ? $mail['message']['value'] : '',
    );
    
    $form['counselling_settings_mail'][$key]['from'] = array(
      '#type' => 'textfield',
      '#title' => t('From'),
      '#size' => 60,
      '#default_value' => isset($mail['from']) ? $mail['from'] : variable_get('site_mail'),
    );
    
    $form['counselling_settings_mail'][$key]['tokens'] = array(
      '#theme' => 'token_tree_link',
      '#token_types' => array('user', 'appointment', 'practitioner', 'patient'),
    );
    
  }
  
  $system_settings_default = variable_get('counselling_settings_system', []);
  
  $form['counselling_settings_system'] = [
    '#type' => 'fieldset',
    '#title' => t('General'),
    '#group' => 'counselling_settings',
    '#tree' => TRUE,
  ];
  
  $form['counselling_settings_system']['status'] = [
    '#type' => 'select',
    '#title' => 'Counselling system status',
    '#options' => ['Disabled', 'Enabled'],
    '#default_value' => isset($system_settings_default['status']) ? $system_settings_default['status'] : NULL,
  ];
  
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save configuration'));
  
  if (!empty($_POST) && form_get_errors()) {
    drupal_set_message(t('The settings have not been saved because of the errors.'), 'error');
  }
  
  // By default, render the form using theme_system_settings_form().
  if (!isset($form['#theme'])) {
    $form['#theme'] = 'system_settings_form';
  }
  return $form;
}

function counselling_settings_form_validate($form, &$form_state) {}

function counselling_settings_form_submit($form, &$form_state) {
  // Exclude unnecessary elements.
  form_state_values_clean($form_state);
  
  foreach ($form_state['values'] as $key => $value) {
    
    switch ($key) {
      
      case 'counselling_settings_mail':
        $mail_settings = $value;
        foreach ($mail_settings as $mail_key => $mail_setting) {
          variable_set($key . '__' . $mail_key, $mail_setting);
        }
        break;
        
      default:
        if (is_array($value) && isset($form_state['values']['array_filter'])) {
          $value = array_keys(array_filter($value));
        }
        variable_set($key, $value);
        break;
    }

  }
  
  drupal_set_message(t('The configuration options have been saved.'));
}