<?php


namespace Drupal\counselling\Controller;

use Drupal\counselling\Storage;

class ThankYou {
  
  public static function build($key) {
    global $user;
    
    $method = self::keyToMethod($key);
    if(!method_exists(__CLASS__, $key)) {
      return drupal_not_found();
    }
  
    $storage = new Storage('thank_you');
    if($storage->get('visited')) {
      
      $storage->clear();
      
      $data = self::$method();
      
      $breadcrumb = array(
        l(t('Home'), '<front>'),
        l(t('Advice & support '), '/'),
        l(t('Community'), '/community'),
        l(t('Online bereavement counselling profile'), 'user/' . $user->uid . '/counselling/patient/appointments'),
        t($data['title']),
      );
      drupal_set_breadcrumb($breadcrumb);
  
      drupal_set_title($data['title']);
  
      $build = [
        '#theme' => 'counselling__page',
        '#key' => 'thank_you__' . $key,
        '#elements' => $data['elements'],
      ];
  
      return $build;
    }
    else {
      return drupal_goto('user/' . $user->uid . '/counselling/patient/appointments');
    }
    

  }
  
  protected static function feedback() {
    $data = [];
    $data['title'] = 'Support the bereavement video service';
    
    $text = [
      '#type' => 'markup',
      '#markup' => '<p>Our bereavement video support service provides free and confidential support to anyone who needs our help and is funded entirely by donations.</p>
                    <p>Please help us be there for others in the future.</p>',
    ];
    
    $link = l('Make a donation', COUNSELLING_DONATION_URL, ['external' => 'true', 'attributes' => ['class' => 'button']]);
    
    $data['elements'] = [
      'text' => render($text),
      'link' => '<div class="button--wrapper">' . render($link) . '</div>',
    ];
    return $data;
  }
  
  protected static function keyToMethod($key) {
    $str = drupal_clean_css_identifier($key);
    $str = str_replace('-', ' ', $str);
    $str = str_replace('_', ' ', $str);
    $str = ucwords($str);
    $str = str_replace(' ', '', $str);
    $str = lcfirst($str);
    return $str;
  }
  
}