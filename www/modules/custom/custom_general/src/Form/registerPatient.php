<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\api;

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
    $form['address'] = array(
      '#type' => 'textarea',
      '#title' => t("Address"),
      '#required' => TRUE,
    );
    $form['address']['#suffix'] = "</div></div>";

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
    $uid = \Drupal::currentUser()->id();

    $values = array(
      'type' => 'patient',
      'uid' => $uid,
      'title' => 'Patient---' . $uid . '---' . \Drupal::time()->getRequestTime(),
    );

    $values['field_first_name'] = array(
      'value' => $form_state->getValue('firstname'),
    );
    $values['field_last_name'] = array(
      'value' => $form_state->getValue('lastname'),
    );
    $values['field_date_of_birth'] = array(
      'value' => strtotime($form_state->getValue('dob')),
    );
    $values['field_gender'] = array(
      'value' => $form_state->getValue('gender'),
    );
    $values['field_patient_address'] = array(
      'value' => $form_state->getValue('address'),
    );
    $values['field_temperature'] = array(
      'value' => $form_state->getValue('temp'),
    );
    $values['field_pulse'] = array(
      'value' => $form_state->getValue('pulse'),
    );
    $values['field_respirations_breathing'] = array(
      'value' => $form_state->getValue('breathing'),
    );
    $values['field_blood_pressure'] = array(
      'value' => $form_state->getValue('bp'),
    );

    // Node data save.
    $node = Node::create($values);
    $node->save();

    drupal_set_message("Successfully registered new patient.");
  }
}