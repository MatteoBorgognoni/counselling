<?php

namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\practitioner\Entity\PractitionerController;
use Entity;

class LeaveService {
  

  public static function build($account) {
    
    $form = drupal_get_form('counselling_leave_form', $account);
    
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'leave_service',
      '#elements' => [
        'form' => $form,
      ],
    ];
    
    return $page;
    
  }
  
}