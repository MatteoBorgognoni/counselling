<?php


namespace Drupal\counselling\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\CounsellingManager;
use Drupal\patient\Entity\Patient;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\counselling\Storage;
use Drupal\counselling\EmailManager;

class BereavementCounselling {
  
  public static function build() {
  
    $manager = new CounsellingManager();
    $system_is_enabled = $manager->getSystemStatus();
    
    if($system_is_enabled) {
      $breadcrumb = array(
        l(t('Home'), '<front>'),
        l(t('Advice & support '), '/'),
        l(t('Community'), '/community'),
        'Online bereavement counselling service',
      );
      drupal_set_breadcrumb($breadcrumb);
  
      $build = [];
  
  
      $build = [
        '#theme' => 'counselling__service_details',
        '#counsellors' => $manager->getRenderablePractitioners('counsellor', 'badge'),
        '#volunteers' => $manager->getRenderablePractitioners('volunteer','badge'),
      ];
  
      return $build;
    }
    else {
      return drupal_goto('community');
    }
  }
  
}