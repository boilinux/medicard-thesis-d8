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
        <div class='caption'><i class='fa fa-upload'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['lastname']) . "</div>
      </div>
      <div class='portlet-body'><div class='portlet-body'>
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

    $form['findings'] = array(
      '#type' => 'text_format',
      '#format' => 'doctor_text',
      '#default_value' => $patient['findings'],
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
      '#prefix' => '<div class="portlet box red">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Result</div>
      </div>
      <div class="portlet-body">',
      '#suffix' => '</div></div>',
    );

    $form['prescription'] = array(
      '#type' => 'text_format',
      '#format' => 'doctor_text',
      '#default_value' => $patient['prescription'],
      '#prefix' => '<div class="portlet box red">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Prescription</div>
      </div>
      <div class="portlet-body">',
      '#suffix' => '</div></div>',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * Form submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getValue('nid');
    $uid = \Drupal::currentUser()->id();

    $username = \Drupal::database()->query("SELECT name FROM users_field_data WHERE uid = " . $uid)->fetchField();

    $data = [
      'findings' => $form_state->getValue(['findings', 'value']),
      'recommendation' => $form_state->getValue(['recommendation', 'value']),
      'result' => $form_state->getValue(['result', 'value']),
      'prescription' => $form_state->getValue(['prescription', 'value']),
    ];

    try {
      $client = \Drupal::httpClient();
      $response = $client->post('http://192.168.10.124/api/patient/update', [
        'headers' => [
          'Content-Type' => 'application/json',
          'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
          'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
          'nid' => $nid,
          'role' => 'doctor',
          'action' => 'update',
          'username' => $username,
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