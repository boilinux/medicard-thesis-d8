<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\custom_general\Controller\medicardApi;

class updatePatient extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_patient';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $patient_id = NULL) {
    $patient = medicardApi::get_patient();
    $patient = $patient['patient'][$patient_id];

    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $patient_id,
    );

    $form['firstname'] = array(
      '#type' => 'textfield',
      '#title' => t("First Name"),
      '#default_value' => $patient['firstname'],
      '#required' => TRUE,
    );
    $form['firstname']['#prefix'] = '<div class="portlet box green">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Patient Details:</div>
      </div>
      <div class="portlet-body">';

    $form['middlename'] = array(
      '#type' => 'textfield',
      '#title' => t("Middle Name"),
      '#default_value' => $patient['middlename'],
      '#required' => TRUE,
    );
    $form['lastname'] = array(
      '#type' => 'textfield',
      '#title' => t("Last Name"),
      '#default_value' => $patient['lastname'],
      '#required' => TRUE,
    );
    $form['dob'] = array(
      '#type' => 'date',
      '#title' => t("Date Of Birth"),
      '#default_value' => date("Y-m-d", $patient['dob']),
      '#required' => TRUE,
    ); 
    $form['gender'] = array(
      '#type' => 'select',
      '#title' => t("Gender"),
      '#options' => array('male' => 'Male', 'female' => 'Female'),
      '#default_value' => $patient['gender'],
      '#required' => TRUE,
    );
    $form['status'] = array(
      '#type' => 'select',
      '#title' => t("Status"),
      '#options' => array('single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'),
      '#default_value' => $patient['status'],
      '#required' => TRUE,
    );
    $form['phonenumber'] = array(
      '#type' => 'textfield',
      '#title' => t("Phone number"),
      '#default_value' => $patient['phonenumber'],
      '#required' => TRUE,
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => t("Email"),
      '#default_value' => $patient['email'],
      '#required' => TRUE,
    );
    $form['address'] = array(
      '#type' => 'textarea',
      '#title' => t("Address"),
      '#default_value' => $patient['address'],
      '#required' => TRUE,
    );
    $form['employer'] = array(
      '#type' => 'textfield',
      '#title' => t("Employer"),
      '#default_value' => $patient['employer'],
      '#required' => TRUE,
    );
    $form['companyaddress'] = array(
      '#type' => 'textarea',
      '#title' => t("Company address"),
      '#default_value' => $patient['companyaddress'],
      '#required' => TRUE,
    );
    $form['immunization'] = array(
      '#type' => 'textarea',
      '#title' => t("immunization"),
      '#default_value' => $patient['immunization'],
    );
    $form['labtest'] = array(
      '#type' => 'textarea',
      '#title' => t("Laboratory test"),
      '#default_value' => $patient['labtest'],
    );
    $form['labtest']['#suffix'] = "</div></div>";

    $form['temp'] = array(
      '#type' => 'textfield',
      '#title' => t("Temperature"),
      '#default_value' => $patient['temp'],
      '#required' => TRUE,
    );
    $form['temp']['#prefix'] = '<div class="portlet box blue">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Patient Vital signs:</div>
      </div>
      <div class="portlet-body">';
    $form['pulse'] = array(
      '#type' => 'textfield',
      '#title' => t("Pulse"),
      '#default_value' => $patient['pulse'],
      '#required' => TRUE,
    );
    $form['breathing'] = array(
      '#type' => 'textfield',
      '#title' => t("Respirations/Breathing"),
      '#default_value' => $patient['breathing'],
      '#required' => TRUE,
    );
    $form['bp'] = array(
      '#type' => 'textfield',
      '#title' => t("Blood pressure"),
      '#default_value' => $patient['bp'],
      '#required' => TRUE,
    );
    $form['bp']['#suffix'] = "</div></div>";

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
      'firstname' => $form_state->getValue('firstname'),
      'middlename' => $form_state->getValue('middlename'),
      'lastname' => $form_state->getValue('lastname'),
      'dob' => $form_state->getValue('dob'),
      'gender' => $form_state->getValue('gender'),
      'status' => $form_state->getValue('status'),
      'phonenumber' => $form_state->getValue('phonenumber'),
      'email' => $form_state->getValue('email'),
      'employer' => $form_state->getValue('employer'),
      'companyaddress' => $form_state->getValue('companyaddress'),
      'immunization' => $form_state->getValue('immunization'),
      'labtest' => $form_state->getValue('labtest'),
      'address' => $form_state->getValue('address'),
      'temp' => $form_state->getValue('temp'),
      'pulse' => $form_state->getValue('pulse'),
      'breathing' => $form_state->getValue('breathing'),
      'bp' => $form_state->getValue('bp'),
    ];

    try {
      $client = \Drupal::httpClient();
      $response = $client->post('http://192.168.10.123/api/patient/update', [
        'headers' => [
          'Content-Type' => 'application/json',
          'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
          'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
          'nid' => $nid,
          'role' => 'nurse',
          'action' => 'update',
          'username' => $username,
          'site' => 'Hospital',
        ],
        'body' => json_encode($data),
      ]);

      $data = json_decode($response->getBody(), TRUE);
      
      if ($data['status'] == 'success') {
        drupal_set_message("Successfully updated patient " . ucwords($form_state->getValue('firstname')) . " " . ucwords($form_state->getValue('lastname')) . ".");

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