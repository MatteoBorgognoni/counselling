<?php

namespace Drupal\counselling\Controller;

use Drupal\counselling\CounsellingManager;
use Drupal\counselling\QueryManager;
use Drupal\counselling\Storage;

class PatientProfile {
  

  public static function redirect($user) {
    $is_active = (bool) $user->patient->status;
    if ($is_active) {
      return drupal_goto('user/' . $user->uid . '/counselling/patient/appointments');
    }
    else {
      return drupal_goto('user/' . $user->uid . '/counselling/patient/enable');
    }
    
  }
  
  public static function view($user) {
    /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = patient_load_from_uid($user->uid);
    
    $disable_link = l(
      'Disable counselling account',
      'user/' . $user->uid . '/counselling/disable',
       ['attributes' => ['class' => ['disable-profile-link']]]
    );
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'patient_view',
      '#elements' => [
        'profile' => patient_page_view($patient),
        'disable_link' => $disable_link,
      ],
    ];
    
    return $page;
  }
  
  
  public static function edit($user) {
    /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = patient_load_from_uid($user->uid);
  
    $form = drupal_get_form('patient_manage_form', $patient, 'edit', $user);
    
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'patient_edit',
      '#elements' => [
        'form' => $form,
      ],
    ];
    
    return $page;
    
  }
  
  public static function appointments($user) {
    
    $counsellingManager = new CounsellingManager($user->uid);
    $next = $counsellingManager->getNextAppointmentTable('Next appointments');
    $past = $counsellingManager->getPastAppointmentTable('Past appointments');
    $archive = $counsellingManager->getArchivedAppointmentTable();
    
    $build = [
      '#theme' => 'counselling__appointment_page',
      '#elements' => [
        'next_appointments' => $next,
        'past_appointments' => $past,
        'archive' => $archive,
      ],
    ];
  
    drupal_add_js(drupal_get_path('module', 'counselling') .'/js/table_pager.js', ['weight' => 50,]);
    return $build;
  }
  
  public static function book($user) {
    
    /** @var \Drupal\patient\Entity\Patient $patient */
    $patient = $user->patient;
    if(!$patient->type()) {
      $storage = new Storage('patient_booking');
      $storage->set('patient', $patient);
      return drupal_goto('bereavement-counselling/register/your-assessment');
    }
    return drupal_goto('bereavement-counselling/book/new');
    
  }
  
  public static function enable($user) {
    
//    $form = drupal_get_form('patient_enable_form', $user);
//
//    $page = [
//      '#theme' => 'counselling__page',
//      '#key' => 'patient_enable',
//      '#elements' => [
//        'text' => 'Click the button below to re-enable your counselling account',
//        'form' => $form,
//      ],
//    ];
    
    $message = '';
    $message .= '<p>Your account is disabled.</p>';
    $message .= '<p>To re-activate, please contact <a href="mailto: online.community@sueryder.org">online.community@sueryder.org</a></p>';
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'patient_enable',
      '#elements' => [
        'message' => $message,
      ],
    ];
    
    return $page;
    
  }
  
}