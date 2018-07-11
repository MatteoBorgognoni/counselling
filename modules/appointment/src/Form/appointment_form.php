<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;

/**
 * Form callback: create or edit a Appointment.
 */
function appointment_form($form, &$form_state, Appointment $appointment, $op = 'edit', $entity_type) {
  // Add the breadcrumb for the form's location.
  appointment_set_breadcrumb();
  
  $form['#prefix'] = '<div id="appointment-form-wrapper">';
  $form['#suffix'] = '</div>';
  
  $manager = new AppointmentManager($appointment);
  
  if(user_access('edit any appointment')) {
    $form['practitioner'] = [
      '#type' => 'select',
      '#title' => 'Practitioner',
      '#options' => $manager->getPractitionerOptions(),
      '#required' => TRUE,
      '#default_value' => $appointment->practitioner,
      '#empty_option' => ' - Please select -',
      '#ajax' => [
        'callback' => 'appointment_form_type_ajax_callback',
        'wrapper' => 'appointment-form-wrapper',
        'method' => 'replace',
      ],
      '#weight' => 0,
    ];
  }
  else {
    $form['practitioner'] = [
      '#type' => 'hidden',
      '#name' => 'practitioner',
      '#value' => $appointment->practitioner,
    ];
  }
  
  $form['type'] = [
    '#type' => 'checkboxes',
    '#title' => 'Available for...',
    '#options' => [
      'assessment' => 'Assessment',
      'counselling' => 'Counselling',
    ],
    '#required' => TRUE,
    '#weight' => 10,
  ];
  // Hide type field if practitioner volunteer
  global $user;
  /** @var \Drupal\practitioner\Entity\Practitioner $practitioner */
  $practitioner = practitioner_load_from_uid($user->uid);
  
  if ($practitioner && $practitioner->type == 'volunteer') {
    $form['type'] += [
      '#default_value' => ['counselling'],
      '#prefix' => '<div style="display: none;">',
      '#suffix' => '</div>',
    ];
  }
  else {
    $form['type'] += [
      '#default_value' => !empty($appointment->type) ? $appointment->getTypeValue() : ['assessment', 'counselling'],
    ];
    
  }
  
  $default_date = !empty($appointment->date)? date('Y-m-d', $appointment->date) : date('Y-m-d', REQUEST_TIME);
  $default_time_start = !empty($appointment->time_start) ? $appointment->time_start : date('H', REQUEST_TIME) . ':00';
  $default_time_end = !empty($appointment->time_end) ? $appointment->time_end : date('H', REQUEST_TIME + 3600) . ':00';
  
  $query = drupal_get_query_parameters();
  
  if(!empty($query)) {
    if(isset($query['date'])) {
      $default_date = $query['date'];
    }
  
    if(isset($query['time_start'])) {
      $default_time_start = $query['time_start'];
    }
    
    if(isset($query['time_end'])) {
      $default_time_end = $query['time_end'];
    }
  }
  
  $form['date'] = [
    '#type' => 'date_popup',
    '#title' => 'Date',
    '#default_value' => $default_date,
    '#required' => TRUE,
    '#date_type' => DATE_UNIX,
    '#date_timezone' => date_default_timezone(),
    '#date_format' => 'd/m/Y',
    '#date_increment' => 1,
    '#date_year_range' => '0:+3',
    '#weight' => 20,
  ];
  
  $time_options_start = appointment_get_hours_range(8,20, 1800);
  
  $form['time_start'] = [
    '#type' => 'select',
    '#title' => 'Time start',
    '#options' => $time_options_start,
    '#default_value' => $default_time_start,
    '#required' => TRUE,
    '#weight' => 30,
    '#ajax' => [
      'callback' => 'appointment_form_timestart_ajax_callback',
      'wrapper' => 'appointment-form-wrapper',
      'method' => 'replace',
    ],
  ];
  
  
  $time_options_end = appointment_get_hours_range(9,21, 1800);
  
  $form['time_end'] = [
    '#type' => 'select',
    '#title' => 'Time end',
    '#options' => $time_options_end,
    '#default_value' => $default_time_end,
    '#required' => TRUE,
    '#weight' => 40,
    '#ajax' => [
      'callback' => 'appointment_form_timeend_ajax_callback',
      'wrapper' => 'appointment-form-wrapper',
      'method' => 'replace',
    ],
  ];
  
  if (module_exists('chosen')) {
    $form['time_start']['#chosen'] = FALSE;
    $form['time_end']['#chosen'] = FALSE;
  }
  
  // Add the field related form elements.
  $form_state['appointment'] = $appointment;
  field_attach_form('appointment', $appointment, $form, $form_state);
  
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
  
  if($op == 'edit' && $appointment->state == APPOINTMENT_STATE_SCHEDULED) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#submit' => $submit + array('appointment_edit_form_submit'),
      '#limit_validation_errors' => [],
      '#name' => 'delete',
    );
  }
  
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => empty($appointment->identifier()) ? t('Save Appointment'): t('Update Appointment'),
    '#submit' => $submit + array('appointment_edit_form_submit'),
    '#name' => 'save',
  );
  
  if($op == 'add') {
    $form['actions']['add_one'] = array(
      '#type' => 'submit',
      '#value' => t('Save and add more'),
      '#submit' => $submit + array('appointment_edit_form_submit'),
      '#name' => 'add_one',
    );
  }

  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = array('appointment_edit_form_validate');
  
  return $form;
}

/**
 * Form API validate callback for the Appointment form.
 */
function appointment_edit_form_validate(&$form, &$form_state) {
  $values = $form_state['values'];
  
  /** @var Appointment $appointment */
  $appointment = $form_state['appointment'];
  
  /** @var AppointmentManager $appointmentManager */
  $appointmentManager = new AppointmentManager($appointment);
  
  if (is_string($values['date']) && (bool) strtotime($values['date'])) {
    $appointment->date = strtotime($values['date']);
  }
  else {
    $appointment->date = REQUEST_TIME;
  }
  $appointment->time_start = $values['time_start'];
  $appointment->time_end = $values['time_end'];
  $appointment->practitioner = $values['practitioner'];
  
  if($values['practitioner']) {
    /** @var \Drupal\practitioner\Entity\Practitioner $practitioner */
    $practitioner = practitioner_load($values['practitioner']);
    $practitioner_type = $practitioner ? $practitioner->type() : FALSE;
  
    if ($practitioner_type && $practitioner_type == 'volunteer' && in_array('assessment', array_filter($values['type']))) {
      form_set_error('type', 'Practitioner volunteers cannot book assessments. Please untick the "Assessment" option or change the practitioner');
    }
  }
  
  $time_start = (int) str_replace(':', '', $values['time_start']);
  $time_end = (int) str_replace(':', '', $values['time_end']);
  if ($time_start >= $time_end) {
    form_set_error('time_end', '"Time end" field cannot be lower than "Time start"');
  }
  
  if ($form_state['submitted'] && $values['practitioner'] && $appointmentManager->appointmentOverlaps('practitioner', $practitioner->identifier())) {
    form_set_error('time_start', 'This appointment conflicts with another one. Please change the date and/or time.');
  }
  
  //form_set_error('test', 'test');
  field_attach_form_validate('appointment', $appointment, $form, $form_state);
}

/**
 * Form API submit callback for the Appointment form.
 */
function appointment_edit_form_submit(&$form, &$form_state) {
  
  global $user;
  // Save the entity and go back to the list of Appointment.
  /** @var Appointment $appointment */
  $appointment = entity_ui_controller('appointment')->entityFormSubmitBuildEntity($form, $form_state);
  
  $trigger = $form_state['triggering_element']['#name'];
  switch ($trigger) {
    case 'delete':
      $appointment->delete();
      break;
    case 'save':
    case 'add_one':
      $appointment->date = strtotime($form_state['values']['date']);
      // Add in created and changed times.
      $is_new_entity = $appointment->is_new = isset($appointment->is_new) ? $appointment->is_new : 0;
      if ($is_new_entity) {
        $appointment->created = time();
        $appointment->state = APPOINTMENT_STATE_SCHEDULED;
      }
  
      $appointment->changed = time();
      $appointment->uid = $user->uid;
      $appointment->save();
  
      // Send feedback message to the user.
      $message = t("Appointment updated.");
      if ($is_new_entity) {
        $message = t("Appointment booked.");
      }
      drupal_set_message($message);
      break;
  }
  
  // set redirect
  switch ($trigger) {
    case 'add_one':
      
      $date = date('Y-m-d', $appointment->date);
      $time_start = str_pad((int) substr($appointment->time_start, 0, 2) + 1, 1, 0, STR_PAD_LEFT) . ':' . substr($appointment->time_start, 3, 2);
      $time_end = str_pad((int) substr($time_start, 0, 2) + 1, 1, 0, STR_PAD_LEFT) . ':' . substr($time_start, 3, 2);
      
      $query = [
        'date' => $date,
        'time_start' => $time_start,
        'time_end' => $time_end,
      ];
      
      if (user_access('administer appointment') || user_access('edit any appointment')) {
        $form_state['redirect'] = [
          'admin/content/appointment/add',
          ['query' => $query],
        ];
      }
      else {
        $form_state['redirect'] = [
          'user/' . $user->uid . '/counselling/practitioner/create-appointment/',
          ['query'=> $query],
        ];
      }
      break;
      
    default:
      if (user_access('administer appointment')) {
        $form_state['redirect'] = 'admin/content/appointment';
      }
      elseif (user_access('edit any appointment')) {
        $form_state['redirect'] = 'admin/counselling/appointments';
      }
      else {
        $form_state['redirect'] = 'user/' . $user->uid . '/counselling/practitioner/appointments';
      }
      break;
  }
  

  
}

function appointment_form_type_ajax_callback($form, $form_state) {
  
  if (isset($form_state['values']) && !$form_state['appointment']->id) {
    $values = $form_state['values'];
    /** @var \Drupal\practitioner\Entity\Practitioner $practitioner */
    $practitioner = practitioner_load($values['practitioner']);
    $practitioner_type = $practitioner->type();
    
    switch ($practitioner_type) {
      case 'volunteer':
        $form['type']['assessment']['#checked'] = FALSE;
        $form['type']['assessment']['#attributes'] = ['disabled' => 'disabled'];
        break;
      case 'counsellor':
        $form['type']['assessment']['#checked'] = TRUE;
        $form['type']['assessment']['#attributes'] = [];
        break;
    }
  }
  
  return $form;
}

function appointment_form_timestart_ajax_callback($form, $form_state) {
  
  if (isset($form_state['values'])) {
    $values = $form_state['values'];
    $time_start = $values['time_start'];
    
    if($time_start == '21:00' || $time_start == '20:30') {
      $form['time_start']['#value'] = '20:00';
      $form['time_end']['#value'] = '21:00';
      form_set_error('time_start', 'The latest time to book an appointment is 20:00');
    }
    else {
      $time_end = str_pad((int) substr($time_start, 0, 2) + 1, 1, 0, STR_PAD_LEFT) . ':' . substr($time_start, 3, 2);
      $form['time_end']['#value'] = $time_end;
    }
    
  }
  
  return $form;
}

function appointment_form_timeend_ajax_callback($form, $form_state) {
  
  if (isset($form_state['values'])) {
    $values = $form_state['values'];
    $time_start = (int) str_replace(':', '', $values['time_start']);
    $time_end = (int) str_replace(':', '', $values['time_end']);
    if ($time_start >= $time_end) {
      form_set_error('time_end', '"Time end" field cannot be lower than "Time start"');
    }
  }
  
  return $form;
}


/**
 * Form API submit callback for the delete button.
 */
function appointment_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/appointment/manage/' . $form_state['appointment']->id . '/delete';
}

/**
 * Sets the breadcrumb for administrative Appointment pages.
 */
function appointment_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('Content'), 'admin/content'),
    l(t('Appointment'), 'admin/content/appointment'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}


function appointment_get_hours_range( $lower = 0, $upper = 23, $step = 3600, $format = '' ) {
  $lower = $lower * 3600;
  $upper = $upper * 3600;
  
  $times = [];
  
  if (empty($format)) {
    $format = 'H:i';
  }
  
  foreach (range($lower, $upper, $step) as $increment) {
    $increment = gmdate( 'H:i', $increment );
    
    list($hour, $minutes) = explode(':',$increment);
    
    $date = new DateTime($hour . ':' . $minutes);
    
    $times[(string) $increment] = $date->format( $format );
  }
  
  return $times;
}