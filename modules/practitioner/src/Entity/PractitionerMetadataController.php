<?php

namespace Drupal\practitioner\Entity;

use EntityDefaultMetadataController;


class PractitionerMetadataController extends EntityDefaultMetadataController {
  
  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    
    $info['practitioner']['properties']['id'] = array(
      'label' => t('Practitioner ID'),
      'label_position' => 'inline',
      'description' => t('The ID of the Practitioner profile.'),
      'type' => 'integer',
      'schema field' => 'id',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['practitioner']['properties']['bundle'] = array(
      'label' => t('Practitioner profile bundle'),
      'label_position' => 'inline',
      'description' => t('Bundle of Practitioner'),
      'type' => 'text',
      'schema field' => 'bundle',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['practitioner']['properties']['type'] = array(
      'label' => t('Practitioner profile type'),
      'label_position' => 'inline',
      'description' => t('Type of Practitioner'),
      'type' => 'text',
      'options list' => 'practitioner_get_types',
      'schema field' => 'type',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['practitioner']['properties']['email'] = array(
      'label' => t('Email address'),
      'label_position' => 'inline',
      'description' => t('The email address for the Practitioner.'),
      'type' => 'text',
      'schema field' => 'email',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['practitioner']['properties']['first_name'] = array(
      'label' => t('First name'),
      'label_position' => 'inline',
      'description' => t('The first name for the Practitioner.'),
      'type' => 'text',
      'schema field' => 'first_name',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['practitioner']['properties']['last_name'] = array(
      'label' => t('Last name'),
      'label_position' => 'inline',
      'description' => t('The last name for the Practitioner.'),
      'type' => 'text',
      'schema field' => 'last_name',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['practitioner']['properties']['fullname'] = array(
      'label' => t('Full name'),
      'label_position' => 'inline',
      'description' => t('The full name for the Practitioner.'),
      'getter callback' => 'entity_property_getter_method',
      'type' => 'text',
      'entity views field' => TRUE,
      'computed' => TRUE,
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['practitioner']['properties']['room'] = array(
      'label' => t('Room'),
      'label_position' => 'inline',
      'description' => t('The room url for the Practitioner.'),
      'type' => 'text',
      'schema field' => 'room',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['practitioner']['properties']['status'] = array(
      'label' => t('Status'),
      'label_position' => 'inline',
      'description' => t('The status of the Practitioner (active/blocked).'),
      'setter callback' => 'entity_property_verbatim_set',
      'type' => 'integer',
      'options list' => 'practitioner_get_statuses',
      'schema field' => 'status',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
    $info['practitioner']['properties']['created'] = array(
      'label' => t('Creation date'),
      'type' => 'date',
      'label_position' => 'inline',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'created',
      'description' => t('The creation date of the Practitioner.'),
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['practitioner']['properties']['changed'] = array(
      'label' => t('Update date'),
      'type' => 'date',
      'label_position' => 'inline',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'changed',
      'description' => t('The update date of the Practitioner.'),
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['practitioner']['properties']['uid'] = array(
      'label' => t('Practitioner user'),
      'type' => 'user',
      'label_position' => 'inline',
      'description' => t('The user associated with the Practitioner.'),
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'uid',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['practitioner']['properties']['url'] = array(
      'label' => t('Practitioner Url'),
      'label_position' => 'inline',
      'description' => t('The Practitioner url'),
      'getter callback' => 'entity_metadata_entity_get_properties',
      'type' => 'uri',
      'computed' => TRUE,
      'entity views field' => TRUE,
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    
    return $info;
  }
}