<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\node\NodeInterface;

class medicardApi extends ControllerBase {
  /**
   * Check user auth nurse.
   */
  public function check_user_auth_nurse() {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid == 0 && in_array('nurse', $roles)) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }

  /**
   * Check user auth doctor.
   */
  public function check_user_auth_doctor() {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid == 0 && in_array('doctor', $roles)) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }

  /**
   * Check user auth pharmacist.
   */
  public function check_user_auth_pharmacist() {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid == 0 && in_array('pharmacist', $roles)) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }

  /**
   * View patient record.
   */
  public function view_patient(NodeInterface $patient) {
    $output = "";

    $output .= "<div class=''><div class='portlet green box'>";

    $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($patient->get('field_first_name')->value) . " " . ucwords($patient->get('field_last_name')->value) . "</div><div class='actions'>Registered: " . date("d-M-Y", $patient->get('created')->value) . "</div></div>";

    $output .= "<div class='portlet-body'>
      <div class='row static-info'>
        <div class='col-md-5 name'>Date Of Birth:</div><div class='col-md-7 value'> " . date("d-M-Y", $patient->get('field_date_of_birth')->value) . "</div>
        <div class='col-md-5 name'>Gender:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_gender')->value) . "</div>
        <div class='col-md-5 name'>Address:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_patient_address')->value) . "</div>
        <div class='col-md-12 name'><p><h2>Vital signs</h2></p></div>
        <div class='col-md-5 name'>Temperature:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_temperature')->value) . "</div>
        <div class='col-md-5 name'>Pulse:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_pulse')->value) . "</div>
        <div class='col-md-5 name'>Respirations/Breathing:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_respirations_breathing')->value) . "</div>
        <div class='col-md-5 name'>Blood Pressure:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_blood_pressure')->value) . "</div>
      </div>
      <div class='row static-info'>
      </div>
    </div>";

    return array('#markup' => $output, '#cache' => ['max-age' => 0,]);
  }
}
