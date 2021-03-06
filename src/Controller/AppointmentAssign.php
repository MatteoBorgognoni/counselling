<?php

namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\practitioner\Entity\PractitionerController;
use Entity;

class AppointmentAssign {
  

  public static function build(Appointment $appointment) {
    $form = drupal_get_form('appointment_assign_form', $appointment);
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'appointment_assign',
      '#elements' => [
        'form' => $form,
      ],
    ];
    return $page;
  }
  
}