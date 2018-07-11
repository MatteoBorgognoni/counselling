<div class="counselling-step-indicator">
  <?php foreach ($steps as $number => $step): ?>
    <span class="<?php echo $step['classes'] ?>"><?php echo $step['value'] ?></span>
  <?php endforeach; ?>
</div>