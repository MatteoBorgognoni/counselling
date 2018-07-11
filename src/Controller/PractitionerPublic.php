<?php

namespace Drupal\counselling\Controller;

use Drupal\counselling\CounsellingManager;
use Drupal\practitioner\Entity\Practitioner;

class PractitionerPublic {
  
 
  public static function build(Practitioner $practitioner) {
    
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'practitioner_public',
      '#elements' => [
        'profile' => practitioner_page_view($practitioner, 'badge'),
      ],
    ];
    
    return $page;
  }
  
}