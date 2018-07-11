<?php

/**
 * Implements hook_menu().
 */
function practitioner_menu() {
  $items = [];
  
  $items['admin/content/practitioner/manage/%practitioner/disable'] = array(
    'title' => 'Disable Practitioner',
    'type' => MENU_CALLBACK,
    'access arguments' => array('disable practitioner profile'),
    'page callback' => 'drupal_get_form',
    'page arguments' => ['practitioner_disable_form', 4],
    'file' => 'src/Form/practitioner_disable_form.php',
  
  );
  
  $items['admin/content/practitioner/manage/%practitioner/enable'] = array(
    'title' => 'Enable Practitioner',
    'type' => MENU_CALLBACK,
    'access arguments' => array('disable practitioner profile'),
    'page callback' => 'drupal_get_form',
    'page arguments' => ['practitioner_enable_form', 4],
    'file' => 'src/Form/practitioner_enable_form.php',
  );
  
  return $items;
}

