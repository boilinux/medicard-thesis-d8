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
use Symfony\Component\HttpFoundation\Request;

class medicardApi extends ControllerBase {
  /**
   * Check user auth.
   */
  public function check_user_auth() {
    $uid = \Drupal::currentUser()->id();

    if ($uid > 0) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Check user auth nurse.
   */
  public function check_user_auth_nurse() {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid > 0 && in_array('nurse', $roles)) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Check user auth doctor.
   */
  public function check_user_auth_doctor() {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid > 0 && in_array('doctor', $roles)) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Check user auth pharmacist.
   */
  public function check_user_auth_pharmacist() {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid > 0 && in_array('pharmacist', $roles)) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
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

  /**
   * Update via post patient.
   */
  public function post_update_patient(Request $request) {
    $response = "";

    if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
      $data = json_decode($request->getContent(), TRUE);

      $secret = $request->headers->get('secret');
      $token = $request->headers->get('token');
      $nid = $request->headers->get('nid');
      $role = $request->headers->get('role');
      $action = $request->headers->get('action');
      $username = $request->headers->get('username');

      // Check for validation.
      $query_secret = \Drupal::database()->query("SELECT COUNT(*) FROM node_revision__field_secret_api WHERE field_secret_api_value = '" . $secret . "'")->fetchField();

      $query_token = \Drupal::database()->query("SELECT COUNT(*) FROM node__field_device_token WHERE field_device_token_value = '" . $token . "'")->fetchField();

      if ($query_secret > 0 && $query_token > 0) {

        $entity_id = \Drupal::database()->query("SELECT entity_id FROM node__field_device_token WHERE field_device_token_value = '" . $token . "'")->fetchField();

        if ($action == 'update' && $role == 'nurse') {
          $node = Node::load($nid);

          $node->field_first_name->value = $data['firstname'];
          $node->field_last_name->value = $data['lastname'];
          $node->field_date_of_birth->value = strtotime($data['dob']);
          $node->field_gender->value = $data['gender'];
          $node->field_patient_address->value = $data['address'];
          $node->field_temperature->value = $data['temp'];
          $node->field_pulse->value = $data['pulse'];
          $node->field_respirations_breathing->value = $data['breathing'];
          $node->field_blood_pressure->value = $data['bp'];

          // Tracking
          $track = "First Name: " . $data['firstname'] . "\n";
          $track .= "Last Name: " . $data['lastname'] . "\n";
          $track .= "Date Of Birth: " . date("d-M-Y", $data['dob']) . "\n";
          $track .= "Gender: " . $data['gender'] . "\n";
          $track .= "address: " . $data['address'] . "\n";
          $track .= "Temperature: " . $data['temp'] . "\n";
          $track .= "Pulse: " . $data['pulse'] . "\n";
          $track .= "Respirations/Breathing: " . $data['breathing'] . "\n";
          $track .= "Blood Pressure: " . $data['bp'] . "\n";
          $track .= "Created: " . date("d-M-Y H:i", \Drupal::time()->getRequestTime()) . "\n";
          $track .= "User: " . $username . "\n";
          $track .= "Role: Nurse";

          $node->field_updates_track->appendItem($track);

        }
        else if ($action == 'update' && $role == 'doctor') {
          $node = Node::load($nid);

          $node->field_first_name->value = $data['firstname'];
          $node->field_last_name->value = $data['lastname'];
          $node->field_date_of_birth->value = strtotime($data['dob']);
          $node->field_gender->value = $data['gender'];
          $node->field_patient_address->value = $data['address'];
          $node->field_temperature->value = $data['temp'];
          $node->field_pulse->value = $data['pulse'];
          $node->field_respirations_breathing->value = $data['breathing'];
          $node->field_blood_pressure->value = $data['bp'];

          $node->field_findings->value = $data['findings'];
          $node->field_recommendation->value = $data['recommendation'];
          $node->field_result->value = $data['result'];
          $node->field_prescription->value = $data['prescription'];

          // Tracking
          $track = "First Name: " . $data['firstname'] . "\n";
          $track .= "Last Name: " . $data['lastname'] . "\n";
          $track .= "Date Of Birth: " . date("d-M-Y", $data['dob']) . "\n";
          $track .= "Gender: " . $data['gender'] . "\n";
          $track .= "address: " . $data['address'] . "\n";
          $track .= "Temperature: " . $data['temp'] . "\n";
          $track .= "Pulse: " . $data['pulse'] . "\n";
          $track .= "Respirations/Breathing: " . $data['breathing'] . "\n";
          $track .= "Blood Pressure: " . $data['bp'] . "\n";

          $track .= "Findings: " . $data['findings'] . "\n";
          $track .= "Recommendation: " . $data['recommendation'] . "\n";
          $track .= "Result: " . $data['result'] . "\n";
          $track .= "Prescription: " . $data['prescription'] . "\n";

          $track .= "Created: " . date("d-M-Y H:i", \Drupal::time()->getRequestTime()) . "\n";
          $track .= "User: " . $username . "\n";
          $track .= "Role: Doctor";

          $node->field_updates_track->appendItem($track);
        }

        // Node data save.
        $node->save();

        $response = ['status' => 'success'];
      }
      else {
        $response = ['status' => 'faield'];
      }
    }

    return new JsonResponse($response);
  }
}
