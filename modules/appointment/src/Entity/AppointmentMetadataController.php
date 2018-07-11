<?php

namespace Drupal\appointment\Entity;

use EntityDefaultMetadataController;


class AppointmentMetadataController extends EntityDefaultMetadataController {
  
  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    
    $info['appointment']['properties']['id'] = array(
      'label' => t('Appointment ID'),
      'description' => t('The ID of the Appointment.'),
      'type' => 'integer',
      'schema field' => 'id',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['appointment']['properties']['bundle'] = array(
      'label' => t('Bundle'),
      'description' => t('Bundle of Appointment'),
      'type' => 'text',
      'schema field' => 'type',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['appointment']['properties']['type'] = array(
      'label' => t('Appointment type'),
      'description' => t('Type of Appointment'),
      'getter callback' => 'entity_property_getter_method',
      'setter callback' => 'entity_property_setter_method',
      'type' => 'text',
      'schema field' => 'type',
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['appointment']['properties']['state'] = array(
      'label' => t('State'),
      'description' => t('The state of the Appointment.'),
      'setter callback' => 'entity_property_verbatim_set',
      'type' => 'integer',
      'options list' => 'appointment_get_states',
      'schema field' => 'state',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
    $info['appointment']['properties']['attended'] = array(
      'label' => t('Attended'),
      'description' => t('The attending state of the Appointment.'),
      'setter callback' => 'entity_property_verbatim_set',
      'type' => 'integer',
      'options list' => 'appointment_get_attending_states',
      'schema field' => 'attended',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
    $info['appointment']['properties']['date'] = array(
      'label' => t('Appointment date'),
      'type' => 'date',
      'getter callback' => 'entity_property_verbatim_get',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'date',
      'description' => t('The scheduled date of the Appointment.'),
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['appointment']['properties']['date_end'] = array(
      'label' => t('Appointment date end'),
      'type' => 'date',
      'getter callback' => 'entity_property_verbatim_get',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'date',
      'description' => t('The scheduled date end of the Appointment.'),
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['appointment']['properties']['time_start'] = array(
      'label' => t('Appointment time start'),
      'type' => 'text',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'time_start',
      'description' => t('The scheduled starting time of the Appointment.'),
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['appointment']['properties']['time_end'] = array(
      'label' => t('Appointment time end'),
      'type' => 'text',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'time_end',
      'description' => t('The scheduled ending time of the Appointment.'),
      'extra_fields' => [
        'form' => TRUE,
        'display' => TRUE,
      ],
    );
    $info['appointment']['properties']['created'] = array(
      'label' => t('Creation date'),
      'type' => 'date',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'created',
      'description' => t('The creation date of the Appointment.'),
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['appointment']['properties']['changed'] = array(
      'label' => t('Update date'),
      'type' => 'date',
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'changed',
      'description' => t('The update date of the Appointment.'),
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
    $info['appointment']['properties']['patient'] = array(
      'label' => t('Patient'),
      'type' => 'patient',
      'description' => t('The patient attending the Appointment.'),
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'patient',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
    $info['appointment']['properties']['practitioner'] = array(
      'label' => t('Practitioner'),
      'type' => 'practitioner',
      'description' => t('The practitioner hosting the Appointment.'),
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'practitioner',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
  
    $info['appointment']['properties']['uid'] = array(
      'label' => t('Appointment author'),
      'type' => 'user',
      'description' => t('The user who authored the Appointment.'),
      'setter callback' => 'entity_property_verbatim_set',
      'schema field' => 'uid',
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
  
    $info['appointment']['properties']['feedback'] = array(
      'label' => t('Feedback'),
      'description' => t('Feedback for the Appointment'),
      'type' => 'text',
      'schema field' => 'feedback',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
  
    $info['appointment']['properties']['score'] = array(
      'label' => t('Score'),
      'description' => t('Score for the Appointment'),
      'getter callback' => 'entity_property_getter_method',
      'setter callback' => 'entity_property_setter_method',
      'type' => 'integer',
      'schema field' => 'score',
      'extra_fields' => [
        'form' => FALSE,
        'display' => TRUE,
      ],
    );
    
    $info['appointment']['properties']['url'] = array(
      'label' => t('Appointment Url'),
      'description' => t('The Appointment url'),
      'getter callback' => 'entity_metadata_entity_get_properties',
      'type' => 'uri',
      'computed' => TRUE,
      'entity views field' => TRUE,
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
  
    $info['appointment']['properties']['admin_cancel_url'] = array(
      'label' => t('Appointment Cancel Url'),
      'description' => t('The Appointment cancel url for admins'),
      'getter callback' => 'entity_property_getter_method',
      'type' => 'uri',
      'computed' => TRUE,
      'entity views field' => TRUE,
      'extra_fields' => [
        'form' => FALSE,
        'display' => FALSE,
      ],
    );
  
    $info['appointment']['properties']['admin_assign_url'] = array(
      'label' => t('Appointment Assign Url'),
      'description' => t('The Appointment assign url for admins'),
      'getter callback' => 'entity_property_getter_method',
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