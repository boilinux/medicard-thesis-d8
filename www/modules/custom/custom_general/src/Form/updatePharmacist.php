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
    $buffer_comment = 0;

    $data = medicardApi::get_patient();
    foreach ($data['patient'] as $nid => $patient) {
      if ($patient['card_id'] == $card) {
        $patient_id = $nid;
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

    $prescription = $data['prescription'];

    $arr = [];
    foreach ($prescription as $res) {
      $data2 = json_decode($res['value']);

      foreach ($data2 as $res2) {
        foreach ($res2->patient as $res3) {
          $query_title = \Drupal::database()->query("SELECT nfd.title FROM node_field_data AS nfd 
          WHERE nfd.type = 'medicine' AND nfd.nid = " . $res3->med_nid)->fetchField();

          if ($res3->quantity != $res3->acquire) {
            $form['medicine-' . $res3->med_nid] = [
              '#type' => 'textfield',
              '#title' => $query_title . " - " . abs($res3->quantity - $res3->acquire) . " " . $res3->comment,
              '#default_value' => 1,
              '#attributes' => [
                'data-nid' => $res3->med_nid,
                'data-title' => $query_title,
                'class' => ['medicine-textfield'],
              ],
            ];

            $arr[$res2->date] = $res2;
            $buffer_comment = 1;
          }
        }
      }
    }

    foreach ($arr as $arr2) {
      $form['#attached']['drupalSettings']['custom_general']['custom_general_script2']['data'][] = $arr2;
    }

    if ($buffer_comment) {
      $form['pharmacomment'] = array(
        '#type' => 'textarea',
      );

      $form['pharmacomment2'] = array(
        '#type' => 'textarea',
      );

      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#button_type' => 'primary',
      );
    }
    else {
      drupal_set_message('No prescription from a doctor.', 'warning');
    }
    
    $form['#attached']['library'][] = 'custom_general/custom_general_script2';

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
      'pharmacomment2' => $form_state->getValue('pharmacomment'),
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