<?php

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\EmailManager;

/**
 * Form callback: create or edit a Appointment.
 */
function appointment_update_form($form, &$form_state, Appointment $appointment) {
  
  global $user;
  // Add the breadcrumb for the form's location.
  appointment_update_set_breadcrumb($user->uid);
  
  $manager = new AppointmentManager($appointment);
  
  $form['#prefix'] = '<div id="appointment-update-form-wrapper">';
  $form['#suffix'] = '</div>';
  
  $form['attended'] = [
    '#type' => 'radios',
    '#title' => 'Has the appointment been attended?',
    '#options' => [
      1 => 'Yes',
      0 => 'No',
    ],
    '#required' => TRUE,
    '#default_value' => $appointment->attended,
    '#ajax' => [
      'callback' => 'appointment_update_form_ajax_callback',
      'wrapper' => 'appointment-update-form-wrapper',
      'method' => 'replace',
    ],
  ];
  
  if($appointment->type() == 'assessment') {
    
    /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = $appointment->patient();
    $form_state['patient'] = $patient;
    
    $form['type'] = array(
      '#type' => 'radios',
      '#title' => t('Set patient type'),
      '#options' => [
        'counsellor' => 'Counsellor',
        'volunteer' => 'Support worker',
      ],
      '#states' => [
        'visible' => [
          ':input[name="attended"]' => ['value' => '1'],
        ],
      ],
    );
    
    if($patient->type()) {
      $form['type']['#default_value'] = $patient->type();
    }
  }
  
  // Add the field related form elements.
  $form_state['appointment'] = $appointment;
  
  $form['actions'] = [
    '#type' => 'container',
    '#attributes' => ['class' => ['form-actions']],
    '#weight' => 400,
  ];
  
  // We add the form #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = [];
  
  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }
  
  $form['actions']['back'] = [
    '#type' => 'submit',
    '#value' => t('Back'),
    '#submit' => $submit + ['appointment_update_form_submit'],
    '#limit_validation_errors' => [],
    '#op' => 'back',
  ];
  
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Update Appointment'),
    '#submit' => $submit + ['appointment_update_form_submit'],
    '#op' => 'save',
  ];

  // We append the validate handler to validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'] = ['appointment_update_form_validate'];
  
  return $form;
}

/**
 * Form API validate callback for the Appointment form.
 */
function appointment_update_form_validate(&$form, &$form_state) {
  $appointment = $form_state['appointment'];
  $values = $form_state['values'];
  
  if ($appointment->type() == 'assessment' && $values['attended'] && !$values['type']) {
    form_set_error('type', 'Please set the patient type');
  }
  
}


function appointment_update_form_ajax_callback($form, $form_state) {

  if (isset($form_state['values']) && $form_state['values']['attended']) {
    $form['type']['#required'] = TRUE;
  }

  return $form;
}

/**
 * Form API submit callback for the Appointment form.
 */
function appointment_update_form_submit(&$form, &$form_state) {
  $op = $form_state['triggering_element']['#op'];
  global $user;
  
  switch ($op) {
    case 'back':

      break;
    case 'save':
      $values = $form_state['values'];
      // Save the entity and go back to the list of Appointment.
      /** @var Appointment $appointment */
      $appointment = $form_state['appointment'];
      $appointment->attended = (int) $values['attended'];
      $appointment->changed = time();
      $appointment->uid = $user->uid;
      if ($appointment->attended) {
        $appointment->state = APPOINTMENT_STATE_ATTENDED;
        $appointment->save();
        
        $send_mail = new EmailManager();
        $send_mail->feedbackRequest($appointment);
      }
      else {
        $appointment->state = APPOINTMENT_STATE_NOT_ATTENDED;
        $appointment->save();
      }
    
      if($appointment->type() == 'assessment' && $values['attended']) {
        /** @var \Drupal\patient\Entity\Patient $patient */
        $patient = $form_state['patient'];
        $patient->type = $values['type'];
        $patient->changed = time();
        $patient->save();
    
        $send_mail = new EmailManager();
        $send_mail->supportApproved($patient);
      }
      break;
  }
  
  if (user_access('administer appointment')) {
    $form_state['redirect'] = 'admin/content/appointment';
  } else {
    $form_state['redirect'] = 'user/' . $user->uid . '/counselling/practitioner/appointments';
  }
  
}

/**
 * Sets the breadcrumb for administrative Appointment pages.
 */
function appointment_update_set_breadcrumb($uid) {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Advice & support '), '/'),
    l(t('Community'), '/community'),
    l(t('Online bereavement counselling profile'), 'user/' . $uid . '/counselling/practitioner/appointments'),
    t('Update appointment'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}

