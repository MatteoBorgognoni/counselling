<?php

namespace Drupal\counselling\Controller;

use Drupal\counselling\CounsellingManager;

class PractitionerProfile {
  

  public static function redirect($user) {
  
    return drupal_goto('user/' . $user->uid . '/counselling/practitioner/appointments');
    
  }
  
  public static function view($user) {
    
    if (!practitioner_has_profile($user->uid)) {
      return drupal_goto('user/' . $user->uid . '/counselling/practitioner/create');
    }
    
    $practitioner = practitioner_load_from_uid($user->uid);
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'practitioner_view',
      '#elements' => [
        'profile' => practitioner_page_view($practitioner),
      ],
    ];
    
    return $page;
  }
  
  
  public static function edit($user) {
  
    if (!practitioner_has_profile($user->uid)) {
      return drupal_goto('user/' . $user->uid . '/counselling/practitioner/create');
    }
    
    $practitioner = practitioner_load_from_uid($user->uid);
    
    $form = drupal_get_form('practitioner_manage_form', $practitioner, 'edit', $user);
    
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'practitioner_edit',
      '#elements' => [
        'form' => $form,
      ],
    ];
  
    return $page;
    
  }
  
  public static function create($user) {
    
    if (practitioner_has_profile($user->uid)) {
      return drupal_goto('user/' . $user->uid . '/counselling/practitioner/view');
    }
    
    $form = drupal_get_form('practitioner_manage_form', practitioner_create(), 'add', $user);
    
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'practitioner_create',
      '#elements' => [
        'form' => $form,
      ],
    ];
    
    return $page;
  }
  
  public static function appointments($user) {
  
    if (!practitioner_has_profile($user->uid)) {
      return drupal_goto('user/' . $user->uid . '/counselling/practitioner/create');
    }
    
    drupal_add_library('system', 'drupal.ajax');
    $counsellingManager = new CounsellingManager($user->uid);
  
    $next = $counsellingManager->getNextAppointmentTable('Next appointments');
    $past = $counsellingManager->getPastAppointmentTable('Past appointments');
    $archive = $counsellingManager->getArchivedAppointmentTable();
    //$assessments = $counsellingManager->getAssessmentAppointmentTable('Patients to be assessed');
    $scheduled = $counsellingManager->getScheduledAppointmentTable('Available appointments');
    
    $build = [
      '#theme' => 'counselling__appointment_page',
      '#elements' => [
        'next_appointments' => $next,
        //'assessments' => $assessments,
        'past_appointments' => $past,
        'archive' => $archive,
        'scheduled' => $scheduled,
      ],
    ];
  
    drupal_add_js(drupal_get_path('module', 'counselling') .'/js/table_pager.js', ['weight' => 50,]);
    return $build;
  }
  
}