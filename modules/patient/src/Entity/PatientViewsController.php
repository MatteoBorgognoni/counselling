<?php

namespace Drupal\patient\Entity;

use EntityDefaultViewsController;

/**
 * Patient Views Controller class.
 */
class PatientViewsController extends EntityDefaultViewsController {
  
  /**
   * Edit or add extra fields to views_data().
   */
  public function views_data() {
    $data = parent::views_data();
    
    // Add your custom data here
    
    return $data;
  }
}
