<?php if(!empty($practitioners)): ?>

<div class="practitioner-choice support-team">
  <?php foreach ($practitioners as $id => $practitioner): ?>
    <div class="practitioner practitioner-<?php echo $id ?>" practitioner-id="<?php echo $id ?>">
      <div class="practitioner-choice-elements">
        <div class="badge"><?php echo practitioner_badge($practitioner) ?></div>
        <div class="link"><a href="#" onclick="return false;">Talk to <?php echo $practitioner->getFirstName() ?></a></div>
      </div>
    </div>
  <?php endforeach ?>
</div>

<?php else: ?>
<div class="help">There are no available practitioners.</div>
<?php endif; ?>
