<?php

/**
 * @file
 * Appointment controller class.
 */

namespace Drupal\appointment\Entity;

use EntityAPIControllerExportable;
use DatabaseTransaction;
use Exception;

/**
 * The Controller for Appointment entities.
 */
class AppointmentController extends EntityAPIControllerExportable {
  /**
   * Create a Appointment.
   *
   * @param array $values
   *   An array containing the possible values.
   *
   * @return object
   *   A object with all default fields initialized.
   */
  public function create(array $values = array()) {
    global $user;
    // Add values that are specific to our entity.
    $values += array(
      'id' => '',
      'bundle' => 'appointment',
      'type' => '',
      'is_new' => TRUE,
      'date' => REQUEST_TIME,
      'date_end' => REQUEST_TIME + 3600,
      'time_start' => '',
      'time_end' => '',
      'patient' => NULL,
      'practitioner' => NULL,
      'state' => 0,
      'attended' => NULL,
      'feedback' => NULL,
      'score' => NULL,
      'created' => '',
      'changed' => '',
      'uid' => NULL,
    );
    
    /** @var \Drupal\practitioner\Entity\Practitioner $practitioner */
    $practitioner = practitioner_load_from_uid($user->uid);
    if($practitioner) {
      $values['practitioner'] = (int) $practitioner->identifier();
    }

    $entity = parent::create($values);

    return $entity;
  }
  
  /**
   * Implements EntityAPIControllerInterface.
   *
   * @param $transaction
   *   Optionally a DatabaseTransaction object to use. Allows overrides to pass
   *   in their transaction object.
   */
  
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    
    $transaction = isset($transaction) ? $transaction : db_transaction();
    try {
      // Load the stored entity, if any.
      if (!empty($entity->{$this->idKey}) && !isset($entity->original)) {
        // In order to properly work in case of name changes, load the original
        // entity using the id key if it is available.
        $entity->original = entity_load_unchanged($this->entityType, $entity->{$this->idKey});
      }
      $entity->is_new = !empty($entity->is_new) || empty($entity->{$this->idKey});
      $this->invoke('presave', $entity);
      
      if(is_array($entity->type)) {
        foreach ($entity->type as $type) {
          if($type) {
            $value[$type] = $type;
          }
        }
        $entity->type = implode('|', $value);
      }
      
      $date = date('Y-m-d', $entity->date) . ' ' . $entity->time_start;
      $entity->date = strtotime($date);
  
      $date_end = date('Y-m-d', $entity->date) . ' ' . $entity->time_end;
      $entity->date_end = strtotime($date_end);
      
      if ($entity->is_new) {
        $return = drupal_write_record($this->entityInfo['base table'], $entity);
        if ($this->revisionKey) {
          $this->saveRevision($entity);
        }
        $this->invoke('insert', $entity);
      }
      else {
        // Update the base table if the entity doesn't have revisions or
        // we are updating the default revision.
        if (!$this->revisionKey || !empty($entity->{$this->defaultRevisionKey})) {
          $return = drupal_write_record($this->entityInfo['base table'], $entity, $this->idKey);
        }
        if ($this->revisionKey) {
          $return = $this->saveRevision($entity);
        }
        $this->resetCache(array($entity->{$this->idKey}));
        $this->invoke('update', $entity);
        
        // Field API always saves as default revision, so if the revision saved
        // is not default we have to restore the field values of the default
        // revision now by invoking field_attach_update() once again.
        if ($this->revisionKey && !$entity->{$this->defaultRevisionKey} && !empty($this->entityInfo['fieldable'])) {
          field_attach_update($this->entityType, $entity->original);
        }
      }
      
      // Ignore slave server temporarily.
      db_ignore_slave();
      unset($entity->is_new);
      unset($entity->is_new_revision);
      unset($entity->original);
      
      return $return;
    }
    catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw $e;
    }
  }
  
  
}
