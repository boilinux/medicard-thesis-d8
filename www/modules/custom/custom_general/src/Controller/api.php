<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\custom_general\Controller\apiHelper;

class api extends ControllerBase {
  /**
   * Check user auth.
   */
  public function check_user_auth() {
    $uid = \Drupal::currentUser()->id();

    if ($uid == 0) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }

  /**
   * Create data.
   */
  public function data_create() {
    
    $data = array(
      'current' => isset($_REQUEST['current']) && !empty($_REQUEST['current']) ? $_REQUEST['current'] : '',
      'level' => isset($_REQUEST['level']) && !empty($_REQUEST['level']) ? $_REQUEST['level'] : '',
      'warning' => isset($_REQUEST['warning']) && !empty($_REQUEST['warning']) ? $_REQUEST['warning'] : '',
    );

    $entity_id = \Drupal::database()->query("SELECT entity_id FROM node__field_device_token WHERE field_device_token_value = '" . $_REQUEST['token'] . "'")->fetchField();

    $values = array(
      'type' => 'project_data',
      'uid' => 1,
      'title' => $_REQUEST['token'] . '---' . \Drupal::time()->getRequestTime(),
      'field_flood_current' => array(
        'value' => number_format($data['current'], 4, '.', ''),
      ),
      'field_flood_level' => array(
        'value' => $data['level'],
      ),
      'field_flood_warning' => array(
        'value' => $data['warning'],
      ),
    );

    if (!empty($entity_id)) {
      $values['field_reference_device'] = array(
        'target_id' => $entity_id,
      );
    }

    // Send SMS notification via Nexmo API SMS.
    // Check for mobile number if exist.
    $query = \Drupal::database()->query("SELECT num.field_mobile_number_value AS number, txt.field_mobile_text_format_value AS mobile_format FROM node__field_device_token AS token 
      LEFT JOIN node__field_mobile_number AS num ON num.entity_id = token.entity_id
      LEFT JOIN node__field_mobile_text_format AS txt ON txt.entity_id = token.entity_id
      WHERE token.field_device_token_value = '" . $_REQUEST['token'] . "'")->fetchAll();

    foreach ($query as $mobile_number) {
      $mobile_text = $mobile_number->mobile_format;

      $mobile_text = str_replace('%flood_level', $data['level'], $mobile_text);
      $mobile_text = str_replace('%flood_current', number_format($data['current'], 4, '.', ''), $mobile_text);

      $client = \Drupal::httpClient();
      $request = $client->post('https://rest.nexmo.com/sms/json', [
        'json' => [
          'api_key'=> 'key',
          'api_secret' => 'secret',
          'to' => $mobile_number->number,
          'from' => 'SEPFloWMS',
          'text' => $mobile_text,
        ]
      ]);
      
      $values['field_sms_return_data'][] = array(
        'value' => $request->getBody(),
      );
    }

    // Node data save.
    $node = Node::create($values);
    $node->save();

    return array('#markup' => 'Successfully Created data.');
  }

  /**
   * Insert credit.
   */
  public function data_create_credit() {
    $entity_id = \Drupal::database()->query("SELECT entity_id FROM node__field_device_token WHERE field_device_token_value = '" . $_REQUEST['token'] . "'")->fetchField();

    $uid = isset($_REQUEST['uid']) && !empty($_REQUEST['uid']) ? $_REQUEST['uid'] : \Drupal::currentUser()->id();
    $amount = $_REQUEST['amount'];

    $values = array(
      'type' => 'user_credit',
      'uid' => $uid,
      'title' => "credit---" . $uid . "---" . $amount . "---" . \Drupal::time()->getRequestTime(),
      'field_amount' => array(
        'value' => $amount,
      ),
    );

    if (!empty($entity_id)) {
      $values['field_reference_device'] = array(
        'target_id' => $entity_id,
      );
    }

    // Node data save.
    $node = Node::create($values);
    $node->save();

    // Crate activity message.
    $values = array(
      'type' => 'activity',
      'uid' => $uid,
      'title' => "activity---" . $uid . "---credit---" . \Drupal::time()->getRequestTime(),
      'body' => array(
        'value' => "Thank you for loading amount of " . $amount . " peso(s).",
      ),
    );

    // Node data save.
    $node = Node::create($values);
    $node->save();

    return array('#markup' => 'Success');
  }

  /**
   * check token.
   */
  public function authorize_token() {
    $token = '';
    // check for token if existing
    if (isset($_REQUEST['token']) && !empty($_REQUEST['token'])) {
      $token = $_REQUEST['token'];
    }

    $cnt_token = \Drupal::database()->query("SELECT COUNT(*) FROM node__field_device_token WHERE field_device_token_value = '" . $token . "'")->fetchField();

    if ($cnt_token == 0) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }

  // test page
  public function test_page() {
    $uid = \Drupal::currentUser()->id();

    drupal_set_message($_SERVER['DOCUMENT_ROOT']);

    $output = exec("python " . $_SERVER['DOCUMENT_ROOT'] . "/python/insert_coin.py " . $uid);

    return array('#markup' => $output);
  }

  /**
   * get user credit points.
   */
  public function user_credit_points() {
    $credit = 0;

    $uid = \Drupal::currentUser()->id();

    $query = \Drupal::database()->query("SELECT fa.field_amount_value AS amount FROM node_field_data AS n
      LEFT JOIN node__field_amount AS fa ON fa.bundle = n.type && fa.entity_id = n.nid
      WHERE n.uid = " . $uid . " AND n.type = 'user_credit'")->fetchAll();

    foreach ($query as $res) {
      $credit += $res->amount;
    }

    return $credit > 0 ? $credit : 0;
  }

  /**
   * get user credit points.
   */
  public function user_credit_deduct_points() {
    $deduct_credit = 0;
    
    $uid = \Drupal::currentUser()->id();

    $query = \Drupal::database()->query("SELECT fa.field_amount_value AS amount FROM node_field_data AS n
      LEFT JOIN node__field_amount AS fa ON fa.bundle = n.type && fa.entity_id = n.nid
      WHERE n.uid = " . $uid . " AND n.type = 'credit_out'")->fetchAll();

    foreach ($query as $res) {
      $deduct_credit += $res->amount;
    }

    return $deduct_credit > 0 ? $deduct_credit : 0;
  }

  /**
   * Total credits.
   */
  public function user_total_credits() {
    return api::user_credit_points() - api::user_credit_deduct_points();
  }

  /**
   * Print me this file.
   */
  public function print_me($direct_fid = '') {
    $fid = $_REQUEST['target_id'];

    if (!empty($direct_fid)) {
      $fid = $direct_fid;
    }

    // check the file if exist.
    if (\Drupal::database()->query("SELECT COUNT(f.fid) FROM file_managed AS f WHERE f.fid = " . $fid . " AND f.uid = " . \Drupal::currentUser()->id())->fetchField() == 0) {
      drupal_set_message("The document does not exist. Please contact the administrator of this machine.", 'error');

      $response = new \Symfony\Component\HttpFoundation\RedirectResponse("/user/my-prints");
      $response->send();

      exit();
    }

    $page_count = \Drupal::database()->query("SELECT fdp.field_doc_pages_value AS pages FROM node__field_documents AS fd
      LEFT JOIN node__field_doc_pages AS fdp ON fdp.entity_id = fd.entity_id
      WHERE fd.field_documents_target_id = " . $fid)->fetchField();

    $amount = $page_count * 2; // 2 pesos for each print.

    if (api::user_total_credits() >= $amount) {

      $file_path = $_SERVER['DOCUMENT_ROOT'] . "/sites/default/files/";
      $file = File::load($fid);

      $file_uri = str_replace('public://', '', $file->getFileUri());
      $file_uri = str_replace(" ", "\ ", $file_uri);

      $file_path = $file_path . $file_uri;

      // Print the file.
      // exec("/bin/bash " . $_SERVER['DOCUMENT_ROOT'] . "/print_me.sh " . $file_path . " > /dev/null 2>&1 &");
      exec("sudo unoconv --stdout " . $file_path . " | lpr -P EPSON_L120_Series");

      // Deduct credit.
      $values = array(
        'type' => 'credit_out',
        'uid' => \Drupal::currentUser()->id(),
        'title' => "credit-out---" . $uid . "---" . $amount . "---" . \Drupal::time()->getRequestTime(),
        'field_amount' => array(
          'value' => $amount,
        ),
      );

      // Get the nid from the file.
      $entity_id = \Drupal::database()->query("SELECT d.entity_id AS nid FROM node__field_documents AS d
        WHERE d.field_documents_target_id = " . $fid)->fetchField();
      if (!empty($entity_id)) {
        $values['field_print_id'] = array(
          'target_id' => $entity_id,
        );
      }

      // Node data save.
      $node = Node::create($values);
      $node->save();

      drupal_set_message("₱" . $amount . " was deducted to your credit.");

      drupal_set_message("Your documents is being process for printing.");
    }
    else {
      drupal_set_message("Insufficient credit. Please Topup new credit.", "error");
    }


    $response = new \Symfony\Component\HttpFoundation\RedirectResponse("/user/my-prints");
    $response->send();
  }

  /**
   * Get page count of a file.
   */
  public function get_page_file_count($ext, $file_path) {
    $page = 0;

    $file_path = str_replace(" ", "\ ", $file_path);

    if ($ext == 'ppt') {
      $page = exec("wvSummary " . $file_path . " | grep -oP '(?<=of Slides = )[ A-Za-z0-9]*'");
    }
    else if ($ext == 'pptx') {
      $page = exec("unzip -p " . $file_path . " docProps/app.xml | grep -oP '(?<=\<Slides\>).*(?=\</Slides\>)'");
    }
    // Doc
    else if ($ext == 'doc') {
      $page = exec("wvSummary " . $file_path . " | grep -oP '(?<=of Pages = )[ A-Za-z0-9]*'");
    }
    // Docx
    else if ($ext == 'docx') {
      $page = exec("unzip -p " . $file_path . " docProps/app.xml | grep -oP '(?<=\<Pages\>).*(?=\</Pages\>)'");
    }
    // PDF
    else if ($ext == 'pdf') {
      $page = exec("pdfinfo " . $file_path . " | grep -oP '(?<=Pages:          )[ A-Za-z0-9]*'");
    }
    // ODT
    else if ($ext == 'odt') {
      $page = exec('unzip -p ' . $file_path . ' meta.xml | grep -oP \'(?<=page-count=")[ A-Za-z0-9]*\'');
    }
    // Pictures
    else if (strtolower($ext) == 'png' || strtolower($ext) == 'jpg') {
      $page = 1;
    }

    return $page;
  }

  /**
   * User send request for credit.
   */
  public function user_insert_credit() {
    $message = "";
    $response = new AjaxResponse();

    $uid = \Drupal::currentUser()->id();

    $check_python_running = exec("ps -e | grep python");
    if (empty($check_python_running)) {
      // $output = exec("python " . $_SERVER['DOCUMENT_ROOT'] . "/python/insert_coin.py " . $uid . " &");

      // Run shell script to run the coin_insert.py.
      exec("/bin/bash " . $_SERVER['DOCUMENT_ROOT'] . "/run_insert_coin.sh " . $uid . " > /dev/null 2>&1 &");

      $message = "
        <div id='credit-ajax-response'><span class='message' style='display:none;'>Please insert coin now.</span></div>
      ";
    }
    else {
      $message = "<div id='credit-ajax-response'><span class='error_message' style='display:none;'>The machine is busy. Please try again later.</span></div>";
    }

    return $response->addCommand(new ReplaceCommand(
        '#credit-ajax-response',
        $message)
    );
  }

  /**
   * Transaction history
   */
  public function transaction_history() {
    $output = "";

    return array('#markup' => $output);
  }
}
