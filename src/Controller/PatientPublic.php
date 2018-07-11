<?php

namespace Drupal\counselling\Controller;

use Drupal\counselling\CounsellingManager;
use Drupal\patient\Entity\Patient;

class PatientPublic {
  
 
  public static function build(Patient $patient) {
    
    $profile = patient_page_view($patient, 'public');
    
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'patient_public',
      '#elements' => [
        'profile' => $profile,
      ],
    ];
    
    if (user_access('view any appointment')) {
      $user_link = l($patient->user()->name, 'user/' . $patient->uid);
      $first_name = '';
      $last_name = '';
      
      $user_wrapper = entity_metadata_wrapper('user', $patient->user());
      
      $children = [
        '#theme' => 'item_list',
        '#items' => [],
        '#type' => 'ul',
        '#title' => NULL,
      ];
      
      $children['#items']['link'] = 'Account: ' . render($user_link);
  
      if(isset($patient->user()->field_user_first_name) && !empty($patient->user()->field_user_first_name)) {
        $children['#items']['first_name'] = 'First name: ' . $user_wrapper->field_user_first_name->value();
      }
      
      if(isset($patient->user()->field_user_last_name) && !empty($patient->user()->field_user_last_name)) {
        $children['#items']['last_name'] = 'Last name: ' . $user_wrapper->field_user_last_name->value();
      }
      
      $user = [
        'element' => [
          '#type' => 'fieldset',
          '#title' => t('User'),
          '#attributes' => ['class' => ['collapsible']],
          '#children' => render($children),
        ],
      ];
  
      
      $page['#elements']['user'] = render($user);
    }
    
    return $page;
  }
  
}