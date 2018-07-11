<?php

/**
 * @file
 * Practitioner class.
 */

namespace Drupal\practitioner\Entity;

use Entity;

/**
 * The class used for practitioner entities.
 */
class Practitioner extends Entity {
  /**
   * Class constructor.
   */
  public function __construct($values = array()) {
    parent::__construct($values, 'practitioner');
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
    return array('path' => 'admin/content/practitioner/' . $this->identifier());
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
  
  public function type($raw = TRUE) {
    if(!is_null($this->type)) {
      if($raw) {
        return $this->type;
      }
      else {
        return practitioner_get_types()[$this->type];
      }
    }
    return '';
  }
  
  public function user() {
    return user_load($this->uid);
  }
  
  public function profileUrl() {
    return 'user/' . $this->uid . '/counselling/practitioner';
  }
  
  public function publicUrl($ajax = FALSE) {
    if(!$ajax) {
      return 'bereavement-counselling/practitioner/' . $this->identifier();
    }
    return 'bereavement-counselling/practitioner/' . $this->identifier() . '/nojs';
  }
  
}
