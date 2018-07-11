<?php

namespace Drupal\counselling;

use Drupal\appointment\Entity\Appointment;
use Drupal\practitioner\Entity\Practitioner;
use Drupal\patient\Entity\Patient;
use Drupal\appointment\AppointmentManager;
use SelectQuery;

class QueryManager {
  
  protected $user;
  
  protected $query;
  
  protected $counsellingManager;
  
  protected $actionsAllowed;
  
  public function __construct($user, $user_constraint = TRUE) {
    $this->user = $user;
    $this->query = $this->getBaseQuery($user_constraint);
    
    global $user;
    $this->actionsAllowed = $user->uid == $this->user->uid;
  }
  
  public function getBaseQuery($user_constraint = TRUE) {
    $query = db_select('appointment', 'a')->orderBy('date', 'ASC');
    $query->leftjoin('practitioner', 'pr', 'a.practitioner = pr.id');
    $query->leftjoin('patient', 'pt', 'a.patient = pt.id');
    $query->leftjoin('users', 'u', 'a.uid = u.uid');
    
    if($user_constraint) {
      if ($this->userIsPractitioner()) {
        $practitioner_id = $this->user->practitioner->identifier();
        $query->condition('a.practitioner', $practitioner_id, '=');
      }
  
      if ($this->userIsPatient()) {
        $patient_id = $this->user->patient->identifier();
        $query->condition('a.patient', $patient_id, '=');
      }
    }
    
    return $query;
  }
  
  /**
   * Return the appointments for the current user
   *
   * @param array $range
   * @param null $type
   * @param bool $booked
   * @param bool $attended
   * @param bool $past
   * @return array
   */
  public function getAppointments(array $range = [], $type = NULL, $booked = FALSE, $attended = NULL, $past = FALSE, $canceled = FALSE, $practitioner = FALSE, $patient = FALSE) {
    $appointments = [];
    
    $this->query->fields('a', ['id']);
    
    if($range) {
      $this->query->range($range[0], $range[1]);
    }
    
    if($booked) {
      $this->query->isNotNull('a.patient');
    }
    else {
      $this->query->isNull('a.patient');
    }
    
    if(is_null($attended)) {
      $this->query->isNull('a.attended');
    }
    else {
      if($attended) {
        $this->query->condition('a.attended', 1, '=');
      }
      else {
        $this->query->condition('a.attended', 0, '=');
      }
    }
    
    if(!$past) {
      $now = REQUEST_TIME;
      $this->query->condition('a.date', $now, '>');
    }
    else {
      $now = REQUEST_TIME;
      $this->query->condition('a.date', $now, '<');
    }
  
    $states = [
      APPOINTMENT_STATE_CANCELED_PRACTITIONER,
      APPOINTMENT_STATE_CANCELED_PATIENT,
      APPOINTMENT_STATE_CANCELED_ADMIN,
      APPOINTMENT_STATE_CHANGED
    ];
    
    if(!$canceled) {
      $this->query->condition('a.state', $states, 'NOT IN');
    }
    else {
      $this->query->condition('a.state', $states, 'IN');
    }
    
    if(!is_null($type)) {
      $this->query->condition('a.type', $type, '=');
    }
    
    if($practitioner) {
      $this->query->condition('a.practitioner', $practitioner, '=');
    }
    
    if($patient) {
      $this->query->condition('a.patient', $patient, '=');
    }
    
    $results = $this->query->execute()->fetchAll();
    
    foreach ($results as $result) {
      $appointments[] = appointment_load($result->id);
    }
    return $appointments;
  }
  
  public function getAllAppointments() {
    $appointments = [];
    $this->query->fields('a', ['id']);
  
    $results = $this->query->execute()->fetchAll();
  
    foreach ($results as $result) {
      $appointments[] = appointment_load($result->id);
    }
    return $appointments;
  }
  
  public function getAvailableAppointments($type, $practitioner_id = NULL) {
    $appointments = [];
    $this->query->fields('a', ['id']);
    $this->query->isNull('a.patient');
  
    $now = REQUEST_TIME;
    $this->query->condition('a.date', $now, '>');
    $this->query->condition('a.type', '%' . $type . '%','LIKE');
    
    if(!is_null($practitioner_id)) {
      $this->query->condition('practitioner', $practitioner_id, '=');
    }
    
    $results = $this->query->execute()->fetchAll();
    
    foreach ($results as $result) {
      $appointments[] = appointment_load($result->id);
    }
    return $appointments;
  }
  
  
  public function getHeaders($type) {
    
    $headers = [];
    
    switch ($type) {
      case 'scheduled':
        $headers['date'] = ['data' => t('Date'),'field' => 'date'];
        $headers['type'] = ['data' => t('Type')];
        if ($this->actionsAllowed && $this->userIsPractitioner()) {
          $headers['actions'] = ['data' => t('Actions')];
        }
        break;
      case 'next':
        $headers['date'] = ['data' => t('Date'),'field' => 'date'];
        $headers['state'] = ['data' => t('State')];
        if ($this->userIsPractitioner()) {
          $headers['type'] = ['data' => t('Type')];
          $headers['patient'] = ['data' => t('Patient')];
        }
        if ($this->userIsPatient()) {
          $headers['practitioner'] = ['data' => t('Counsellor')];
        }
        if($this->actionsAllowed) {
          $headers['actions'] = ['data' => t('Actions')];
        }
        
        break;
      case 'booked':
        $headers['date'] = ['data' => t('Date'),'field' => 'date'];
        $headers['type'] = ['data' => t('Type')];
        $headers['patient'] = ['data' => t('Patient')];
        $headers['actions'] = ['data' => t('Actions')];
        break;
      case 'past':
        $headers['date'] = ['data' => t('Date'),'field' => 'date'];
        $headers['state'] = ['data' => t('State')];
        if ($this->userIsPractitioner()) {
          $headers['type'] = ['data' => t('Type')];
          $headers['patient'] = ['data' => t('Patient')];
        }
        if ($this->userIsPatient()) {
          $headers['practitioner'] = ['data' => t('Counsellor')];
        }
        if($this->actionsAllowed) {
          $headers['actions'] = ['data' => t('Actions')];
        }

        break;
      case 'archived':
        $headers['date'] = ['data' => t('Date'),'field' => 'date'];
        $headers['state'] = ['data' => t('State')];
        $headers['type'] = ['data' => t('Type')];
        if ($this->userIsPractitioner()) {
          $headers['patient'] = ['data' => t('Patient')];
        }
        if ($this->userIsPatient()) {
          $headers['practitioner'] = ['data' => t('Counsellor')];
        }
        break;
    }
    
    return $headers;
  }
  
  public function getEmptyMessage($type) {
    $empty_message = '';
  
    switch ($type) {
      case 'scheduled':
        $empty_message = 'There are no scheduled appointments';
        break;
      case 'next':
        $empty_message = 'There are no booked appointments';
        break;
      case 'booked':
        $empty_message = 'There are no booked appointments';
        break;
      case 'past':
        $empty_message = 'There are no past appointments';
        break;
      case 'archived':
        $empty_message = 'There are no archived appointments';
        break;
    }
  
    return $empty_message;
  }
  
  public function getScheduledRows($range = []) {
    $rows = [];
    $now = REQUEST_TIME;
    
    if($range) {
      $this->query->range($range[0], $range[1]);
    }
  
    $this->query->isNull('a.patient');
    $this->query->condition('a.date', $now, '>');
    $this->query->fields('a', ['id', 'type', 'date', 'date_end']);
    
    /** @var \DatabaseStatementBase $results */
    $results = $this->processTable('scheduled',10);
  
    if(empty($results)) {
      return [];
    }
  
    // Looping for filling the table rows
    foreach ($results as $result) {
      $rows[$result->id]['class'] = ['appointment-row appointment-row--scheduled appointment-' . $result->id];
      $rows[$result->id]['data'] = [];
      $rows[$result->id]['data']['date'] = [
        'data' => appointment_display_date($result->date, $result->date_end),
        'class' => 'date'
      ];
      $rows[$result->id]['data']['type'] = [
        'data' => implode(', ', drupal_map_assoc(explode('|', $result->type), 'ucfirst')),
        'class' => 'type'
      ];
      
      $actions = [];
      $actions['edit'] = l('Edit', 'appointment/' . $result->id . '/edit');
      if ($this->actionsAllowed && isset($this->user->practitioner)) {
        $rows[$result->id]['data']['actions'] = [
          'data' => theme('item_list', ['items' => $actions]),
          'class' => 'actions'
        ];
      }
      
    }
    
    return $rows;
  }

  public function getNextRows($range = []) {
    $rows = [];
    
    if($range) {
      $this->query->range($range[0], $range[1]);
    }
    
    $now = REQUEST_TIME;
    
    $this->query->isNotNull('a.patient');
    $this->query->isNull('a.attended');
    $this->query->condition('a.state', [APPOINTMENT_STATE_BOOKED], 'IN');
    $this->query->condition('a.date_end', $now, '>');
    $this->query->fields('a', ['id', 'date', 'date_end', 'type', 'state', 'patient', 'practitioner']);
    $this->query->fields('pt', ['first_name', 'last_name']);
    $this->query->fields('pr', ['first_name', 'last_name', 'room']);
    
    $results = $this->processTable('booked',10);
    if(empty($results)) {
      return [];
    }
    
    // Looping for filling the table rows
    foreach ($results as $result) {
      $rows[$result->id]['class'] = ['appointment-row appointment-row--next appointment-' . $result->id];
      $rows[$result->id]['data'] = [];
  
      $rows[$result->id]['data']['date'] = [
        'data' => appointment_display_date($result->date, $result->date_end),
        'class' => 'date'
      ];
      $rows[$result->id]['data']['state'] = [
        'data' => ucfirst(appointment_get_states('short')[$result->state]),
        'class' => 'state'
      ];
      if($this->userIsPractitioner()) {
        $rows[$result->id]['data']['type'] = [
          'data' => ucfirst($result->type),
          'class' => 'type'
        ];
        $rows[$result->id]['data']['patient'] = [
          'data' => l($result->first_name . ' ' . $result->last_name, '/bereavement-counselling/patient/' . $result->patient),
          'class' => 'patient'
        ];
      }
      if($this->userIsPatient()) {
        $rows[$result->id]['data']['practitioner'] = [
          'data' => l($result->pr_first_name . ' ' . $result->pr_last_name, '/bereavement-counselling/practitioner/' . $result->practitioner),
          'class' => 'practitioner'
        ];
      }

      if($this->actionsAllowed) {
        $actions = [];
        if($this->userIsPatient()) {
          if ($result->date - $now < 300) {
            $actions['attend'] = l('Start', 'appointment/' . $result->id . '/room', ['attributes' => ['target' => '_blank']]);
          }
          else {
            $actions['attend'] = l('Start', '#', ['attributes' => ['class' => ['disabled'], 'onclick' => 'return false;', 'title' => 'Room not yet available']]);
          }
          $actions['change'] = l('Change', 'appointment/' . $result->id . '/change');
        }
        $actions['cancel'] = l('Cancel', 'appointment/' . $result->id . '/cancel');
  
  
        $rows[$result->id]['data']['actions'] = [
          'data' => theme('item_list', ['items' => $actions]),
          'class' => 'actions'
        ];
      }

      
    }
    return $rows;
  }


  public function getBookedRows($range = []) {
    $rows = [];
  
    if($range) {
      $this->query->range($range[0], $range[1]);
    }
  
    $now = REQUEST_TIME;
    
    $this->query->isNotNull('a.patient');
    $this->query->condition('a.state', APPOINTMENT_STATE_BOOKED, '=');
    $this->query->condition('a.date', $now, '>');
    $this->query->fields('a', ['id', 'date', 'date_end', 'type', 'state', 'patient']);
    $this->query->fields('pt', ['first_name', 'last_name']);
    $this->query->fields('pr', ['first_name', 'last_name']);
  
    $results = $this->processTable('booked',10);
  
    if(empty($results)) {
      return [];
    }
  
    // Looping for filling the table rows
    foreach ($results as $result) {
      $rows[$result->id]['class'] = ['appointment-row appointment-row--booked appointment-' . $result->id];
      $rows[$result->id]['data'] = [];
      
      $rows[$result->id]['data']['date'] = [
        'data' => appointment_display_date($result->date, $result->date_end),
        'class' => 'date'
      ];
      $rows[$result->id]['data']['type'] = [
        'data' => implode(', ', drupal_map_assoc(explode('|', $result->type), 'ucfirst')),
        'class' => 'type'
      ];
      $rows[$result->id]['data']['patient'] = [
        'data' => l($result->first_name . ' ' . $result->last_name, '/bereavement-counselling/patient/' . $result->patient),
        'class' => 'patient'
      ];
      if($this->actionsAllowed) {
        $actions = [];
        $actions['cancel'] = l('Cancel', 'appointment/' . $result->id . '/cancel');
        $rows[$result->id]['data']['actions'] = [
          'data' => theme('item_list', ['items' => $actions]),
          'class' => 'actions'
        ];
      }
    }
  
    return $rows;
  }

  
  public function getPastRows($range = []) {
    $rows = [];
    
    if($range) {
      $this->query->range($range[0], $range[1]);
    }
    
    $now = REQUEST_TIME;
    $this->query->isNotNull('a.patient');
    $this->query->condition('a.date_end', $now, '<');
    $this->query->isNull('score');
  
  
    $states = [
      APPOINTMENT_STATE_CANCELED_PRACTITIONER,
      APPOINTMENT_STATE_CANCELED_PATIENT,
      APPOINTMENT_STATE_CANCELED_ADMIN,
      APPOINTMENT_STATE_CHANGED,
      APPOINTMENT_STATE_NOT_ATTENDED,
    ];
    
    if($this->userIsPractitioner()) {
      $db_or = db_or();
      $db_or->condition('a.state', [$states], 'NOT IN');
      $db_and = db_and();
      $db_and->condition('a.state', APPOINTMENT_STATE_ATTENDED, '=');
      $db_and->isNull('a.score');
      $db_or->condition($db_and);
      $this->query->condition($db_or);
    }
  
    if($this->userIsPatient()) {
      $this->query->condition('a.state', [$states], 'NOT IN');
    }

    $this->query->fields('a', ['id', 'date', 'date_end', 'type', 'attended', 'state', 'patient', 'practitioner']);
    $this->query->fields('pt', ['first_name', 'last_name']);
    $this->query->fields('pr', ['first_name', 'last_name']);
    
    $results = $this->processTable('past',10);
    
    if(empty($results)) {
      return [];
    }
    
    // Looping for filling the table rows
    foreach ($results as $result) {
      $rows[$result->id]['class'] = ['appointment-row appointment-row--past appointment-' . $result->id];
      $rows[$result->id]['data'] = [];
      $rows[$result->id]['data']['date'] = [
        'data' => appointment_display_date($result->date, $result->date_end),
        'class' => 'date'
      ];
      $rows[$result->id]['data']['state'] = [
        'data' => ucfirst(appointment_get_states('short')[$result->state]),
        'class' => 'state'
      ];
      if($this->userIsPractitioner()) {
        $rows[$result->id]['data']['type'] = [
          'data' => implode(', ', drupal_map_assoc(explode('|', $result->type), 'ucfirst')),
          'class' => 'type'
        ];
        $rows[$result->id]['data']['patient'] = [
          'data' => l($result->first_name . ' ' . $result->last_name, '/bereavement-counselling/patient/' . $result->patient),
          'class' => 'patient'
        ];
      }
      if($this->userIsPatient()) {
        $rows[$result->id]['data']['practitioner'] = [
          'data' => l($result->pr_first_name . ' ' . $result->pr_last_name, '/bereavement-counselling/practitioner/' . $result->practitioner),
          'class' => 'practitioner'
        ];
      }
  
      if($this->actionsAllowed) {
        $actions = [];
        if($this->userIsPractitioner()) {
          if($result->state == APPOINTMENT_STATE_ATTENDED) {
            $actions['update'] = l('Waiting feedback', '#', ['attributes' => ['class' => ['disabled'], 'onclick' => 'return false;']]);
          }
          elseif (in_array($result->state, [APPOINTMENT_STATE_CANCELED_PRACTITIONER, APPOINTMENT_STATE_CANCELED_PATIENT])) {
            $actions['update'] = '';
          }
          else {
            $actions['update'] = l('Update', 'appointment/' . $result->id . '/update');
          }
        }
        if($this->userIsPatient()) {
          if ($result->attended) {
            $actions['feedback'] = l('Feedback', 'appointment/' . $result->id . '/feedback');
          }
          else {
            $actions['feedback'] = l('Feedback', '#', ['attributes' => ['class' => ['disabled'], 'onclick' => 'return false;']]);
          }
        }
  
        $rows[$result->id]['data']['actions'] = [
          'data' => theme('item_list', ['items' => $actions]),
          'class' => 'actions'
        ];
      }

    }
    
    return $rows;
  }
  
  
  public function getArchivedRows($range = []) {
    $rows = [];
  
    if($range) {
      $this->query->range($range[0], $range[1]);
    }
  
    $now = REQUEST_TIME;
  
    $states = [
      APPOINTMENT_STATE_CANCELED_PRACTITIONER,
      APPOINTMENT_STATE_CANCELED_PATIENT,
      APPOINTMENT_STATE_CANCELED_ADMIN,
      APPOINTMENT_STATE_CHANGED,
      APPOINTMENT_STATE_NOT_ATTENDED,
    ];
  
    $db_or = db_or();
    $db_and = db_and();
    $db_and_2 = db_and();
    // First or condition: Appointment is canceled or changed or patient did not attend
    $db_or->condition('a.state', $states, 'IN');
    // Second or condition: Appointment is attended AND feedback is not null
    $db_and->condition('a.state', APPOINTMENT_STATE_ATTENDED, '=');
    $db_and->isNotNull('a.score');
    $db_or->condition($db_and);
    // Third or condition: Appointment is scheduled or booked but date is past
    $db_and_2->condition('a.state', [APPOINTMENT_STATE_SCHEDULED], 'IN');
    $db_and_2->condition('a.date', $now, '<');
    $db_or->condition($db_and_2);
    
    $this->query->condition($db_or);
  
    $this->query->fields('a', ['id', 'date', 'date_end', 'type', 'state', 'patient', 'practitioner']);
    $this->query->fields('pt', ['first_name', 'last_name']);
    $this->query->fields('pr', ['first_name', 'last_name']);
    
    $results = $this->processTable('past',10);
    
    if(empty($results)) {
      return [];
    }
  
    // Looping for filling the table rows
    foreach ($results as $result) {
      $rows[$result->id]['class'] = ['appointment-row appointment-row--archived appointment-' . $result->id];
      $rows[$result->id]['data'] = [];
      $rows[$result->id]['data']['date'] = [
        'data' => appointment_display_date($result->date, $result->date_end),
        'class' => 'date'
      ];
      
      $rows[$result->id]['data']['state'] = [
        'data' => ucfirst(appointment_get_states('short')[$result->state]),
        'class' => 'state'
      ];
      
      $rows[$result->id]['data']['type'] = [
        'data' => implode(', ', drupal_map_assoc(explode('|', $result->type), 'ucfirst')),
        'class' => 'appointment-type'
      ];
      
      if($this->userIsPractitioner()) {
        $rows[$result->id]['data']['patient'] = [
          'data' => l($result->first_name . ' ' . $result->last_name, '/bereavement-counselling/patient/' . $result->patient),
          'class' => 'patient'
        ];
      }
      if($this->userIsPatient()) {
        $rows[$result->id]['data']['patient'] = [
          'data' => l($result->pr_first_name . ' ' . $result->pr_last_name, '/bereavement-counselling/practitioner/' . $result->practitioner),
          'class' => 'practitioner'
        ];
      }
    }
  
    return $rows;
  }
  
  
  protected function processTable($type, $limit = 10) {
    $table_sort = $this->query->extend('TableSort') // Add table sort extender.
    ->orderByHeader($this->getHeaders($type)); // Add order by headers.
  
    $pager = $table_sort->extend('PagerDefault')->element(0)
      ->limit($limit); // 10 rows per page.
    return $pager->execute();
  }
  
  
  public function getReminderAppointments($type, $time) {
    $comparison = $time + 86400;
    switch ($type) {
      case 'hour':
        $comparison = $time + 3600;
        break;
      case 'day':
        $comparison = $time + 86400;
        break;
    }
    
    $this->query->fields('a', ['id']);
    $this->query->isNotNull('patient');
    $this->query->condition('state', APPOINTMENT_STATE_BOOKED, '=');
    $this->query->condition('a.date', $comparison, '=');

    return $this->query->execute()->fetchCol();
  }
  
  protected function userIsPatient() {
    return isset($this->user->patient);
  }
  
  protected function userIsPractitioner() {
    return isset($this->user->practitioner);
  }
  
  protected function debug() {
    if (module_exists('devel')) {
      dsm([$this->query->__toString(), $this->query->arguments()]);
    }
    else {
      echo '<pre>'; print_r($this->query->__toString()); print_r($this->query->arguments()); echo '</pre>';
    }
  }
  
}
