<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

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
  public function buildForm(array $form, FormStateInterface $form_state, $patient = NULL) {
    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $patient->get('nid')->value,
    );

    $form['firstname'] = array(
      '#type' => 'textfield',
      '#title' => t("First Name"),
      '#default_value' => $patient->get('field_first_name')->value,
      '#required' => TRUE,
    );
    $form['firstname']['#prefix'] = '<div class="portlet box green">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Patient Details:</div>
      </div>
      <div class="portlet-body">';

    $form['lastname'] = array(
      '#type' => 'textfield',
      '#title' => t("Last Name"),
      '#default_value' => $patient->get('field_last_name')->value,
      '#required' => TRUE,
    );
    $form['dob'] = array(
      '#type' => 'date',
      '#title' => t("Date Of Birth"),
      '#default_value' => date("Y-m-d", $patient->get('field_date_of_birth')->value),
      '#required' => TRUE,
    ); 
    $form['gender'] = array(
      '#type' => 'select',
      '#title' => t("Gender"),
      '#options' => array('male' => 'Male', 'female' => 'Female'),
      '#default_value' => $patient->get('field_gender')->value,
      '#required' => TRUE,
    );
    $form['address'] = array(
      '#type' => 'textarea',
      '#title' => t("Address"),
      '#default_value' => $patient->get('field_patient_address')->value,
      '#required' => TRUE,
    );
    $form['address']['#suffix'] = "</div></div>";

    $form['temp'] = array(
      '#type' => 'textfield',
      '#title' => t("Temperature"),
      '#default_value' => $patient->get('field_temperature')->value,
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
      '#default_value' => $patient->get('field_pulse')->value,
      '#required' => TRUE,
    );
    $form['breathing'] = array(
      '#type' => 'textfield',
      '#title' => t("Respirations/Breathing"),
      '#default_value' => $patient->get('field_respirations_breathing')->value,
      '#required' => TRUE,
    );
    $form['bp'] = array(
      '#type' => 'textfield',
      '#title' => t("Blood pressure"),
      '#default_value' => $patient->get('field_blood_pressure')->value,
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
    // $node = Node::load($form_state->getValue('nid'));

    // $node->field_first_name->value = $form_state->getValue('firstname');
    // $node->field_last_name->value = $form_state->getValue('lastname');
    // $node->field_date_of_birth->value = strtotime($form_state->getValue('dob'));
    // $node->field_gender->value = $form_state->getValue('gender');
    // $node->field_patient_address->value = $form_state->getValue('address');
    // $node->field_temperature->value = $form_state->getValue('temp');
    // $node->field_pulse->value = $form_state->getValue('pulse');
    // $node->field_respirations_breathing->value = $form_state->getValue('breathing');
    // $node->field_blood_pressure->value = $form_state->getValue('bp');

    // // Node data save.
    // $node->save();

    $data = [
      'firstname' => $form_state->getValue('firstname'),
      'lastname' => $form_state->getValue('lastname'),
      'dob' => $form_state->getValue('dob'),
      'gender' => $form_state->getValue('gender'),
      'address' => $form_state->getValue('address'),
      'temp' => $form_state->getValue('temp'),
      'pulse' => $form_state->getValue('pulse'),
      'breathing' => $form_state->getValue('breathing'),
      'bp' => $form_state->getValue('bp'),
    ];

    try {
      $client = \Drupal::httpClient();
      $response = $client->post('http://192.168.254.102/api/patient/update', [
        'headers' => [
          'Content-Type' => 'application/json',
          'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
          'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
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