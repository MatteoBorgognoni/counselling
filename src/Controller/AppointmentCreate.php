<?php

namespace Drupal\counselling\Controller;

use Drupal\practitioner\Entity\Practitioner;
use Drupal\practitioner\Entity\PractitionerController;
use Entity;

class AppointmentCreate {
  

  public static function build($user) {
  
    $form = entity_ui_get_form('appointment', appointment_create(), 'add');
  
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'appointment_create',
      '#elements' => [
        'form' => $form,
      ],
    ];
  
    return $page;
    
  }
  
}