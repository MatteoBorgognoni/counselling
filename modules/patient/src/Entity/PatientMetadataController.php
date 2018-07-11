<?php

namespace Drupal\patient\Entity;

use EntityDefaultMetadataController;


class PatientMetadataController extends EntityDefaultMetadataController {
  
  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    
    $info['patient']['properties']['id'] = array(
      'label' => t('Patient ID'),
      'label_position' => 'inline',
      'description' => t('The ID of the Patient profile.'),
      'type' => 'integer',
      'schema field' => 'id',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['patient']['properties']['bundle'] = array(
      'label' => t('Patient profile bundle'),
      'label_position' => 'inline',
      'description' => t('Bundle of Patient'),
      'type' => 'text',
      'schema field' => 'bundle',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['patient']['properties']['type'] = array(
      'label' => t('Patient profile type'),
      'label_position' => 'inline',
      'description' => t('Type of Patient'),
      'type' => 'text',
      'schema field' => 'type',
      'options list' => 'patient_get_types',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['patient']['properties']['email'] = array(
      'label' => t('Email address'),
      'label_position' => 'inline',
      'description' => t('The email address for the Patient.'),
      'type' => 'text',
      'schema field' => 'email',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['patient']['properties']['first_name'] = array(
      'label' => t('First name'),
      'label_position' => 'inline',
      'description' => t('The first name for the Patient.'),
      'type' => 'text',
      'schema field' => 'first_name',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['patient']['properties']['last_name'] = array(
      'label' => t('Last name'),
      'label_position' => 'inline',
      'description' => t('The last name for the Patient.'),
      'type' => 'text',
      'schema field' => 'last_name',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['patient']['properties']['fullname'] = array(
      'label' => t('Full name'),
      'label_position' => 'inline',
      'description' => t('The full name for the Patient.'),
      'getter callback' => 'entity_property_getter_method',
      'type' => 'text',
      'entity views field' => TRUE,
      'computed' => TRUE,
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['patient']['properties']['reminder_email'] = array(
      'label' => t('Email reminders'),
      'label_position' => 'inline',
      'description' => t('Whether the Patient wants email reminders (yes/no).'),
      'setter callback' => 'entity_property_verbatim_set',
      'type' => 'integer',
      'options list' => 'patient_get_reminder_options',
      'schema field' => 'reminder_email',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['patient']['properties']['status'] = array(
      'label' => t('Status'),
      'label_position' => 'inline',
      'description' => t('The status of the Patient (active/blocked).'),
      'setter callback' => 'entity_property_verbatim_set',
      'type' => 'integer',
      'options list' => 'patient_get_statuses',
      'schema field' => 'status',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
    $info['patient']['properties']['created'] = array(
      'label' => t('Creation date'),
      'label_position' => 'inline',
      'type' => 'date',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'created',
      'description' => t('The creation date of the Patient.'),
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['patient']['properties']['changed'] = array(
      'label' => t('Update date'),
      'label_position' => 'inline',
      'type' => 'date',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'changed',
      'description' => t('The update date of the Patient.'),
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['patient']['properties']['uid'] = array(
      'label' => t('Patient user'),
      'label_position' => 'inline',
      'type' => 'user',
      'description' => t('The user associated with the Patient.'),
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'uid',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['patient']['properties']['url'] = array(
      'label' => t('Patient Url'),
      'label_position' => 'inline',
      'description' => t('The Patient url'),
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