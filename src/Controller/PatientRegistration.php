<?php


namespace Drupal\counselling\Controller;

use Drupal\counselling\CounsellingManager;
use Drupal\counselling\Storage;

class PatientRegistration {
  
  public static function details() {
  
    $manager = new CounsellingManager();
    $system_is_enabled = $manager->getSystemStatus();
    
    if(!$system_is_enabled) {
      return drupal_goto('community');
    }
    
    $storage = new Storage('patient_booking');
    $storage->set('step', 1);
    
    $patient = patient_create();
    if ($storage->get('patient')) {
      $patient = $storage->get('patient');
    }
  
    $step_indicator = [
      '#theme' => 'counselling__step_indicator',
      '#steps' => 3,
      '#current_step' => 1,
    ];
    
    $form = drupal_get_form('patient_register_form', $patient);
    drupal_set_title('Register for online bereavement counselling service');
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'register_details',
      '#elements' => [
        'step_indicator' => $step_indicator,
        'form' => $form,
      ],
    ];
    
    return $page;
  }
  
  public static function assessment() {
    $storage = new Storage('patient_booking');
    $storage->set('step', 2);
  
    if (!$storage->get('patient')) {
      drupal_goto('bereavement-counselling/register/your-details');
    }
     /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = $storage->get('patient');
  
    $step_indicator = [
      '#theme' => 'counselling__step_indicator',
      '#steps' => 3,
      '#current_step' => 2,
    ];
    
    $form = drupal_get_form('patient_booking_form', 'assessment', $patient);
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'register_assessment',
      '#elements' => [
        'step_indicator' => $step_indicator,
        'form' => $form,
      ],
    ];
    
    return $page;
  }
  
  public static function confirm() {
    $storage = new Storage('patient_booking');
    $storage->set('step', 3);
  
    if (!$storage->get('patient')) {
      drupal_goto('bereavement-counselling/register/your-details');
    }
    /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = $storage->get('patient');
    /** @var \Drupal\appointment\Entity\Appointment $appointment */
    $appointment = $storage->get('appointment');
  
    $step_indicator = [
      '#theme' => 'counselling__step_indicator',
      '#steps' => 3,
      '#current_step' => 3,
    ];
  
    $confirmation_details = [
      '#theme' => 'counselling__confirmation_details',
      '#appointment' => $storage->get('appointment'),
      '#change_link' => 'bereavement-counselling/register/your-assessment',
      '#change_link_title' => 'Change this date/time',
      '#heading' => 'Your video assessment will be on:',
    ];
    
    
    $form = drupal_get_form('patient_confirm_form', 'assessment', $patient, $appointment);
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'register_confirm',
      '#elements' => [
        'step_indicator' => $step_indicator,
        'confirmation-details' => $confirmation_details,
        'form' => $form,
      ],
    ];
    
    return $page;
  }
  
}