<?php

namespace Drupal\counselling;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\patient\Entity\Patient;
use Drupal\counselling\CounsellingManager;

class EmailManager {
  
  protected $user;
  
  protected $counsellingManager;

  public function __construct() {
    $this->user = user_load($GLOBALS['user']->uid);
    $this->counsellingManager = new CounsellingManager();
  }
  
  public function bookingManager(Appointment $appointment) {
    if($appointment->type == 'assessment') {
      return $this->sendMail('assessment_manager', ['appointment' => $appointment]);
    }
    return $this->sendMail('booking_manager', ['appointment' => $appointment]);
  }
  
  public function bookingPractitioner(Appointment $appointment) {
    if($appointment->type == 'assessment') {
      return $this->sendMail('assessment_practitioner', ['appointment' => $appointment]);
    }
    return $this->sendMail('booking_practitioner', ['appointment' => $appointment]);
  }
  
  public function bookingPatient(Appointment $appointment) {
    if($appointment->type == 'assessment') {
      return $this->sendMail('assessment_patient', ['appointment' => $appointment]);
    }
    return $this->sendMail('booking_patient', ['appointment' => $appointment]);
  }
  
  public function cancelled(Appointment $appointment) {
    return $this->sendMail('canceled', ['appointment' => $appointment]);
  }
  
  public function cancelledByPractitioner(Appointment $appointment) {
    return $this->sendMail('canceled_by_practitioner', ['appointment' => $appointment]);
  }
  
  public function cancelledByPatient(Appointment $appointment) {
    return $this->sendMail('canceled_by_patient', ['appointment' => $appointment]);
  }
  
  public function feedbackRequest(Appointment $appointment) {
    /** @var Patient $patient */
    $patient = $appointment->patient();
    $type = $patient->type();
    if (!$type) {
      $type = 'counsellor';
    }
    return $this->sendMail('feedback_request_' . $type, ['appointment' => $appointment]);
  }

  public function supportApproved(Patient $patient) {
    $type = $patient->type();
    return $this->sendMail('support_approved_' . $type, ['patient' => $patient]);
  }
  
  public function sendReminder($type, Appointment $appointment) {
    $key = 'reminder_' . $type;
    return $this->sendMail($key, ['appointment' => $appointment]);
  }
  
  protected function sendMail($key, array $data) {
    // Prepare mail
    $settings = $this->counsellingManager->getMailSettings($key);
    $language = language_default();
  
    $addressee_type = $settings['addressee_type'];
    $addressees = [];
    switch ($addressee_type) {
      case 'single':
        $addressees[] = token_replace($settings['to'], $data);
        break;
      case 'roles':
        $roles = $settings['roles'];
        $addressees = $this->getAddresseesByRolenames($roles);
        break;
    }
    
    $subject = token_replace($settings['subject'], $data);
    $body = token_replace($settings['message']['value'], $data);
    
    $from = token_replace($settings['from'], $data);
    
    $params = [
      'subject' => $subject,
      'body' => $body,
    ];
    // Send Mail
    $results = [];
    foreach ($addressees as $id => $to) {
      $results[$to] = drupal_mail('counselling', $key, $to, $language, $params, $from);
      watchdog('counselling', 'Email "%key" sent to %address', ['%key' => $key, '%address' => $to], WATCHDOG_NOTICE, NULL);
    }
    return $results;
  }
  
  public function simpleTest() {
    $key = 'test';
    $to = 'test@test.test';
    $language = language_default();
    $params = [
      'subject' => 'Simple test',
      'body' => 'This is a simple test',
    ];
    $from = 'test@test.test';
    drupal_mail('counselling', $key, $to, $language, $params, $from);
    watchdog('counselling', 'Email "%key" sent to %address', ['%key' => $key, '%address' => $to], WATCHDOG_NOTICE, NULL);
  }
  
  public function test($values) {
    $key = 'test';

    $language = language_default();
    
    $body = $values['message']['value'];
   
    $from = $values['from'];
    
    $params = [
      'subject' => $values['subject'],
      'body' => $body,
    ];
  
    $addressee_type = $values['addressee_type'];
    $addressees = [];
    switch ($addressee_type) {
      case 'single':
        $addressees[] = $values['to'];
        break;
      case 'roles':
        $roles = $values['roles'];
        $addressees = $this->getAddresseesByRolenames($roles);
        break;
    }
    $results = [];
    foreach ($addressees as $id => $to) {
      $results[$to] = drupal_mail('counselling', $key, $to, $language, $params, $from);
      watchdog('counselling', 'Email "%key" sent to %address', ['%key' => $key, '%address' => $to], WATCHDOG_NOTICE, NULL);
    }
    
    return $results;
  }
  
  public function getAddresseesByRolenames($roles) {
    $addressees = [];
    
    foreach (array_filter($roles) as $role_name) {
      $role = user_role_load_by_name($role_name);
      if(isset($role->rid)) {
        $results = db_select('users_roles', 'ur')
          ->fields('ur', ['uid'])
          ->condition('ur.rid', $role->rid, '=')
          ->execute()
          ->fetchAll();
        foreach ($results as $result) {
          $addressees[$result->uid] = user_load($result->uid)->mail;
        }
      }
    }
    
    return $addressees;
  }
  
  public function getAddresseesByRids($rids) {
    $addressees = [];
    
    foreach ($rids as $rid) {
      if($rid) {
        $results = db_select('users_roles', 'ur')
          ->fields('ur', ['uid'])
          ->condition('ur.rid', $rid, '=')
          ->execute()
          ->fetchAll();
        foreach ($results as $result) {
          $addressees[$result->uid] = user_load($result->uid)->mail;
        }
      }
    }

    return $addressees;
  }
  
}