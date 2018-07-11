<?php


namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\CounsellingManager;
use Drupal\patient\Entity\Patient;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\counselling\Storage;

class AdminPages {
  
  public static function redirect() {
    return drupal_goto('admin/counselling/appointments');
  }
  
  public static function appointments() {
  
    $actions = [];
    $actions['add'] = l('Add appointment', 'admin/content/appointment/add');
    
    $build[] = [
      '#theme' => 'counselling__page',
      '#key' => 'admin_appointments',
      '#elements' => [
        'actions' => theme('item_list', ['items' => $actions, 'class' => ['action-links']]),
        'view' => views_embed_view('appointments', 'admin'),
      ],
    ];
    
    return $build;
  }
  
  public static function practitioners() {
    
    $actions = [];
    $actions['add'] = l('Add practitioner', 'admin/content/practitioner/add');
    
    $build[] = [
      '#theme' => 'counselling__page',
      '#key' => 'admin_practitioners',
      '#elements' => [
        'actions' => theme('item_list', ['items' => $actions, 'class' => ['action-links']]),
        'view' => views_embed_view('practitioners', 'admin'),
      ],
    ];
  
    return $build;
  }
  
  public static function patients() {
    
    $build[] = [
      '#theme' => 'counselling__page',
      '#key' => 'admin_patient',
      '#elements' => [
        'view' => views_embed_view('patients', 'admin'),
      ],
    ];
    
    return $build;
  }
  
  public static function settings() {
    $build = drupal_get_form('counselling_settings_form');
    
    return $build;
  }
}