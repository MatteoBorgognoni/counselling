<?php
  global $user;
  $profile = patient_load_from_uid($user->uid);
?>

<?php if ($profile): ?>
  <p>Our bereavement video support service provides free and confidential support to anyone who needs our help; and is funded entirely by donations.
    Please help us be there for others in the future.</p>
  <div class="button--wrapper"><a href="<?php echo COUNSELLING_DONATION_URL ?>" target="_blank" class="button">Make a donation</a></div>
<?php endif; ?>