<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

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
  public function buildForm(array $form, FormStateInterface $form_state, $patient = NULL) {
    $output = "";

    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $patient->get('nid')->value,
    );

    $output .= "<div class='portlet box green'>
      <div class='portlet-title'>
        <div class='caption'><i class='fa fa-upload'></i> " . ucwords($patient->get('field_first_name')->value) . " " . ucwords($patient->get('field_last_name')->value) . "</div>
      </div>
      <div class='portlet-body'><div class='portlet-body'>
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

    $form['findings'] = array(
      '#type' => 'text_format',
      '#format' => 'doctor_text',
      '#default_value' => $patient->get('field_findings')->value,
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
      '#default_value' => $patient->get('field_recommendation')->value,
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
      '#default_value' => $patient->get('field_result')->value,
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
      '#default_value' => $patient->get('field_prescription')->value,
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
    $node = Node::load($form_state->getValue('nid'));

    $node->field_findings->value = $form_state->getValue(['findings', 'value']);
    $node->field_recommendation->value = $form_state->getValue(['recommendation', 'value']);
    $node->field_result->value = $form_state->getValue(['result', 'value']);
    $node->field_prescription->value = $form_state->getValue(['prescription', 'value']);

    // Node data save.
    $node->save();

    drupal_set_message("Successfully updated patient " . ucwords($node->get('field_first_name')->value) . " " . ucwords($node->get('field_last_name')->value) . ".");

    $response = new \Symfony\Component\HttpFoundation\RedirectResponse("/");
    $response->send();
  }
}