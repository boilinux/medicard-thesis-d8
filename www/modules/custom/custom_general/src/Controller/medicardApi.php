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
   * get card id.
   */
  public function get_card_id() {
    $card = exec("sudo python " . $_SERVER['DOCUMENT_ROOT'] . "/insert_smartcard.py 0");

    return $card;
  }
  /**
   * check card id.
   */
  public function check_card_id($card_id = NULL) {
    $client = \Drupal::httpClient();

    $data['card_id'] = $card_id;

    $response = $client->post('http://192.168.10.123/api/patient/view/card_id', [
      'headers' => [
        'Content-Type' => 'application/json',
        'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
        'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
      ],
      'body' => json_encode($data),
    ]);

    $data = json_decode($response->getBody(), TRUE);

    if ($data['status'] == 'true') {
      return 'exist';
    }
    else if($data['status'] == 'false') {
      return 'not yet';
    }
    else {
      return 'failed';
    }
  }
  /**
   * post card id.
   */
  public function get_view_patient_card_id(Request $request) {
    $response = array();

    if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
      $data = json_decode($request->getContent(), TRUE);

      $secret = $request->headers->get('secret');
      $token = $request->headers->get('token');

      $card_id = $data['card_id'];

      // Check for validation.
      $query_secret = \Drupal::database()->query("SELECT COUNT(*) FROM node_revision__field_secret_api WHERE field_secret_api_value = '" . $secret . "'")->fetchField();

      $query_token = \Drupal::database()->query("SELECT COUNT(*) FROM node__field_device_token WHERE field_device_token_value = '" . $token . "'")->fetchField();

      if ($query_secret > 0 && $query_token > 0) {
        $query = \Drupal::database()->query("SELECT COUNT(*) FROM node__field_card_id WHERE field_card_id_value = '" . $card_id . "'")->fetchField();

        if ($query > 0) {
          $response = ['status' => 'true'];
        }
        else {
          $response = ['status' => 'false'];
        }
      }
      else {
        $response = ['status' => 'failed'];
      }
    }
    return new JsonResponse($response);
  }
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
   * Check user auth nurse register.
   */
  public function check_user_auth_nurse_register() {
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
   * Check user auth nurse.
   */
  public function check_user_auth_nurse($patient_id = NULL) {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid > 0 && in_array('nurse', $roles)) {
      $card = medicardApi::get_card_id();
      $card = str_replace(' ', '', $card);
      $status = medicardApi::check_card_id($card);

      $patient = medicardApi::get_patient();
      $data = str_replace(' ', '', $patient['patient'][$patient_id]['card_id']);

      if (empty($card) || $status == 'failed') {
        return AccessResult::forbidden();
      }
      else if(!empty($card) && $status == 'exist' && $data == $card) {
        return AccessResult::allowed();
      }
      else {
        return AccessResult::forbidden();
      }
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Check user auth doctor.
   */
  public function check_user_auth_doctor($patient_id = NULL) {
    $uid = \Drupal::currentUser()->id();
    $roles = \Drupal::currentUser()->getRoles();

    if ($uid > 0 && in_array('doctor', $roles)) {
      $card = medicardApi::get_card_id();
      $card = str_replace(' ', '', $card);
      $status = medicardApi::check_card_id($card);

      $patient = medicardApi::get_patient();
      $data = str_replace(' ', '', $patient['patient'][$patient_id]['card_id']);

      if (empty($card) || $status == 'failed') {
        return AccessResult::forbidden();
      }
      else if(!empty($card) && $status == 'exist' && $data == $card) {
        return AccessResult::allowed();
      }
      else {
        return AccessResult::forbidden();
      }
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
      $card = medicardApi::get_card_id();
      $card = str_replace(' ', '', $card);
      $status = medicardApi::check_card_id($card);

      if (empty($card) || $status == 'failed') {
        return AccessResult::forbidden();
      }
      else if(!empty($card) && $status == 'exist') {
        return AccessResult::allowed();
      }
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
   * set tracking for revision.
   */
  private function set_patient_revision($data = NULL, $role = NULL) {
    // Tracking
    $track = "";
    
    if ($role == 'nurse') {
      $track = "First Name: " . $data['firstname'] . "\n";
      $track .= "Middle Name: " . $data['middlename'] . "\n";
      $track .= "Last Name: " . $data['lastname'] . "\n";
      $track .= "Date Of Birth: " . date("d-M-Y", $data['dob']) . "\n";
      $track .= "Gender: " . $data['gender'] . "\n";
      $track .= "Status: " . $data['status'] . "\n";
      $track .= "Phone number: " . $data['phonenumber'] . "\n";
      $track .= "Email: " . $data['email'] . "\n";
      $track .= "Employer: " . $data['employer'] . "\n";
      $track .= "Company address: " . $data['companyaddress'] . "\n";
      $track .= "Immunization: " . implode(',', $data['immunization']) . "\n";
      $track .= "Laboratory test: " . implode(',', $data['labtest']) . "\n";
      $track .= "address: " . $data['address'] . "\n";
      $track .= "Temperature: " . $data['temp'] . "\n";
      $track .= "Pulse: " . $data['pulse'] . "\n";
      $track .= "Respirations/Breathing: " . $data['breathing'] . "\n";
      $track .= "Blood Pressure: " . $data['bp'] . "\n";
      $track .= "Created: " . date("d-M-Y H:i", \Drupal::time()->getRequestTime()) . "\n";
      $track .= "User: " . $data['username'] . "\n";
      $track .= "Role: Nurse";
    }
    else if ($role == 'doctor') {
      $track = "First Name: " . $data['firstname'] . "\n";
      $track .= "Middle Name: " . $data['middlename'] . "\n";
      $track .= "Last Name: " . $data['lastname'] . "\n";
      $track .= "Date Of Birth: " . date("d-M-Y", $data['dob']) . "\n";
      $track .= "Gender: " . $data['gender'] . "\n";
      $track .= "Status: " . $data['status'] . "\n";
      $track .= "Phone number: " . $data['phonenumber'] . "\n";
      $track .= "Email: " . $data['email'] . "\n";
      $track .= "Employer: " . $data['employer'] . "\n";
      $track .= "Company address: " . $data['companyaddress'] . "\n";
      $track .= "Immunization: " . implode(',', $data['immunization']) . "\n";
      $track .= "Laboratory test: " . implode(',', $data['labtest']) . "\n";
      $track .= "address: " . $data['address'] . "\n";
      $track .= "Temperature: " . $data['temp'] . "\n";
      $track .= "Pulse: " . $data['pulse'] . "\n";
      $track .= "Respirations/Breathing: " . $data['breathing'] . "\n";
      $track .= "Blood Pressure: " . $data['bp'] . "\n";
      $track .= "Findings: " . implode(',', $data['findings']) . "\n";
      $track .= "Recommendation: " . implode(',', $data['recommendation']) . "\n";
      $track .= "Result: " . implode(',', $data['result']) . "\n";
      $track .= "Prescription: " . implode(',', $data['prescription']) . "\n";
      $track .= "Created: " . date("d-M-Y H:i", \Drupal::time()->getRequestTime()) . "\n";
      $track .= "User: " . $data['username'] . "\n";
      $track .= "Role: Doctor";
    }

    return $track;
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
          $track = medicardApi::set_patient_revision($data, 'nurse');

          $values = [
            'type' => 'patient',
            'uid' => 1,
            'title' => 'Patient---' . $data['firstname'] . ' ' . $data['lastname'] . "-" . \Drupal::time()->getRequestTime(),
            'field_card_id' => ['value' => $data['card_id']],
            'field_first_name' => ['value' => $data['firstname']],
            'field_middle_name' => ['value' => $data['middlename']],
            'field_last_name' => ['value' => $data['lastname']],
            'field_date_of_birth' => ['value' => strtotime($data['dob'])],
            'field_gender' => ['value' => $data['gender']],
            'field_patient_status' => ['value' => $data['status']],
            'field_phone_number' => ['value' => $data['phonenumber']],
            'field_patient_email' => ['value' => $data['email']],
            'field_patient_employer' => ['value' => $data['employer']],
            'field_company_address' => ['value' => $data['companyaddress']],
            'field_immunizations' => ['value' => $data['immunization']],
            'field_laboratory_test' => ['value' => $data['labtest']],
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
          $node->field_middle_name->value = $data['middlename'];
          $node->field_last_name->value = $data['lastname'];
          $node->field_date_of_birth->value = strtotime($data['dob']);
          $node->field_gender->value = $data['gender'];
          $node->field_patient_status->value = $data['status'];
          $node->field_phone_number->value = $data['phonenumber'];
          $node->field_patient_email->value = $data['email'];
          $node->field_patient_employer->value = $data['employer'];
          $node->field_company_address->value = $data['companyaddress'];
          $node->field_immunizations->value = $data['immunization'];
          $node->field_laboratory_test->value = $data['labtest'];
          $node->field_patient_address->value = $data['address'];
          $node->field_temperature->value = $data['temp'];
          $node->field_pulse->value = $data['pulse'];
          $node->field_respirations_breathing->value = $data['breathing'];
          $node->field_blood_pressure->value = $data['bp'];

          $track = medicardApi::set_patient_revision($data, 'nurse');

          $node->field_updates_track->appendItem($track);

        }
        else if ($action == 'update' && $role == 'doctor') {
          $node = Node::load($nid);

          $node->field_findings->appendItem($data['findings']);
          $node->field_recommendation->appendItem($data['recommendation']);
          $node->field_result->appendItem($data['result']);
          $node->field_prescription->appendItem($data['prescription']);

          $track = medicardApi::set_patient_revision($data, 'doctor');

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
            'middlename' => $node->get('field_middle_name')->value,
            'lastname' => $node->get('field_last_name')->value,
            'dob' => $node->get('field_date_of_birth')->value,
            'gender' => $node->get('field_gender')->value,
            'status' => $node->get('field_patient_status')->value,
            'phonenumber' => $node->get('field_phone_number')->value,
            'email' => $node->get('field_patient_email')->value,
            'employer' => $node->get('field_patient_employer')->value,
            'companyaddress' => $node->get('field_company_address')->value,
            'immunization' => $node->get('field_immunizations')->getValue(),
            'labtest' => $node->get('field_laboratory_test')->getValue(),
            'address' => $node->get('field_patient_address')->value,
            'temp' => $node->get('field_temperature')->value,
            'pulse' => $node->get('field_pulse')->value,
            'breathing' => $node->get('field_respirations_breathing')->value,
            'bp' => $node->get('field_blood_pressure')->value,
            'findings' => $node->get('field_findings')->getValue(),
            'recommendation' => $node->get('field_recommendation')->getValue(),
            'result' => $node->get('field_result')->getValue(),
            'prescription' => $node->get('field_prescription')->getValue(),
            'created' => $node->get('created')->value,
            'card_id' => $node->get('field_card_id')->value,
            'revision' => $node->get('field_updates_track')->getValue(),
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
        $response = $client->post('http://192.168.10.123/api/patient/view', [
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
