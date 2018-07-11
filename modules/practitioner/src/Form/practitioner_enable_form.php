<?php

use Drupal\practitioner\Entity\Practitioner;

/**
 * Form callback: create or edit a Practitioner.
 */
function practitioner_enable_form($form, &$form_state, Practitioner $practitioner) {
  // Add the breadcrumb for the form's location.
  practitioner_enable_set_breadcrumb();
  $form['#prefix'] = '<div id="practitioner-form-wrapper">';
  $form['#suffix'] = '</div>';

  $form_state['practitioner'] = $practitioner;
  
  $form['actions'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('form-actions')),
    '#weight' => 400,
  );
  
  // We add the form #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = array();
  
  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }
  
  $form['actions']['back'] = array(
    '#type' => 'submit',
    '#limit_validation_errors' => [],
    '#submit' => $submit + ['practitioner_enable_form_submit'],
    '#value' => t('Back'),
    '#name' => 'back',
  );
  
  $form['actions']['enable'] = array(
    '#type' => 'submit',
    '#value' => t('Enable Practitioner'),
    '#submit' => $submit + array('practitioner_enable_form_submit'),
    '#name' => 'enable',
  );
  
  return $form;
}

/**
 * Form API submit callback for the Practitioner form.
 */
function practitioner_enable_form_submit(&$form, &$form_state) {
  
  $trigger = $form_state['triggering_element']['#name'];
  switch ($trigger) {
    case 'enable':
      $practitioner = $form_state['practitioner'];
      $practitioner->status = 1;
      $practitioner->changed = time();
      $practitioner->save();
      break;
  }
  
  // Send feedback message to the user.
  $message = t("Practitioner :label enabled.", array(':label' => $practitioner->email));
  
  drupal_set_message($message);
  
  $form_state['redirect'] = 'admin/content/practitioners';
}


/**
 * Sets the breadcrumb for administrative Practitioner pages.
 */
function practitioner_enable_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('Content'), 'admin/content'),
    l(t('Practitioner'), 'admin/content/practitioner'),
  );
  
  drupal_set_breadcrumb($breadcrumb);
}
