<?php

/**
 * Implements hook_menu().
 */
function counselling_menu() {
  $items = [];
 
  
  // Patient routes
  
  $items['bereavement-counselling'] = [
    'title' => 'Bereavement counselling service',
    'page callback' => '\Drupal\\counselling\\Controller\\BereavementCounselling::build',
    'access arguments' => ['create patient profile'],
    'type' => MENU_CALLBACK,
  ];
  
  $items['bereavement-counselling/thank-you/%'] = [
    'title' => 'Thank you',
    'title arguments' => [2],
    'page callback' => '\Drupal\\counselling\\Controller\\ThankYou::build',
    'page arguments' => [2],
    'access arguments' => ['create patient profile'],
    'type' => MENU_CALLBACK,
  ];

  $items['bereavement-counselling/register/your-details'] = [
    'title' => 'Register for online bereavement counselling service',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientRegistration::details',
    'access callback' => 'counselling_booking_access',
    'access arguments' => [1],
    'type' => MENU_CALLBACK,
    'file' => 'src/Form/patient_register_form.php',
  ];
  
  $items['bereavement-counselling/register/your-assessment'] = [
    'title' => 'Book your appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientRegistration::assessment',
    'access callback' => 'counselling_booking_access',
    'access arguments' => [1],
    'type' => MENU_CALLBACK,
    'file' => 'src/Form/patient_booking_form.php',
  ];
  
  $items['bereavement-counselling/register/confirm'] = [
    'title' => 'Confirm your assessment booking',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientRegistration::confirm',
    'access callback' => 'counselling_booking_access',
    'access arguments' => [1],
    'type' => MENU_CALLBACK,
    'file' => 'src/Form/patient_confirm_form.php',
  ];
  
  $items['bereavement-counselling/book/new'] = [
    'title' => 'Book your appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientBooking::book',
    'access callback' => 'counselling_booking_access',
    'access arguments' => [1],
    'type' => MENU_CALLBACK,
    'file' => 'src/Form/patient_booking_form.php',
  ];
  
  $items['bereavement-counselling/book/confirm'] = [
    'title' => 'Confirm your booking',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientBooking::confirm',
    'access callback' => 'counselling_booking_access',
    'access arguments' => [1],
    'type' => MENU_CALLBACK,
    'file' => 'src/Form/patient_confirm_form.php',
  ];
  
  $items['bereavement-counselling/booking-confirmation'] = [
    'title' => 'Your video support session has been booked',
    'title callback' => 'counselling_booking_confirmation_title',
    'page callback' => '\Drupal\\counselling\\Controller\\BookingConfirmation::build',
    'access arguments' => ['create patient profile'],
    'type' => MENU_CALLBACK,
  ];
  
  $items['user/%user/counselling/patient'] = [
    'title' => 'Counselling',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientProfile::redirect',
    'page arguments' => [1],
    'access callback' => 'patient_user_access',
    'access arguments' => ['view', 1],
    'file' => 'src/Form/patient_manage_form.php',
    'type' => MENU_LOCAL_TASK,
    'weight' => 99,
  ];
  
  $items['user/%user/counselling/patient/appointments'] = [
    'title' => 'Appointments',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientProfile::appointments',
    'page arguments' => [1],
    'access callback' => 'patient_user_access',
    'access arguments' => ['appointments', 1],
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => 0,
  ];
  
  $items['user/%user/counselling/patient/book-appointment'] = [
    'title' => 'Book Appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientProfile::book',
    'page arguments' => [1],
    'access callback' => 'patient_user_access',
    'access arguments' => ['book', 1],
    'type' => MENU_LOCAL_TASK,
    'weight' => 1,
  ];
  
  $items['user/%user/counselling/patient/view'] = [
    'title' => 'View profile',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientProfile::view',
    'page arguments' => [1],
    'access callback' => 'patient_user_access',
    'access arguments' => ['view', 1],
    'type' => MENU_LOCAL_TASK,
    'weight' => 2,
  ];
  
  $items['user/%user/counselling/patient/edit'] = [
    'title' => 'Edit profile',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientProfile::edit',
    'page arguments' => [1],
    'access callback' => 'patient_user_access',
    'access arguments' => ['edit', 1],
    'file' => 'src/Form/patient_manage_form.php',
    'type' => MENU_LOCAL_TASK,
    'weight' => 3,
  ];
  
  $items['user/%user/counselling/disable'] = [
    'title' => 'Leave counselling service',
    'page callback' => '\Drupal\\counselling\\Controller\\LeaveService::build',
    'page arguments' => [1],
    'access callback' => 'patient_user_access',
    'access arguments' => ['leave', 1],
    'type' => MENU_CALLBACK,
    'file' => 'src/Form/counselling_leave_form.php',
  ];
  
  $items['user/%user/counselling/patient/enable'] = [
    'title' => 'Re-enable profile',
    'page callback' => '\Drupal\\counselling\\Controller\\PatientProfile::enable',
    'page arguments' => [1],
    'access callback' => 'patient_user_access',
    'access arguments' => ['enable', 1],
    'file' => 'src/Form/patient_enable_form.php',
    'type' => MENU_CALLBACK,
    'weight' => 3,
  ];
  
  // Admin routes
  
  $items['admin/counselling'] = [
    'title' => t('Counselling'),
    'page callback' => '\Drupal\\counselling\\Controller\\AdminPages::redirect',
    'type' => MENU_NORMAL_ITEM,
    'access arguments' => ['edit any appointment'],
  ];
  
  $items['admin/counselling/appointments'] = [
    'title' => t('Appointments'),
    'page callback' => '\Drupal\\counselling\\Controller\\AdminPages::appointments',
    'type' => MENU_LOCAL_TASK,
    'access arguments' => ['edit any appointment'],
    'weight' => 0,
  ];
  
  $items['admin/counselling/practitioners'] = [
    'title' => t('Practitioners'),
    'page callback' => '\Drupal\\counselling\\Controller\\AdminPages::practitioners',
    'type' => MENU_LOCAL_TASK,
    'access arguments' => ['edit any practitioner profile'],
    'weight' => 1,
  ];
  
  $items['admin/counselling/patients'] = [
    'title' => t('Patients'),
    'page callback' => '\Drupal\\counselling\\Controller\\AdminPages::patients',
    'type' => MENU_LOCAL_TASK,
    'access arguments' => ['view any patient profile'],
    'weight' => 2,
  ];
  
  $items['admin/counselling/settings'] = [
    'title' => t('Settings'),
    'page callback' => '\Drupal\\counselling\\Controller\\AdminPages::settings',
    'type' => MENU_LOCAL_TASK,
    'access arguments' => ['administer appointment'],
    'weight' => 3,
    'file' => 'src/Form/counselling_settings_form.php',
  ];
  
  $items['admin/counselling/email-test'] = [
    'title' => t('Test email'),
    'page callback' => 'drupal_get_form',
    'page arguments' => ['counselling_email_test_form'],
    'type' => MENU_LOCAL_TASK,
    'access arguments' => ['administer appointment'],
    'weight' => 4,
    'file' => 'src/Form/counselling_email_test_form.php',
  ];
  
  $items['admin/content/appointment/%appointment/cancel'] = [
    'type' => MENU_CALLBACK,
    'title' => 'Cancel Appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentCancel::admin',
    'page arguments' => [3],
    'access callback' => 'appointment_access',
    'access arguments' => ['cancel', 3],
    'file' => 'src/Form/appointment_cancel_form.php',
  ];
  
  $items['admin/content/appointment/%appointment/assign'] = [
    'type' => MENU_CALLBACK,
    'title' => 'Assign patient',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentAssign::build',
    'page arguments' => [3],
    'access callback' => 'appointment_access',
    'access arguments' => ['assign', 3],
    'file' => 'src/Form/appointment_assign_form.php',
  ];
  
  
  // Practitioner routes
  
  $items['user/%user/counselling/practitioner'] = [
    'title' => 'Counselling',
    'page callback' => '\Drupal\\counselling\\Controller\\PractitionerProfile::redirect',
    'page arguments' => [1],
    'access arguments' => ['view own practitioner profile'],
    'file' => 'src/Form/practitioner_manage_form.php',
    'type' => MENU_LOCAL_TASK,
    'weight' => 99,
  ];
  
  $items['user/%user/counselling/practitioner/appointments'] = [
    'title' => 'Appointments',
    'page callback' => '\Drupal\\counselling\\Controller\\PractitionerProfile::appointments',
    'page arguments' => [1],
    'access arguments' => ['create appointment'],
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => 0,
  ];
  
  $items['user/%user/counselling/practitioner/create-appointment'] = [
    'title' => 'Add appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentCreate::build',
    'page arguments' => [1],
    'access arguments' => ['create appointment'],
    'type' => MENU_LOCAL_TASK,
    'weight' => 1,
  ];
  
  $items['user/%user/counselling/practitioner/view'] = [
    'title' => 'View profile',
    'page callback' => '\Drupal\\counselling\\Controller\\PractitionerProfile::view',
    'page arguments' => [1],
    'access callback' => 'practitioner_user_access',
    'access arguments' => ['view', 1],
    'type' => MENU_LOCAL_TASK,
    'file' => 'src/Form/practitioner_manage_form.php',
    'weight' => 2,
  ];
  
  $items['user/%user/counselling/practitioner/edit'] = [
    'title' => 'Edit profile',
    'page callback' => '\Drupal\\counselling\\Controller\\PractitionerProfile::edit',
    'page arguments' => [1],
    'access callback' => 'practitioner_user_access',
    'access arguments' => ['edit', 1],
    'file' => 'src/Form/practitioner_manage_form.php',
    'type' => MENU_LOCAL_TASK,
    'weight' => 3,
  ];
  
  $items['user/%user/counselling/practitioner/create'] = [
    'title' => 'Create',
    'type' => MENU_CALLBACK,
    'page callback' => '\Drupal\\counselling\\Controller\\PractitionerProfile::create',
    'page arguments' => [1],
    'access callback' => 'practitioner_user_access',
    'access arguments' => ['create', 1],
    'file' => 'src/Form/practitioner_manage_form.php',
  ];
  
  // Appointment routes
  
  $items['appointment/%appointment/cancel'] = [
    'type' => MENU_CALLBACK,
    'title' => 'Cancel Appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentCancel::build',
    'page arguments' => [1],
    'access callback' => 'appointment_access',
    'access arguments' => ['cancel', 1],
    'file' => 'src/Form/appointment_cancel_form.php',
  ];
  
  $items['appointment/%appointment/update'] = [
    'type' => MENU_CALLBACK,
    'title' => 'Update Appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentUpdate::build',
    'page arguments' => [1],
    'access callback' => 'appointment_access',
    'access arguments' => ['update', 1],
    'file' => 'src/Form/appointment_update_form.php',
  ];
  
  $items['appointment/%appointment/feedback'] = [
    'type' => MENU_CALLBACK,
    'title' => 'Leave feedback',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentFeedback::build',
    'page arguments' => [1],
    'access callback' => 'appointment_access',
    'access arguments' => ['feedback', 1],
    'file' => 'src/Form/appointment_feedback_form.php',
  ];
  
  $items['appointment/%appointment/change'] = [
    'type' => MENU_CALLBACK,
    'title' => 'Change appointment',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentChange::build',
    'page arguments' => [1],
    'access callback' => 'appointment_access',
    'access arguments' => ['change', 1],
    'file' => 'src/Form/appointment_change_form.php',
  ];
  
  $items['appointment/%appointment/room'] = [
    'type' => MENU_CALLBACK,
    'title' => 'Room',
    'page callback' => '\Drupal\\counselling\\Controller\\AppointmentRoom::build',
    'page arguments' => [1],
    'access callback' => 'appointment_access',
    'access arguments' => ['room', 1],
  ];
  
  // Entity public routes
  
  $items['bereavement-counselling/practitioner/%practitioner'] = [
    'type' => MENU_CALLBACK,
    'title callback' => 'counselling_profile_title',
    'title arguments' => [1,2],
    'page callback' => '\Drupal\\counselling\\Controller\\PractitionerPublic::build',
    'page arguments' => [2],
    'access arguments' => ['view any practitioner badge'],
  ];
  
  $items['bereavement-counselling/patient/%patient'] = [
    'type' => MENU_CALLBACK,
    'title callback' => 'counselling_profile_title',
    'title arguments' => [1,2],
    'page callback' => '\Drupal\\counselling\\Controller\\PatientPublic::build',
    'page arguments' => [2],
    'access arguments' => ['view any patient profile'],
  ];
  
  return $items;
}