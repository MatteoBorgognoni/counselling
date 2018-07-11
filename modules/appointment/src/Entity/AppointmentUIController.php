<?php

/**
 * @file
 * appointment editing UI.
 */

namespace Drupal\appointment\Entity;

use EntityDefaultUIController;
use EntityFieldQuery;


/**
 * UI controller.
 */
class AppointmentUIController extends EntityDefaultUIController {
  /**
   * Overrides hook_menu() defaults.
   *
   * Main reason for doing this is that parent class hook_menu() is, optimized
   * for entity type administration.
   */
  public function hook_menu() {
    // TODO: EntityDefaultUIController controller automatically adds the menu
    // to import entities, but there is a bug with this action and can not work
    // with the version of your entity_api module, track the issue # 2112755
    // https://www.drupal.org/node/2112755
    $wildcard = isset($this->entityInfo['admin ui']['menu wildcard']) ? $this->entityInfo['admin ui']['menu wildcard'] : '%entity_object';
    $items = parent::hook_menu();

    // Change the overview menu type for the list of Appointment.
    $items[$this->path]['type'] = MENU_LOCAL_TASK;

    // Replaces default entity title.
    $items[$this->path]['title'] = 'Appointment';

    // Extend the 'edit' path.
    $items['appointment/' . $wildcard . '/edit'] = array(
      'title callback' => 'appointment_page_title',
      'title arguments' => array(1),
      'page callback' => 'entity_ui_get_form',
      'page arguments' => array($this->entityType, 1),
      'access callback' => 'appointment_access',
      'access arguments' => array('edit', 1),
    );

    $items[$this->path . '/add'] = array(
      'title callback' => 'entity_ui_get_action_title',
      'title arguments' => array('add', $this->entityType, 'appointment'),
      'page callback' => 'entity_ui_get_form',
      'page arguments' => array($this->entityType, appointment_create(), 'add'),
      'access arguments' => array('create appointment'),
    );

    $items['admin/content/appointment/' . $wildcard] = array(
      'title callback' => 'appointment_page_title',
      'title arguments' => array(3),
      'page callback' => 'appointment_view',
      'page arguments' => array(3),
      'access callback' => 'appointment_access',
      'access arguments' => array('view', 3),
    );
  
    $items['admin/structure/appointment'] = array(
      'title' => 'Appointment',
      'type' => MENU_NORMAL_ITEM,
      'access arguments' => array('administer appointment'),
      'page callback' => 'appointment_bundle_config',
    );

    return $items;
  }
  
  /**
   * Generates the render array for a overview table for arbitrary entities
   * matching the given conditions.
   *
   * @param $conditions
   *   An array of conditions as needed by entity_load().
   
   * @return array
   *   A renderable array.
   */
  public function overviewTable($conditions = array()) {
    
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', $this->entityType);
    
    // Add all conditions to query.
    foreach ($conditions as $key => $value) {
      $query->propertyCondition($key, $value);
    }
    
    if ($this->overviewPagerLimit) {
      $query->pager($this->overviewPagerLimit);
    }
    
    $query->propertyOrderBy('date', 'ASC');
    
    $results = $query->execute();
    
    $ids = isset($results[$this->entityType]) ? array_keys($results[$this->entityType]) : array();
    $entities = $ids ? entity_load($this->entityType, $ids) : array();
    //ksort($entities);
   
    $rows = array();
    foreach ($entities as $entity) {
      
      $patient_name = '';
      if ($entity->patient) {
        /** @var \Drupal\patient\Entity\Patient $patient */
        $patient = patient_load($entity->patient);
        $patient_name = $patient ? l($patient->label(), $patient->uri()['path']) : '';
      }
  
      $practitioner_name = '';
      if ($entity->practitioner) {
        /** @var \Drupal\practitioner\Entity\practitioner $practitioner */
        $practitioner = practitioner_load($entity->practitioner);
        $practitioner_name = $practitioner ? l($practitioner->label(), $practitioner->uri()['path']) : '';
      }
      
      $additional_cols = [
        'type' => $entity->type(),
        'patient' => $patient_name,
        'practitioner' => $practitioner_name,
        'date' => $entity->date(),
        'time_start' => $entity->time_start,
        'time_end' => $entity->time_end,
        'state' => $entity->state(),
      ];
      
      $rows[] = $this->overviewTableRow($conditions, entity_id($this->entityType, $entity), $entity, $additional_cols);
    }
    
    $additional_headers = ['Type', 'Patient', 'Practitioner', 'Date', 'Start', 'End', 'State'];
  
    
    $render = array(
      '#theme' => 'table',
      '#header' => $this->overviewTableHeaders($conditions, $rows, $additional_headers),
      '#rows' => $rows,
      '#empty' => t('None.'),
    );
    return $render;
  }
  
  /**
   * Generates the table headers for the overview table.
   */
  protected function overviewTableHeaders($conditions, $rows, $additional_header = array()) {
    $header = $additional_header;
    array_unshift($header, t('Label'));
    
    // Add operations with the right colspan.
    $header[] = array('data' => t('Operations'), 'colspan' => $this->operationCount());
    return $header;
  }
  
  /**
   * Generates the row for the passed entity and may be overridden in order to
   * customize the rows.
   *
   * @param $additional_cols
   *   Additional columns to be added after the entity label column.
   */
  protected function overviewTableRow($conditions, $id, $entity, $additional_cols = array()) {
    $entity_uri = entity_uri($this->entityType, $entity);
    
    $row[] = array('data' => array(
      '#theme' => 'entity_ui_overview_item',
      '#label' => entity_label($this->entityType, $entity),
      '#name' => !empty($this->entityInfo['exportable']) ? entity_id($this->entityType, $entity) : FALSE,
      '#url' => $entity_uri ? $entity_uri : FALSE,
      '#entity_type' => $this->entityType),
    );
    
    // Add in any passed additional cols.
    foreach ($additional_cols as $col) {
      $row[] = $col;
    }
    

    // In case this is a bundle, we add links to the field ui tabs.
    $field_ui = !empty($this->entityInfo['bundle of']) && entity_type_is_fieldable($this->entityInfo['bundle of']) && module_exists('field_ui');
    // For exportable entities we add an export link.
    $exportable = !empty($this->entityInfo['exportable']);
    // If i18n integration is enabled, add a link to the translate tab.
    $i18n = !empty($this->entityInfo['i18n controller class']);
    
    // Add operations depending on the status.
    if (entity_has_status($this->entityType, $entity, ENTITY_FIXED)) {
      $row[] = array('data' => l(t('clone'), $this->path . '/manage/' . $id . '/clone'), 'colspan' => $this->operationCount());
    }
    else {
      $row[] = l(t('edit'), $this->path . '/manage/' . $id);
      
      if ($field_ui) {
        $row[] = l(t('manage fields'), $this->path . '/manage/' . $id . '/fields');
        $row[] = l(t('manage display'), $this->path . '/manage/' . $id . '/display');
      }
      if ($i18n) {
        $row[] = l(t('translate'), $this->path . '/manage/' . $id . '/translate');
      }

      
      if (empty($this->entityInfo['exportable']) || !entity_has_status($this->entityType, $entity, ENTITY_IN_CODE)) {
        $row[] = l(t('delete'), $this->path . '/manage/' . $id . '/delete', array('query' => drupal_get_destination()));
      }
      elseif (entity_has_status($this->entityType, $entity, ENTITY_OVERRIDDEN)) {
        $row[] = l(t('revert'), $this->path . '/manage/' . $id . '/revert', array('query' => drupal_get_destination()));
      }
      else {
        $row[] = '';
      }
    }

    return $row;
  }
  
  
  
}

