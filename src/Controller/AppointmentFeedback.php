<?php

namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\practitioner\Entity\PractitionerController;
use Entity;

class AppointmentFeedback {
  

  public static function build(Appointment $appointment) {
    
    if (!$appointment->score()) {    $form = drupal_get_form('appointment_feedback_form', $appointment);
  
      $page = [
        '#theme' => 'counselling__page',
        '#key' => 'appointment_feedback',
        '#elements' => [
          'form' => $form,
        ],
      ];
  
      return $page;
    }
    else {
     
      drupal_set_message('You have already left feedback.', 'warning');
      global $user;
      return drupal_goto('user/' . $user->uid . '/counselling/patient');
    
    }
    
  }
  
}