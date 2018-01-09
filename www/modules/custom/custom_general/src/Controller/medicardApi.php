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
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\custom_general\Controller\medicardApi;

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
  public function view_patient($patient_id = NULL) {
    $data = medicardApi::get_patient();
    $patient = $data['patient'][$patient_id];

    $output = "";

    $output .= "<div class=''><div class='portlet green box'>";

    $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['lastname']) . "</div><div class='actions'>Registered: " . date("d-M-Y", $patient['created']) . "</div></div>";

    $output .= "<div class='portlet-body'>
      <div class='row static-info'>
        <div class='col-md-5 name'>Date Of Birth:</div><div class='col-md-7 value'> " . date("d-M-Y", $patient['dob']) . "</div>
        <div class='col-md-5 name'>Gender:</div><div class='col-md-7 value'> " . ucwords($patient['gender']) . "</div>
        <div class='col-md-5 name'>Address:</div><div class='col-md-7 value'> " . ucwords($patient['address']) . "</div>
        <div class='col-md-12 name'><p><h2>Vital signs</h2></p></div>
        <div class='col-md-5 name'>Temperature:</div><div class='col-md-7 value'> " . ucwords($patient['temp']) . "</div>
        <div class='col-md-5 name'>Pulse:</div><div class='col-md-7 value'> " . ucwords($patient['pulse']) . "</div>
        <div class='col-md-5 name'>Respirations/Breathing:</div><div class='col-md-7 value'> " . ucwords($patient['breathing']) . "</div>
        <div class='col-md-5 name'>Blood Pressure:</div><div class='col-md-7 value'> " . ucwords($patient['bp']) . "</div>
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

        if ($action == 'register' && $role == 'nurse') {
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

          $values = [
            'type' => 'patient',
            'uid' => 1,
            'title' => 'Patient---' . $data['firstname'] . ' ' . $data['firstname'] . \Drupal::time()->getRequestTime(),
            'field_first_name' => ['value' => $data['firstname']],
            'field_last_name' => ['value' => $data['lastname']],
            'field_date_of_birth' => ['value' => $data['dob']],
            'field_gender' => ['value' => $data['gender']],
            'field_patient_address' => ['value' => $data['address']],
            'field_temperature' => ['value' => $data['temp']],
            'field_pulse' => ['value' => $data['pulse']],
            'field_respirations_breathing' => ['value' => $data['breathing']],
            'field_blood_pressure' => ['value' => $data['bp']],
            'field_updates_track' => ['value' => $track],
          ];

          $node = Node::create($values);

        }
        else if ($action == 'update' && $role == 'nurse') {
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

  /**
   * Get view patient.
   */
  public function get_view_patient(Request $request) {
    $response = array();

    if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
      $data = json_decode($request->getContent(), TRUE);

      $secret = $request->headers->get('secret');
      $token = $request->headers->get('token');

      // Check for validation.
      $query_secret = \Drupal::database()->query("SELECT COUNT(*) FROM node_revision__field_secret_api WHERE field_secret_api_value = '" . $secret . "'")->fetchField();

      $query_token = \Drupal::database()->query("SELECT COUNT(*) FROM node__field_device_token WHERE field_device_token_value = '" . $token . "'")->fetchField();

      if ($query_secret > 0 && $query_token > 0) {

        $query = \Drupal::database()->query("SELECT nfd.nid AS nid FROM node_field_data AS nfd 
          WHERE nfd.type = 'patient' ORDER BY nfd.created DESC")->fetchAll();
        
        foreach ($query as $res) {
          $node = Node::load($res->nid);

          $response['patient'][$res->nid] = [
            'firstname' => $node->get('field_first_name')->value,
            'lastname' => $node->get('field_last_name')->value,
            'dob' => $node->get('field_date_of_birth')->value,
            'gender' => $node->get('field_gender')->value,
            'address' => $node->get('field_patient_address')->value,
            'temp' => $node->get('field_temperature')->value,
            'pulse' => $node->get('field_pulse')->value,
            'breathing' => $node->get('field_respirations_breathing')->value,
            'bp' => $node->get('field_blood_pressure')->value,
            'findings' => $node->get('field_findings')->value,
            'recommendation' => $node->get('field_recommendation')->value,
            'result' => $node->get('field_result')->value,
            'prescription' => $node->get('field_prescription')->value,
            'created' => $node->get('created')->value,
          ];
        }

        $response['status'] = 'success';
      }
      else {
        $response = ['status' => 'failed'];
      }
    }
    return new JsonResponse($response);
  }
  /**
   * Get patient data from main server.
   */
  public function get_patient() {
      try {
        $client = \Drupal::httpClient();
        $response = $client->post('http://192.168.254.124/api/patient/view', [
          'headers' => [
            'Content-Type' => 'application/json',
            'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
            'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
          ],
        ]);

        $data = json_decode($response->getBody(), TRUE);
        return $data;

      } catch (RequestException $e) {
        return false;
      }
  }
}
