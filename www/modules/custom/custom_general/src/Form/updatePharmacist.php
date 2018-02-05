<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\medicardApi;

class updatePharmacist extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pharmacist_comment_form';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $card = medicardApi::get_card_id();
    $card = str_replace(' ', '', $card);

    $data = medicardApi::get_patient();
    foreach ($data['patient'] as $nid => $patient) {
      if ($patient['card_id'] == $card) {
        $form['nid'] = array(
          '#type' => 'hidden',
          '#value' => $nid,
        );

        $form['fullname'] = array(
          '#type' => 'hidden',
          '#value' => ucwords($patient['firstname']) . " " . ucwords($patient['lastname']),
        );
      }
    }    

    $form['pharmacomment'] = array(
      '#type' => 'textarea',
      '#required' => TRUE,
    );
    $form['pharmacomment']['#prefix'] = '<div class="portlet box green">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Comment</div>
      </div>
      <div class="portlet-body">';
    $form['pharmacomment']['#suffix'] = "</div></div>";

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

    $suffix = "\n posted on " . date("d-M-Y H:i", \Drupal::time()->getRequestTime()) . " by " . $username . " @ Hospital";

    $data = [
      'pharmacomment' => $form_state->getValue('pharmacomment') . $suffix,
    ];

    try {
      $client = \Drupal::httpClient();
      $response = $client->post('http://192.168.10.123/api/patient/update', [
        'headers' => [
          'Content-Type' => 'application/json',
          'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
          'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
          'nid' => $nid,
          'role' => 'pharmacist',
          'action' => 'update',
          'username' => $username,
          'site' => 'Pharmacy',
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