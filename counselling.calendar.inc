<?php

use Drupal\counselling\CounsellingManager;
use Drupal\appointment\AppointmentManager;

/*************************************************
 *
 *  Calendar form element
 *
 ************************************************/

/**
 * Implements hook_element_info().
 */
function counselling_element_info() {
  $elements = [];
  $elements['counselling_calendar'] = [
    '#input' => TRUE,
    '#process' => ['counselling_calendar_process'],
    '#element_validate' => ['counselling_calendar_validate'],
    '#value_callback' => 'counselling_calendar_value_callback',
    '#appointments' => [],
    '#practitioners' => [],
    '#appointment_type' => NULL,
  ];
  return $elements;
}

function counselling_calendar_value_callback($element, $input = FALSE, &$form_state) {
  
  if ($input !== FALSE) {
    $value = $input;
    return $value;
  }
  elseif (!empty($element['#default_value'])) {
    return $element['#default_value'];
  }
  
  return;
}

function counselling_calendar_process($element, &$form_state, $complete_form) {
  
  if($element['#prefix']) {
    $form['prefix'] = [
      '#type' => 'markup',
      '#markup' => $element['#prefix'],
    ];
  }
  
  $patient_type = '';
  $appointment_type = $element['#appointment_type'];
  
  if ($appointment_type == 'counselling') {
    $patient_type = strtolower($form_state['patient']->type(FALSE));
    $heading_practitioner = '<h2>1. Select a ' . $patient_type . ' to speak to</h2>';
    $heading_calendar = '<h2>2. Select a date (only available dates shown)</h2>';
    $heading_times = '<h2>3. Select a time (only available times shown)</h2>';
  }
  else {
    $heading_practitioner = '';
    $heading_calendar = '<h2>1. Select a date (only available dates shown)</h2>';
    $heading_times = '<h2>2. Select a time (only available times shown)</h2>';
  }
  
  $counsellingManager = new CounsellingManager();
  $element['#tree'] = TRUE;
  
  
  if($appointment_type == 'counselling') {
    
    $element['practitioner_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'practitioner-wrapper',
      ],
    ];
    
    $element['practitioner_wrapper']['heading'] = [
      '#type' => 'markup',
      '#markup' => t($heading_practitioner)
    ];
    
    $element['practitioner_wrapper']['practitioner'] = [
      '#theme' => 'counselling__practitioner_choice',
      '#practitioners' => $element['#practitioners'],
    ];
    
    $element['practitioner_wrapper']['practitioner_id'] = [
      '#type' => 'hidden',
      '#value' => isset($element['#value']['practitioner_id']) ? $element['#value']['practitioner_id'] : '',
      '#attributes' => ['id' => 'practitioner-id'],
    ];
    
    foreach ($element['#practitioners'] as $id => $practitioner) {
      $appointments_by_practitioner = $counsellingManager->getAvailableAppointments('counselling', $id);
      $events_by_practitioner = counselling_prepare_events($appointments_by_practitioner, $element['#default_value']);
      drupal_add_js(['counselling' => ['events_' . $id => array_values($events_by_practitioner)]], 'setting');
    }
  }
  
  $element['calendar_wrapper'] = [
    '#type' => 'container',
    '#attributes' => [
      'id' => 'calendar-wrapper',
      'class' => $appointment_type == 'counselling' ? ['disabled-wrapper'] : [],
    ],
  ];
  
  
  $element['calendar_wrapper']['heading'] = [
    '#type' => 'markup',
    '#markup' => t($heading_calendar)
  ];
  
  if ($appointment_type == 'counselling') {
    $element['calendar_wrapper']['help'] = [
      '#type' => 'markup',
      '#markup' => t('<div class="help">Please select a ' . $patient_type . ' to speak to first</div>'),
    ];
  }
  
  $element['calendar_wrapper']['calendar'] = [
    '#theme' => 'counselling__calendar',
    '#appointments' => $element['#appointments'],
    '#selected_event' => $element['#default_value'],
    '#appointment_type' => $appointment_type,
  ];
  
  $time_array = [];
  foreach ($element['#appointments'] as $appointment) {
    $date = date('Y-m-d', $appointment->date);
    $time_start = date('H:i', $appointment->date);
    $time_end =  date('H:i', $appointment->date_end);
    $time_array[$date][$appointment->practitioner][$appointment->id] = $time_start . '-' . $time_end;
  }
  
  $element['times_wrapper'] = [
    '#type' => 'container',
    '#attributes' => [
      'id' => 'times-wrapper',
      'class' => ['disabled-wrapper'],
    ],
  ];
  
  
  $element['times_wrapper']['heading'] = [
    '#type' => 'markup',
    '#markup' => t($heading_times)
  ];
  
  $element['times_wrapper']['help'] = [
    '#type' => 'markup',
    '#markup' => t('<div class="help">Please select a date first</div>'),
  ];
  
  foreach ($time_array as $date => $times_data) {
    $element['times_wrapper'][$date] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['times-container'],
        'id' => $date,
        'style' => "display: none;"
      ],
      '#tree' => TRUE,
    ];
    foreach ($times_data as $practitioner_id => $times) {

      foreach ($times as $appointment_id => $time) {
        $element['times_wrapper'][$date][$appointment_id] = [
          '#type' => 'link',
          '#title' => $time,
          '#href' => 'javascript:void(0)',
          '#options' => [
            'fragment' => '',
            'external' => TRUE,
            'attributes' => [
              'onclick' => 'return false;',
              'class' => ['appointment-time', 'practitioner-' . $practitioner_id],
              'appointment_id' => $appointment_id,
              'practitioner_id' => $practitioner_id,
            ]
          ],
        ];
      }
  
    }
  }
  
  $element['appointment_id'] = [
    '#type' => 'hidden',
    '#value' => isset($element['#value']['appointment_id']) ? $element['#value']['appointment_id'] : '',
    '#attributes' => ['id' => 'appointment-id'],
  ];
  
  if($element['#suffix']) {
    $form['prefix'] = [
      '#type' => 'markup',
      '#markup' => $element['#prefix'],
    ];
  }
  
  return $element;
}

function counselling_calendar_validate($element, &$form_state) {

}


/**
 * Implements hook_process_HOOK().
 */
function template_preprocess_counselling__calendar(&$variables) {
  
  libraries_load('fullcalendar');
  drupal_add_js(drupal_get_path('module', 'counselling') .'/js/calendar.js');
  drupal_add_css(drupal_get_path('module', 'counselling') .'/css/calendar.css');
  $appointments = $variables['appointments'];
  $selected_event = $variables['selected_event'];
  $events = counselling_prepare_events($appointments, $selected_event);
  
  drupal_add_js(['counselling' => ['events' => array_values($events)]], 'setting');
}

function counselling_prepare_events($appointments, $selected_event = []) {
  $events = [];
  
  if (!empty($appointments)) {
    foreach ($appointments as $appointment) {
      $date = date('Y-m-d', $appointment->date);
      $events[$date]['id'] = $appointment->id;
      $events[$date]['title'] = date('d', $appointment->date);
      $events[$date]['start'] = $date;
      $events[$date]['stringDate'] = $date;
      if(isset($selected_event['appointment_id']) && $selected_event['appointment_id'] == $appointment->id) {
        $events[$date]['className'] = 'selected-date';
      }
    }
  }
  
  return $events;
}
