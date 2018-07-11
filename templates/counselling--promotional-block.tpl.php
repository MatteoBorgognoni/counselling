<?php
  global $user;
  $profile = patient_load_from_uid($user->uid);
  $practitioner = practitioner_load_from_uid($user->uid);
?>

<?php if (!$profile && !$practitioner): ?>
  <p>We're trialling a new video support service for users of the Sue Ryder online community. If you'd like to talk to one of our counsellors, contact us today.</p>
  <p>The service is completely free and confidential.</p>
  <div class='button--wrapper'><a href='/bereavement-counselling' class='button'>Find out more</a></div>
<?php endif; ?>

<?php if ($profile): ?>
  <?php if ($profile->status): ?>
  <p>You have signed up.</p>
  <div class='button--wrapper'><a href='/user/<?php echo $user->uid ?>/counselling/patient' class='button'>View appointments</a></div>
  <?php else: ?>
    <p>Your account is disabled.</p>
    <p>To re-activate, please contact <a href="mailto: online.community@sueryder.org">online.community@sueryder.org</a></p>
  <?php endif; ?>
<?php endif; ?>

<?php if ($practitioner): ?>
  <div class='button--wrapper'><a href='/user/<?php echo $user->uid ?>/counselling/practitioner' class='button'>View appointments</a></div>
<?php endif; ?>
