<?php

/**
 * @file
 * Patient controller class.
 */

namespace Drupal\patient\Entity;

use EntityAPIController;
use DatabaseTransaction;
use Exception;

/**
 * The Controller for Patient entities.
 */
class PatientController extends EntityAPIController {
  /**
   * Create a Patient.
   *
   * @param array $values
   *   An array containing the possible values.
   *
   * @return object
   *   A object with all default fields initialized.
   */
  public function create(array $values = []) {
    global $user;
    // Add values that are specific to our entity.
    $values += [
      'id' => '',
      'bundle' => 'patient',
      'type' => NULL,
      'is_new' => TRUE,
      'email' => '',
      'first_name' => '',
      'last_name' => '',
      'reminder_email' => 0,
      'created' => '',
      'changed' => '',
      'uid' => NULL,
    ];

    $entity = parent::create($values);

    return $entity;
  }
  
  /**
   * Implements EntityAPIControllerInterface.
   *
   * @param $entity
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
