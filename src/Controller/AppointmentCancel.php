<?php

namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\practitioner\Entity\PractitionerController;
use Entity;

class AppointmentCancel {
  

  public static function build(Appointment $appointment) {
    $form = drupal_get_form('appointment_cancel_form', $appointment);
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'appointment_cancel',
      '#elements' => [
        'form' => $form,
      ],
    ];
    return $page;
  }
  
  public static function admin(Appointment $appointment) {
    $form = drupal_get_form('appointment_cancel_form', $appointment, TRUE);
    $page = [
      '#theme' => 'counselling__page',
      '#key' => 'appointment_cancel',
      '#elements' => [
        'form' => $form,
      ],
    ];
    return $page;
  }
  
}