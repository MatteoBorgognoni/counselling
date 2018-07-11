<?php


namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\CounsellingManager;
use Drupal\patient\Entity\Patient;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\counselling\Storage;

class BookingConfirmation {
  
  public static function build() {
  
    $breadcrumb = [
      l(t('Home'), '<front>'),
      l(t('Advice & support '), '/'),
      l(t('Community'), '/community'),
      l(t('Online bereavement counselling service'), '/bereavement-counselling'),
      t('Booking confirmation'),
    ];
    drupal_set_breadcrumb($breadcrumb);
    
    $storage = new Storage('booking_confirmation');
    
    $appointment = !empty($storage->get('appointment')) ? appointment_load($storage->get('appointment')) : FALSE;
    if($appointment) {
      
      switch ($appointment->type) {
        case 'assessment':
          $title = 'Your video support assessment has been booked';
          $subtitle = 'Your video support assessment is booked for';
          $introduction = '<div class="introduction">Your video support assessment has now been booked with one of our Sue Ryder counsellors. You will receive an email confirmation shortly with details of your counsellor and how to begin your assessment.</div>';
          break;
        case 'counselling':
          $title = 'Your video support session has been booked';
          $subtitle = 'Your video support session is booked for';
          $introduction = '<div class="introduction">Your video support session has now been booked. You will receive an email confirmation shortly with details of your session and how to start the video chat.</div>';
          break;
      }
      
      drupal_set_title(t($title));
      
      $confirmation_details = [
        '#theme' => 'counselling__confirmation_details',
        '#appointment' => $appointment,
        '#heading' => $subtitle . ':',
      ];
      
      $next_steps = [
        '#theme' => 'counselling__next_steps',
        '#type' => $appointment->type,
      ];
  
      $page = [
        '#theme' => 'counselling__page',
        '#key' => 'booking_confirmation',
        '#elements' => [
          'introduction' => $introduction,
          'confirmation-details' => $confirmation_details,
          'next_steps' => $next_steps,
        ],
      ];
      
      $storage->clear();
      return $page;
    }
    else {
      global $user;
      return drupal_goto('user/' . $user->uid . '/counselling/patient' );
    }
    
  }

  
}