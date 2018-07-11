<?php

use Drupal\counselling\CounsellingManager;

/**
 * Implements hook_block_info().
 *
 * To create new Counselling promotional block.
 */
function counselling_block_info() {
  $blocks['counselling_promotional_block'] = array(
    'info' => t('Counselling promotional block'),
    'cache' => DRUPAL_NO_CACHE,
  );
  $blocks['counselling_donation_block'] = array(
    'info' => t('Counselling donation block'),
    'cache' => DRUPAL_NO_CACHE,
  );

  return $blocks;
}

/**
 * Implements hook_block_view().
 */
function counselling_block_view($delta = '') {
  $block = [];
  global $user;
  
  $manager = new CounsellingManager();
  $system_is_enabled = $manager->getSystemStatus();
  
  switch ($delta) {
    case 'counselling_promotional_block':
      if ($system_is_enabled && !user_is_anonymous()) {
        $block = [
          'subject' => t('Online bereavement counselling service'),
          'content' => theme('counselling__promotional_block', $block),
        ];
      }
      break;
      
    case 'counselling_donation_block':
      if ($system_is_enabled && !user_is_anonymous() && patient_has_profile($user->uid)) {
        $block = [
          'subject' => t('Can you help?'),
          'content' => theme('counselling__donation_block', $block),
        ];
      }

      break;
      
  }

  return $block;
}
