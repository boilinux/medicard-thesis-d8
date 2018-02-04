<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\medicardApi;

class registerPatient extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_patient';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $card = medicardApi::get_card_id();
    $card = str_replace(' ', '', $card);
    $status = medicardApi::check_card_id($card);

    if (empty($card) || $status == 'failed') {
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Refresh'),
        '#button_type' => 'primary',
        '#prefix' => "<h2>Please insert card.</h2>",
      );
      return $form;
    }
    else if(!empty($card) && $status == 'exist') {
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Refresh'),
        '#button_type' => 'primary',
        '#prefix' => "<h2>Your card is already registered.<br>Please insert another card.</h2>",
      );
      return $form;
    }

    $form['card_id'] = array(
      '#type' => 'hidden',
      '#default_value' => $card,
      '#required' => TRUE,
    );

    $form['firstname'] = array(
      '#type' => 'textfield',
      '#title' => t("First Name"),
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
      '#required' => TRUE,
    );
    $form['lastname'] = array(
      '#type' => 'textfield',
      '#title' => t("Last Name"),
      '#required' => TRUE,
    );
    $form['dob'] = array(
      '#type' => 'date',
      '#title' => t("Date Of Birth"),
      '#required' => TRUE,
    );
    $form['gender'] = array(
      '#type' => 'select',
      '#title' => t("Gender"),
      '#options' => array('male' => 'Male', 'female' => 'Female'),
      '#required' => TRUE,
    );
    $form['status'] = array(
      '#type' => 'select',
      '#title' => t("Status"),
      '#options' => array('single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'),
      '#required' => TRUE,
    );
    $form['phonenumber'] = array(
      '#type' => 'textfield',
      '#title' => t("Phone number"),
      '#required' => TRUE,
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => t("Email"),
      '#required' => TRUE,
    );
    $form['address'] = array(
      '#type' => 'textarea',
      '#title' => t("Address"),
      '#required' => TRUE,
    );
    $form['employer'] = array(
      '#type' => 'textfield',
      '#title' => t("Employer"),
      '#required' => TRUE,
    );
    $form['companyaddress'] = array(
      '#type' => 'textarea',
      '#title' => t("Company address"),
      '#required' => TRUE,
    );
    $form['immunization'] = array(
      '#type' => 'textarea',
      '#title' => t("immunization"),
    );
    $form['labtest'] = array(
      '#type' => 'textarea',
      '#title' => t("Laboratory test"),
    );
    $form['labtest']['#suffix'] = "</div></div>";

    $form['temp'] = array(
      '#type' => 'textfield',
      '#title' => t("Temperature"),
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
      '#required' => TRUE,
    );
    $form['breathing'] = array(
      '#type' => 'textfield',
      '#title' => t("Respirations/Breathing"),
      '#required' => TRUE,
    );
    $form['bp'] = array(
      '#type' => 'textfield',
      '#title' => t("Blood pressure"),
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
    if ($form_state->getValue('op') == 'Refresh') {
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse("/register/patient");
      $response->send();
    }
    else {  
      $uid = \Drupal::currentUser()->id();

      $username = \Drupal::database()->query("SELECT name FROM users_field_data WHERE uid = " . $uid)->fetchField();

      $data = [
        'card_id' => $form_state->getValue('card_id'),
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
            'role' => 'nurse',
            'action' => 'register',
            'username' => $username,
            'site' => 'Barangay',
          ],
          'body' => json_encode($data),
        ]);

        $data = json_decode($response->getBody(), TRUE);
        
        if ($data['status'] == 'success') {
          drupal_set_message("Successfully registered patient " . ucwords($form_state->getValue('firstname')) . " " . ucwords($form_state->getValue('lastname')) . ".");

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
}