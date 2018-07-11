<?php
/**
 * Created by PhpStorm.
 * User: ares
 * Date: 27/01/18
 * Time: 17:45
 */

namespace Drupal\appointment;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\patient\Entity\Patient;

class AppointmentManager {
  
  /** @var \Drupal\appointment\Entity\Appointment $appointment */
  protected $appointment;
  
  /**
   * AppointmentManager constructor.
   * @param \Drupal\appointment\Entity\Appointment $appointment
   */
  public function __construct(Appointment $appointment) {
    $this->appointment = $appointment;
  }
  
  /**
   * @return \Drupal\appointment\Entity\Appointment
   */
  public function getAppointment() {
    return $this->appointment;
  }
  
  public function getDate($format = 'd/m/Y - H:i') {
    return date($format, $this->appointment->date);
  }
  
  public function getAllPractitioners() {
    $query = db_select('practitioner', 'p');
    $query->fields('p', ['id']);
    $query->condition('status', 1, '=');
  
    $ids = $query->execute()->fetchAllKeyed(0,0);
    return practitioner_load_multiple($ids);
  }
  
  
  public function getAllPatients($assessed = NULL) {
    $query = db_select('patient', 'p');
    $query->fields('p', ['id']);
    $query->condition('status', 1, '=');
    
    if(!is_null($assessed)) {
      if($assessed) {
        $query->isNotNull('type');
      }
      else {
        $query->isNull('type');
      }
    }
    
    $ids = $query->execute()->fetchAllKeyed(0,0);
    return patient_load_multiple($ids);
  }
  
  public function getPractitionerOptions() {
    $options = [];
    $practitioners = $this->getAllPractitioners();
    foreach ($practitioners as $id => $practitioner) {
      $options[$id] = $practitioner->fullname();
    }
    return $options;
  }
  
  public function getPatientOptions($appointment_type = NULL) {
    $options = [];
    
    switch ($appointment_type) {
      case 'assessment':
        $assessed = FALSE;
        break;
      case 'counselling':
        $assessed = TRUE;
        break;
      default:
        $assessed = NULL;
        break;
    }
    
    $patients = $this->getAllPatients($assessed);
  
    if($appointment_type == 'counselling') {
      $practitioner_type = $this->appointment->practitioner()->type;
      foreach ($patients as $id => $patient) {
        if($practitioner_type == $patient->type)
          $options[$id] = $patient->fullname();
      }
    }
    else {
      foreach ($patients as $id => $patient) {
        $options[$id] = $patient->fullname();
      }
    }
    return $options;

  }
  
  public function patientWantsReminders() {
    $patient = $this->appointment->patient();
    return (bool) $patient->reminder_email;
  }
  
  public function duplicate() {
    $clone_appointment = clone $this->appointment;
    $clone_appointment->patient = NULL;
    $clone_appointment->attended = NULL;
    $clone_appointment->state = APPOINTMENT_STATE_SCHEDULED;
    $clone_appointment->id = '';
    $clone_appointment->is_new = TRUE;
    $clone_appointment->uid = $this->appointment->practitioner()->uid;
    $clone_appointment->feedback = NULL;
    $clone_appointment->score = NULL;
    $clone_appointment->created = NULL;
    $clone_appointment->changed = NULL;
    $clone_appointment->save();
  }
  
  /**
   * @param string $type
   *
   * patient or practitioner
   *
   * @return bool
   */
  public function appointmentOverlaps($type, $id) {
    
    $start = $this->appointment->calculateDateTime('start');
    $end = $this->appointment->calculateDateTime('end');
    
    $query = db_select('appointment', 'a');
    
    switch ($type) {
      case 'practitioner':
        $query->condition('a.practitioner', $id);
        break;
      case 'patient':
        $query->condition('a.patient', $id);
        break;
    }
    
    
    $and = db_and();
    $and->condition('date', $end, '<');
    $and->condition('date_end', $start, '>');
    $query->condition($and);
    
    if (!empty($this->appointment->id)) {
      $query->condition('id', $this->appointment->id, '!=');
    }
    
    $query->condition('a.state', [1,2], 'IN');
    $query->fields('a', ['id']);
    
    $result = $query->execute()->fetchCol();
    
    if(!empty($result)) {
      return TRUE;
    }
    
    return FALSE;
  }
  
  
}