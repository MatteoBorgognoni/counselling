<div class="confirmation-details">

  <?php if($practitioner): ?>
    <div class="practitioner">
      <h2 class="heading">Your appointment is booked with:</h2>
      <?php echo render($practitioner) ?>
    </div>
  <?php endif ?>

  <?php if($appointment): ?>
    <div class="appointment">

      <h2 class="heading"><?php echo $heading ?></h2>

      <dl class="date">
        <dt>Date</dt>
        <dd><?php echo $appointment->formatDate('l jS F, Y'); ?></dd>
      </dl>
      <dl class="time">
        <dt>Time</dt>
        <dd><?php echo $appointment->formatTime('H:i'); ?></dd>
      </dl>

      <?php if($change_link): ?>
        <div class="change-details">
          <a href="/<?php echo $change_link ?>"><?php echo $change_link_title ?></a>
        </div>
      <?php endif ?>

    </div>
  <?php endif ?>

</div>