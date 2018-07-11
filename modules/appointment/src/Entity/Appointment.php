<?php

/**
 * @file
 * Appointment class.
 */

namespace Drupal\appointment\Entity;

use Entity;

/**
 * The class used for appointment entities.
 */
class Appointment extends Entity {
  /**
   * Class constructor.
   */
  public function __construct($values = array()) {
    parent::__construct($values, 'appointment');
  }

  /**
   * Defines the default entity label.
   */
  protected function defaultLabel() {
    $date =  date('Ymd', $this->date);
    $time = date('g.ia', strtotime($this->time_start));
    $id = $this->identifier();
    return 'appointment-' . $date . '-' . $time;
  }

  /**
   * Defines the default entity URI.
   */
  protected function defaultUri() {
    return array('path' => 'admin/content/appointment/' . $this->identifier());
  }
  

  public function type() {
    $types = explode('|', $this->type);
    return implode(', ', $types);
  }
  
  public function getTypeValue() {
    $types = explode('|', $this->type);
    foreach ($types as $type) {
      if($type) {
        $value[$type] = $type;
      }
    }
    return $value;
  }
  
  public function setType($types) {
    foreach ($types as $type) {
      if($type) {
        $value[$type] = $type;
      }
    }
    $this->type = implode('|', $value);
  }
  
  public function setTypeValue($value) {
    $this->type = $value;
  }
  
  public function date() {
    return date('d/m/Y', $this->date);
  }
  
  public function dateEnd() {
    return date('d/m/Y', $this->date_end);
  }
  
  public function user() {
    return user_load($this->uid);
  }
  
  public function patient() {
    if(!is_null($this->patient)) {
      return patient_load($this->patient);
    }
    else {
      return NULL;
    }
  }
  
  public function practitioner() {
    if(!is_null($this->practitioner)) {
      return practitioner_load($this->practitioner);
    }
    else {
      return NULL;
    }
  }
  
  public function room() {
    return $this->practitioner()->room;
  }
  
  // This is called by the setter callback.
  public function setUser($account) {
    $this->uid = is_object($account) ? $account->uid : $account;
  }
  
  public function score() {
    return $this->score;
  }
  
  public function setScore($score) {
    $this->score = $score;
  }
  
  public function endDate() {
    return strtotime($this->formatDate('Y-m-d') . ' ' . $this->time_end);
  }
  
  public function formatDate($format = 'd/m/Y') {
    return date($format, $this->date);
  }
  
  public function formatDateEnd($format = 'd/m/Y') {
    return date($format, $this->date_end);
  }
  
  public function calculateDateTime($type, $timestamp = TRUE, $format = 'd/m/y H:i') {
    $time = 'time_' . $type;
    $date = date('Y-m-d', $this->date) . ' ' . $this->{$time};
    if($timestamp) {
      return strtotime($date);
    }
    return date($format, strtotime($date));
  }
  
  public function formatTime($format = 'H:i', $start = TRUE, $end = TRUE) {
    
    $time = [];
    
    if ($start) {
      $time['start'] = date($format, strtotime($this->time_start));
    }
  
    if ($end) {
      $time['end'] = date($format, strtotime($this->time_end));
    }
    
    return implode('-', $time);
  }
  
  public function state() {
    return ucfirst(appointment_get_states()[$this->state]);
  }
  
  public function adminCancelUrl() {
    $state = (int) $this->state;
    if($state == 2) {
      return '/admin/content/appointment/' . $this->identifier() . '/cancel';
    }
    else {
      return '';
    }
  }
  
  public function adminAssignUrl() {
    $state = (int) $this->state;
    if($state == 1) {
      return '/admin/content/appointment/' . $this->identifier() . '/assign';
    }
    else {
      return '';
    }
  }
  
}
