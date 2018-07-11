<?php
  global $user;
  $user_has_patient_profile = patient_has_profile($user->uid);
  $user_has_practitioner_profile = practitioner_has_profile($user->uid);
?>

<div class="service-details">

  <div class="intro">
    <p>We're trialling a new video support service for users of the Sue Ryder online community.
      If you've experienced a bereavement and you'd like to talk to one of our counsellors,
      register today for a free and confidential assessment.</p>
  </div>

  <div class="steps">
    <h2>How it works</h2>
    <div class="steps-container">
      <hr>
      <div class="step register">
        <div class="icon"></div>
        <h3>Register for free</h3>
        <p>Complete our online form and give us some brief details about your situation.</p>
      </div>
      <div class="step book">
        <div class="icon"></div>
        <h3>Book initial video support assessment</h3>
        <p>Choose a day & time that works best for you, Mon-Fri, 9-5pm</p>
      </div>
      <div class="step video">
        <div class="icon"></div>
        <h3>Video support</h3>
        <p>Access from anywhere: desktop, tablet or mobile</p>
      </div>
    </div>
  </div>

  <div class="features">
    <h3>What you can expect:</h3>
    <ul class="features-details">
      <li>A completely free and confidential service</li>
      <li>An initial video support assessment with a professional Sue Ryder bereavement counsellor</li>
      <li>Up to six 45-minute video support sessions with a bereavement counsellor</li>
      <li>Helpful email reminders before each appointment</li>
      <li>Access to video sessions through PCs, Macs, tablets and smartphones</li>
      <li>A service that works on low broadband or mobile connections</li>
      <li>Full and secure encryption of video sessions and data to ensure confidentiality</li>
    </ul>
    <p>This service is only available to people who are living in the UK.</p>
    <p>For more information please see our <a href="<?php echo COUNSELLING_FAQ_PATH ?>">Frequently Asked Questions</a> or contact <a href="mailto:online.community@sueryder.org">online.community@sueryder.org</a></p>
  </div>
  
  <div class="support-team">
    <h2>Our bereavement support team</h2>
    <div class="practitioners counsellors">
      <?php foreach ($counsellors as $id => $counsellor): ?>
        <div class="practitioner counsellor"><?php echo render($counsellor) ?></div>
      <?php endforeach ?>
    </div>
    <div class="practitioners volunteers">
      <?php foreach ($volunteers as $id => $volunteer): ?>
        <div class="practitioner volunteer"><?php echo render($volunteer) ?></div>
      <?php endforeach ?>
    </div>
  </div>

  <div class="form-actions form-wrapper" id="edit-actions">
    <div class="form-submit-wrapper">
      <a href="/community" class="form-submit back">Back</a>
    </div>
    <?php if (!$user_has_patient_profile && !$user_has_practitioner_profile): ?>
    <div class="form-submit-wrapper">
      <a href="/bereavement-counselling/register/your-details" class="form-submit">Register for video support</a>
    </div>
    <?php endif ?>
  </div>
</div>