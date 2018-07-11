<?php

namespace Drupal\appointment\Entity;

use EntityDefaultExtraFieldsController;

/**
 * Default controller for generating extra fields based on property metadata.
 *
 * By default a display extra field for each property not being a field, ID or
 * bundle is generated.
 */
class AppointmentExtraFieldsController extends EntityDefaultExtraFieldsController {
 
 /**
   * Implements EntityExtraFieldsControllerInterface::fieldExtraFields().
   */
  public function fieldExtraFields() {
    $extra = [];

    $bundles = $this->entityInfo['bundles'];
    
    // Handle bundle properties.
    
    foreach ($bundles as $bundle_name => $info) {
      foreach ($this->propertyInfo['properties'] as $name => $property_info) {
        if (empty($property_info['field']) && isset($property_info['extra_fields'])) {
          if(isset($property_info['extra_fields']['display']) && $property_info['extra_fields']['display']) {
            $extra[$this->entityType][$bundle_name]['display'][$name] = $this->generateExtraFieldInfo($name, $property_info);
          }
          if(isset($property_info['extra_fields']['form']) && $property_info['extra_fields']['form']) {
            $extra[$this->entityType][$bundle_name]['form'][$name] = $this->generateExtraFieldInfo($name, $property_info);
          }
        }
      }
    }
    return $extra;
  }
  
  /**
   * Generates the display field info for a given property.
   */
  protected function generateExtraFieldInfo($name, $property_info) {
    $info = array(
      'label' => $property_info['label'],
      'weight' => 0,
    );
    if (!empty($property_info['description'])) {
      $info['description'] = $property_info['description'];
    }
    return $info;
  }
  
}