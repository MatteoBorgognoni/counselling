<div class="counselling-page <?php echo drupal_clean_css_identifier($key)?>">
  
  <?php foreach ($elements as $element): ?>
    <?php echo render($element) ?>
  <?php endforeach; ?>
</div>