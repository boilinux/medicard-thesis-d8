<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\custom_general\Controller\medicardApi;

class updateDoctorPatient extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_doctor_patient';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $patient_id = NULL) {
    $patient = medicardApi::get_patient();
    $patient = $patient['patient'][$patient_id];

    $output = "";

    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $patient_id,
    );
    $form['fullname'] = array(
      '#type' => 'hidden',
      '#value' => ucwords($patient['firstname']) . " " . ucwords($patient['lastname']),
    );

    $output .= "<div class='portlet box green'>
      <div class='portlet-title'>
        <div class='caption'><i class='fa fa-upload'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['middlename']) . " " . ucwords($patient['lastname']) . "</div>
      </div>
      <div class='portlet-body'><div class='portlet-body'>
      <div class='row static-info'>
        <div class='col-md-5 name'>Date Of Birth:</div><div class='col-md-7 value'> " . date("d-M-Y", $patient['dob']) . "</div>
        <div class='col-md-5 name'>Gender:</div><div class='col-md-7 value'> " . ucwords($patient['gender']) . "</div>
        <div class='col-md-5 name'>Status:</div><div class='col-md-7 value'> " . ucwords($patient['status']) . "</div>
        <div class='col-md-5 name'>Phone number:</div><div class='col-md-7 value'> " . ucwords($patient['phonenumber']) . "</div>
        <div class='col-md-5 name'>Email:</div><div class='col-md-7 value'> " . ucwords($patient['email']) . "</div>
        <div class='col-md-5 name'>Address:</div><div class='col-md-7 value'> " . ucwords($patient['address']) . "</div>
        <div class='col-md-5 name'>Employer:</div><div class='col-md-7 value'> " . ucwords($patient['employer']) . "</div>
        <div class='col-md-5 name'>Company address:</div><div class='col-md-7 value'> " . ucwords($patient['companyaddress']) . "</div>
        <div class='col-md-5 name'>Immunization:</div><div class='col-md-7 value'> " . ucwords($patient['immunization']) . "</div>
        <div class='col-md-5 name'>Laboratory test:</div><div class='col-md-7 value'> " . ucwords($patient['labtest']) . "</div>
        <div class='col-md-12 name'><p><h2>Vital signs</h2></p></div>
        <div class='col-md-5 name'>Temperature:</div><div class='col-md-7 value'> " . ucwords($patient['temp']) . "</div>
        <div class='col-md-5 name'>Pulse:</div><div class='col-md-7 value'> " . ucwords($patient['pulse']) . "</div>
        <div class='col-md-5 name'>Respirations/Breathing:</div><div class='col-md-7 value'> " . ucwords($patient['breathing']) . "</div>
        <div class='col-md-5 name'>Blood Pressure:</div><div class='col-md-7 value'> " . ucwords($patient['bp']) . "</div>
      </div>
      <div class='row static-info'>
      </div>
    </div>";

    $form['findings'] = array(
      '#type' => 'text_format',
      '#format' => 'doctor_text',
      '#default_value' => $patient['findings'],
      '#required' => TRUE,
      '#prefix' => $output . '<div class="portlet box red">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Findings</div>
      </div>
      <div class="portlet-body">',
      '#suffix' => '</div></div>',
    );

    $form['recommendation'] = array(
      '#type' => 'text_format',
      '#format' => 'doctor_text',
      '#default_value' => $patient['recommendation'],
      '#required' => TRUE,
      '#prefix' => '<div class="portlet box red">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Recommendation</div>
      </div>
      <div class="portlet-body">',
      '#suffix' => '</div></div>',
    );

    $form['result'] = array(
      '#type' => 'text_format',
      '#format' => 'doctor_text',
      '#default_value' => $patient['result'],
      '#required' => TRUE,
      '#prefix' => '<div class="portlet box red">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Result</div>
      </div>
      <div class="portlet-body">',
      '#suffix' => '</div></div>',
    );


    // Doctor's prescription
    $query = \Drupal::database()->query("SELECT nfd.nid AS nid FROM node_field_data AS nfd 
          WHERE nfd.type = 'medicine' ORDER BY nfd.created DESC")->fetchAll();

    $medicines = [];
    $output2 = "<p>Description:</p><ul class='desc-medicine'>";
    foreach ($query as $res) {
      $node = Node::load($res->nid);

      $medicines["medicine-" . $res->nid] = $node->get('title')->value;
      
      $output2 .= "<li data-nid='" . $res->nid . "' class='medicine-" . $res->nid . "'><p>" . $node->get('body')->value . "</p></li>";
    }
    $output2 .= "</ul>";

    $form['medicine'] = [
      '#type' => 'select',
      '#title' => 'Choose a medicine',
      '#options' => $medicines,
    ];
    $form['medicine']['#prefix'] = '<div class="portlet box green">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Prescription</div>
      </div>
      <div class="portlet-body">' . $output2;

    $form['medicine_quantity'] = [
      '#type' => 'textfield',
      '#title' => 'Quantity',
      '#default_value' => 1,
    ];

    $form['medicine_comment'] = [
      '#type' => 'textfield',
      '#title' => 'Dosage',
      '#suffix' => "<p><a id='add_medicine' href='#' class='btn btn-info'>Add medicine</a><a id='reset_medicine' href='#' class='btn btn-info'>reset</a></p><div class='med-list'><p>List of medicine:</p><ul></ul</div>",
    ];

    $form['prescription'] = array(
      '#type' => 'textarea',
      '#required' => TRUE,
      '#suffix' => '</div></div></div>',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );

    $uid = \Drupal::currentUser()->id();
    $username = \Drupal::database()->query("SELECT name FROM users_field_data WHERE uid = " . $uid)->fetchField();

    $form['#attached']['drupalSettings']['custom_general']['custom_general_script']['username'] = $username;
    $form['#attached']['drupalSettings']['custom_general']['custom_general_script']['patient_id'] = $patient_id;
    $form['#attached']['library'][] = 'custom_general/custom_general_script';

    return $form;
  }

  /**
   * Form submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getValue('nid');
    $uid = \Drupal::currentUser()->id();

    $username = \Drupal::database()->query("SELECT name FROM users_field_data WHERE uid = " . $uid)->fetchField();

    $suffix = "\n posted on " . date("d-M-Y H:i", \Drupal::time()->getRequestTime()) . " by Dr. " . $username . " @ Hospital";

    $data = [
      'findings' => $form_state->getValue(['findings', 'value']) . $suffix,
      'recommendation' => $form_state->getValue(['recommendation', 'value']) . $suffix,
      'result' => $form_state->getValue(['result', 'value']) . $suffix,
      'prescription' => $form_state->getValue('prescription'),
    ];

    try {
      $client = \Drupal::httpClient();
      $response = $client->post('http://192.168.10.123/api/patient/update', [
        'headers' => [
          'Content-Type' => 'application/json',
          'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
          'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
          'nid' => $nid,
          'role' => 'doctor',
          'action' => 'update',
          'username' => $username,
          'site' => 'Hospital',
        ],
        'body' => json_encode($data),
      ]);

      $data = json_decode($response->getBody(), TRUE);
      
      if ($data['status'] == 'success') {
        drupal_set_message("Successfully updated patient " . ucwords($form_state->getValue('fullname')) . ".");

        $response = new \Symfony\Component\HttpFoundation\RedirectResponse("/");
        $response->send();
      }
      else {
        drupal_set_message("There are errors upon submission.", "error");
      }

    } catch (RequestException $e) {
      drupal_set_message("There are errors upon submission.", "error");
    }
  }
}