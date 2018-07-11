<?php

namespace Drupal\counselling;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\patient\Entity\Patient;
use Drupal\appointment\AppointmentManager;
use Drupal\counselling\QueryManager;

class CounsellingManager {
  
  protected $user;
  
  public function __construct($uid = NULL) {
    if (!is_null($uid)) {
      $this->user = user_load($uid);
    }
    else {
      $this->user = user_load($GLOBALS['user']->uid);
    }
  }
 
 
  /**
   * @param $type
   * Null to get all types
   * assessment or counselling to get desired type
   *
   *
   * @return array
   */
  public function getAvailableAppointments($type = NULL, $practitioner_id = NULL) {
    $queryManager = new QueryManager($this->user, FALSE);
    return $queryManager->getAvailableAppointments($type, $practitioner_id);
  }
  
  public function getAvailableAppoinmentsByPractitioner($id, $type = NULL) {
    $queryManager = new QueryManager($this->user, FALSE);
    return $queryManager->getAppointments([], $type, FALSE, NULL, FALSE, FALSE, $id);
  }
  
  public function getAppoinmentsByPatient(Patient $patient) {
    $queryManager = new QueryManager($patient->user());
    return $queryManager->getAllAppointments();
  }
  
  public function getAppoinmentsByPractitioner(Practitioner $practitioner) {
    $queryManager = new QueryManager($practitioner->user());
    return $queryManager->getAllAppointments();
  }
  
  public function getAllAppointments() {
    $queryManager = new QueryManager($this->user);
    return $queryManager->getAllAppointments();
  }
  
  /**
   * Return next booked appointment
   *
   * @param bool $booked
   * @return array|mixed
   *
   *
   */
  public function getNextAppointment($booked = TRUE) {
    $queryManager = new QueryManager($this->user);
    $next_appointment = $queryManager->getAppointments([0, 1], NULL, TRUE);
    
    return isset($next_appointment[0]) ? $next_appointment[0] : [];
  }
  
  /**
   * Return last attended appointment
   *
   * @param bool $booked
   * @return array|mixed
   *
   *
   */
  public function getLastAppointment() {
    $queryManager = new QueryManager($this->user);
    
    $last_appointment = $queryManager->getAppointments([0, 1], NULL, TRUE, TRUE, TRUE);
    return isset($last_appointment[0]) ? $last_appointment[0] : [];
  }
  
  public function getNextAppointmentTable($title = 'Next', array $range = []) {
    $table =  $this->getAppointmentTable('next', $range);
    return [
      '#theme' => 'counselling__appointment_table',
      '#title' => $title,
      '#table' => render($table),
      '#active' => TRUE,
    ];
  }
  
  public function getBookedAppointmentTable($title = 'Booked', array $range = []) {
    $table =  $this->getAppointmentTable('booked', $range);
    return [
      '#theme' => 'counselling__appointment_table',
      '#title' => $title,
      '#table' => render($table),
      '#active' => FALSE,
    ];
  }
  
  public function getScheduledAppointmentTable($title = 'Scheduled', array $range = []) {
    $table = $this->getAppointmentTable('scheduled', $range);
    return [
      '#theme' => 'counselling__appointment_table',
      '#title' => $title,
      '#table' => render($table),
      '#active' => FALSE,
    ];
  }
  
  public function getAssessmentAppointmentTable($title = 'Assessment', array $range = []) {
    $table = $this->getAppointmentTable('assessment', $range);
    return [
      '#theme' => 'counselling__appointment_table',
      '#title' => $title,
      '#table' => render($table),
      '#active' => FALSE,
    ];
  }
  
  public function getPastAppointmentTable($title = 'Past', array $range = []) {
    $table = $this->getAppointmentTable('past', $range);
    return [
      '#theme' => 'counselling__appointment_table',
      '#title' => $title,
      '#table' => render($table),
      '#active' => TRUE,
    ];
  }
  
  public function getArchivedAppointmentTable($title = 'Archive', array $range = []) {
    $table = $this->getAppointmentTable('archived', $range);
    return [
      '#theme' => 'counselling__appointment_table',
      '#title' => $title,
      '#table' => render($table),
      '#active' => FALSE,
    ];
  }
  
  /**
   *
   * Types = 'scheduled', 'next', 'booked', 'assessment', 'past', 'archived'
   *
   * @param string $type
   * @return array
   *
   */
  
  public function getAppointmentTable($type = 'available', array $range = []) {

    $queryManager = new QueryManager($this->user);
    
    $method = 'get' . ucfirst($type) . 'Rows';
    
    $table = [];
    $id = 'appointment-' . $type;
    $table['table'] = [
      '#theme' => 'table',
      '#header' => $queryManager->getHeaders($type),
      '#rows' =>  $queryManager->{$method}($range),
      '#empty' => t($queryManager->getEmptyMessage($type)),
      '#attributes' => ['id' => $id],
      '#prefix' => '<div id="' . $id .'-wrapper">',
      '#suffix' => '</div>',
    ];
    
    $table['table']['#attached']['js'][] = [
      'data' => ['appointmentTables' => [
        $type => '#' . $id,
      ]],
      'type' => 'setting',
    ];
  
    $table['pager'] = ['#theme' => 'pager', ];
    return $table;
  }
  
  public function getPractitioners($type = NULL, $active = TRUE) {
    $query = db_select('practitioner', 'p')
      ->fields('p', ['id'])
      ->condition('status', 1, '=');
    
    if(!is_null($type)) {
      $query->condition('type', $type, '=');
    }
    
    if($active) {
      $query->condition('status', 1);
    }
    
    $results = $query->execute()->fetchAll();
    
    $practitioners = [];
    
    foreach ($results as $result) {
      $practitioners[$result->id] = practitioner_load($result->id);
    }
    
    return $practitioners;
  }
  
  public function getAvailablePractitioners($practitioner_type = NULL, $appointment_type = NULL) {
    $available_practitioners = [];
    $practitioners = $this->getPractitioners($practitioner_type);
    foreach ($practitioners as $id => $practitioner) {
      $appointments = $this->getAvailableAppointments($appointment_type, $practitioner->id);
      if($appointments) {
        $available_practitioners[$id] = $practitioner;
      }
    }
    return $available_practitioners;
  }
  
  public function getCounsellors() {
    return $this->getPractitioners('counsellor');
  }
  
  public function getVolunteers() {
    return $this->getPractitioners('volunteer');
  }
  
  public function getRenderablePractitioner(Practitioner $practitioner, $view_mode = 'default') {
    $practitioner_renderable = practitioner_page_view($practitioner, $view_mode);
    return $practitioner_renderable['practitioner'][$practitioner->identifier()];
  }
  
  public function getRenderablePractitioners($type = NULL, $view_mode = 'default') {
    $practitioners = [];
    $objects =  $this->getPractitioners($type);
    foreach ($objects as $practitioner) {
      $practitioners += practitioner_page_view($practitioner, $view_mode)['practitioner'];
    }
    return $practitioners;
  }
  
  
  public function userIsPatient() {
    return isset($this->user->patient);
  }
  
  
  public function userIsPractitioner() {
    return isset($this->user->practitioner);
  }
  
  public function getMailSettings($key) {
    return variable_get('counselling_settings_mail__' . $key);
  }
  
  public function getReminderAppointments($type, $time) {
    $queryManager = new QueryManager($this->user, FALSE);
    $appointment_ids = $queryManager->getReminderAppointments($type, $time);
    return appointment_load_multiple($appointment_ids);
  }
  
  public function getSystemStatus() {
    $system_settings = variable_get('counselling_settings_system');
    
    if($system_settings && $system_settings['status']) {
      return (bool) $system_settings['status'];
    }
    else  {
      return FALSE;
    }
  }
  
}