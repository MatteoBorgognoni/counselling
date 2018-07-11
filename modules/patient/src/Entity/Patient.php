<?php

/**
 * @file
 * Patient class.
 */

namespace Drupal\patient\Entity;

use Entity;

/**
 * The class used for patient entities.
 */
class Patient extends Entity {
  /**
   * Class constructor.
   */
  public function __construct($values = array()) {
    parent::__construct($values, 'patient');
  }

  /**
   * Defines the default entity label.
   */
  protected function defaultLabel() {
    return $this->fullname();
  }

  /**
   * Defines the default entity URI.
   */
  protected function defaultUri() {
    global $user;
    return array('path' => 'admin/content/patient/' . $this->identifier());
  }
  
  public function getFirstName() {
    return ucwords(trim($this->first_name));
  }
  
  public function getLastName() {
    return ucwords(trim($this->last_name));
  }
  
  public function fullname() {
    return $this->getFirstName() . ' ' . $this->getLastName();
  }
  
  public function uid() {
    return $this->uid;
  }

  public function user() {
    return user_load($this->uid);
  }
  
  public function type($raw = TRUE) {
    if(!is_null($this->type)) {
      if($raw) {
        return $this->type;
      }
      else {
        return patient_get_types()[$this->type];
      }
    }
    return '';
  }
  
  public function hasBookedAssessment() {
    if (db_table_exists('appointment')) {
      $query = db_select('appointment', 'a');
      $query->distinct();
      $query->condition('a.patient', $this->identifier());
      $query->condition('a.type', 'assessment');
      $query->condition('a.state', 2);
      $query->fields('a', ['id']);
      $result = $query->execute()->fetchCol();
      
      if(!empty($result)) {
        return TRUE;
      }
    }
    return FALSE;
  }
  
  public function profileUrl() {
    return 'user/' . $this->uid . '/counselling/patient';
  }
  
  public function publicUrl($ajax = FALSE) {
    if(!$ajax) {
      return 'bereavement-counselling/patient/' . $this->identifier();
    }
    return 'bereavement-counselling/patient/' . $this->identifier() . '/nojs';
  }
}
