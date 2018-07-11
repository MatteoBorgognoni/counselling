<?php

namespace Drupal\counselling;

class Storage {
  
  protected $id;
  
  function __construct($id) {
    $this->id = $id;
  }
  
  public function set($key, $value) {
    $_SESSION[$this->id][$key] = serialize($value);
  }
  
  public function get($key) {
    if(!isset($_SESSION[$this->id][$key])) {
      return FALSE;
    }
    $value = unserialize($_SESSION[$this->id][$key]);
    return $value;
  }
  
  public function delete($key) {
    unset($_SESSION[$this->id][$key]);
  }

  public function clear() {
    unset($_SESSION[$this->id]);
  }
  
  public function getAll() {
    return array_map('unserialize', $_SESSION[$this->id]);
  }

  public function getSession() {
    return $_SESSION;
  }
}