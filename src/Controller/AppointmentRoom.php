<?php

namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\practitioner\Entity\PractitionerController;
use Entity;
use DateTime;

class AppointmentRoom {
  

  public static function build(Appointment $appointment) {
  
    global $user;
    
    $now = REQUEST_TIME;
    $date = $appointment->date;
    
    if ($date - $now > 300) {
      $now = new DateTime(date('Y-m-d H:i', REQUEST_TIME));
      $date = new DateTime(date('Y-m-d H:i', $appointment->date));
      $interval = $now->diff($date);
      
      drupal_set_message('The room is not yet available. Please come back in ' . $interval->format("%d days %H hours and %I minutes"), 'warning');
      if ($practitioner = practitioner_load_from_uid($user->uid)) {
        return drupal_goto($practitioner->profileUrl());
      }
      elseif ($patient = patient_load_from_uid($user->uid)) {
        return drupal_goto($patient->profileUrl());
      }
      else {
        return drupal_access_denied();
      }
    }
    else {
      return drupal_goto($appointment->room(), ['external' => TRUE]);
    }
    
  }
  
}