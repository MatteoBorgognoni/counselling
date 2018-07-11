<?php


namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\CounsellingManager;
use Drupal\patient\Entity\Patient;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\counselling\Storage;

class PatientBooking {
  

  public static function book() {
    
    global $user;

    $storage = new Storage('patient_booking');
    $storage->set('step', 1);
  
    /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = patient_load_from_uid($user->uid);
    
    $step_indicator = [
      '#theme' => 'counselling__step_indicator',
      '#steps' => 2,
      '#current_step' => 1,
    ];

    $form = drupal_get_form('patient_booking_form', 'counselling', $patient);
    
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'booking_form',
      '#elements' => [
        'step_indicator' => $step_indicator,
        'form' => $form,
      ],
    ];
    
    return $page;
  }
  
  public static function confirm() {
  
    global $user;
    
    $storage = new Storage('patient_booking');
    $storage->set('step', 2);
  
    /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = patient_load_from_uid($user->uid);
    /** @var \Drupal\appointment\Entity\Appointment $appointment */
    $appointment = $storage->get('appointment');
  
    $step_indicator = [
      '#theme' => 'counselling__step_indicator',
      '#steps' => 2,
      '#current_step' => 2,
    ];
  
    $confirmation_details = [
      '#theme' => 'counselling__confirmation_details',
      '#appointment' => $storage->get('appointment'),
      '#change_link' => 'bereavement-counselling/book/new',
      '#change_link_title' => 'Change this date/time',
      '#heading' => 'Your appointment will be on:',
    ];
    
    $form = drupal_get_form('patient_confirm_form', 'counselling', $patient, $appointment);
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'booking_confirm',
      '#elements' => [
        'step_indicator' => $step_indicator,
        'confirmation-details' => $confirmation_details,
        'form' => $form,
      ],
    ];
    
    return $page;
  }
  
}