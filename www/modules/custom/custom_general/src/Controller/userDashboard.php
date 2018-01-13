<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\custom_general\Controller\apiHelper;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\medicardApi;

class userDashboard extends ControllerBase {
  /**
   * User dashboard
   */
  public function user_dashboard() {
    $output = "";

    if (apiHelper::check_user_role('nurse')) {
      $data = medicardApi::get_patient();

      foreach ($data['patient'] as $nid => $patient) {

        $output .= "<div class='col-md-6 col-sm-12'><div class='portlet yellow-crusta box'>";

        $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['lastname']) . "</div><div class='actions'><a class='btn btn-default btn-sm' href='/view/patient/" . $nid . "'><i class='fa fa-info-circle'></i> View</a><a class='btn btn-default btn-sm' href='/update/patient/" . $nid . "'><i class='fa fa-edit'></i> edit</a></div></div>";

        $output .= "<div class='portlet-body'>
          <div class='row static-info'>
            <div class='col-md-5 name'>Registered on</div><div class='col-md-7 value'> " . date("d-M-Y", $patient['created']) . "</div>
          </div>
          <div class='row static-info'>
          </div>
        </div>";

        $output .= "</div></div>";
      }
    }
    else if (apiHelper::check_user_role('doctor')) {
      $data = medicardApi::get_patient();

      foreach ($data['patient'] as $nid => $patient) {

        $output .= "<div class='col-md-6 col-sm-12'><div class='portlet yellow-crusta box'>";

        $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['lastname']) . "</div><div class='actions'><a class='btn btn-default btn-sm' href='/update/doctor/patient/" . $nid . "'><i class='fa fa-edit'></i> Update</a></div></div>";

        $accordion = "";

        if (!empty($patient['findings'])) {
          $accordion = "<div class='portlet green box doctor-box'>";
          $accordion .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user-md'></i> Findings</div><div class='actions'><a class='btn btn-default btn-sm' data-name='findings' href='#findings'><i class='fa fa-toggle-down'></i> Show</a></div></div>";
          $accordion .= "<div id='findings' class='portlet-body hide'>";
          foreach ($patient['findings'] as $val) {
            $accordion .= "<div>" . $val['value'] . "</div>";
          }
          $accordion .= "</div></div>";
        }
        if (!empty($patient['recommendation'])) {
          $accordion .= "<div class='portlet green box doctor-box'>";
          $accordion .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user-md'></i> Recommendation</div><div class='actions'><a class='btn btn-default btn-sm' data-name='recommendation' href='#recommendation'><i class='fa fa-toggle-down'></i> Show</a></div></div>";
          $accordion .= "<div id='recommendation' class='portlet-body hide'>";
          foreach ($patient['recommendation'] as $val) {
            $accordion .= "<div>" . $val['value'] . "</div>";
          }
          $accordion .= "</div></div>";
        }
        if (!empty($patient['result'])) {
          $accordion .= "<div class='portlet green box doctor-box'>";
          $accordion .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user-md'></i> Result</div><div class='actions'><a class='btn btn-default btn-sm' data-name='result' href='#result'><i class='fa fa-toggle-down'></i> Show</a></div></div>";
          $accordion .= "<div id='result' class='portlet-body hide'>";
          foreach ($patient['result'] as $val) {
            $accordion .= "<div>" . $val['value'] . "</div>";
          }
          $accordion .= "</div></div>";
        }
        if (!empty($patient['prescription'])) {
          $accordion .= "<div class='portlet green box doctor-box'>";
          $accordion .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user-md'></i> Prescription</div><div class='actions'><a class='btn btn-default btn-sm' data-name='prescription' href='#prescription'><i class='fa fa-toggle-down'></i> Show</a></div></div>";
          $accordion .= "<div id='prescription' class='portlet-body hide'>";
          foreach ($patient['prescription'] as $val) {
            $accordion .= "<div>" . $val['value'] . "</div>";
          }
          $accordion .= "</div></div>";
        }
        if (!empty($patient['pharmacomment'])) {
          $accordion .= "<div class='portlet green box doctor-box'>";
          $accordion .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user-md'></i> Pharmacist comments</div><div class='actions'><a class='btn btn-default btn-sm' data-name='pharmacomment' href='#pharmacomment'><i class='fa fa-toggle-down'></i> Show</a></div></div>";
          $accordion .= "<div id='pharmacomment' class='portlet-body hide'>";
          foreach ($patient['pharmacomment'] as $val) {
            $accordion .= "<div>" . $val['value'] . "</div>";
          }
          $accordion .= "</div></div>";
        }

        $output .= "<div class='portlet-body'>
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
            <div class='col-md-5 name'>Registered on </div><div class='col-md-7 value'> " . date("d-M-Y", $patient['created']) . "</div>
          </div>
          " . $accordion . "
        </div>";
        $output .= "</div></div>";
      }
    }
    else if (apiHelper::check_user_role('pharmacist')) {
      $card = medicardApi::get_card_id();
      $card = str_replace(' ', '', $card);
      $status = medicardApi::check_card_id($card);

      if (empty($card) || $status == 'failed') {
        $output .= "<div class='col-sm-12'><h2>Please insert card.</h2></div>";
      }
      else if(!empty($card) && $status == 'exist') {
        $data = medicardApi::get_patient();

        foreach ($data['patient'] as $nid => $patient) {
          if ($patient['card_id'] == $card) {
            $output .= "<div class='cols-m-12'><div class='portlet yellow-crusta box'>";

            $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['lastname']) . "</div></div>";

            $prescription = "";
            if (!empty($patient['prescription'])) {
              foreach ($patient['prescription'] as $val) {
                $prescription .= "<div>" . $val['value'] . "</div>";
              }
            }
            else {
              $prescription .= "<div>No Prescription.</div>";
            }

            $accordion = "";

            if (!empty($patient['pharmacomment'])) {
              $accordion = "<div class='portlet green box doctor-box'>";
              $accordion .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user-md'></i> Latest comments</div><div class='actions'><a class='btn btn-default btn-sm' data-name='pharmacomment' href='#pharmacomment'><i class='fa fa-toggle-down'></i> Show</a></div></div>";
              $accordion .= "<div id='pharmacomment' class='portlet-body hide'>";
              foreach ($patient['pharmacomment'] as $val) {
                $accordion .= "<div>" . $val['value'] . "</div>";
              }
              $accordion .= "</div></div>";
            }

            $output .= "<div class='portlet-body'>
              <div class='row static-info'>
                <div class='col-md-5 name'>Prescription:</div><div class='col-md-7 value'> " . $prescription . "</div>
              </div>
              <div class='row static-info'>
                <div class='col-md-5 name'>Registered on </div><div class='col-md-7 value'> " . date("d-M-Y", $patient['created']) . "</div>
              </div>
              " . $accordion . "
            </div>";
          }
        }
      }
      else {
        $output .= "<div class='col-sm-12'><h2>Patient does not exist.</h2></div>";
      }
      
    }
    else if (apiHelper::check_user_role('administrator')) {
      $data = medicardApi::get_patient();

      foreach ($data['patient'] as $nid => $patient) {

        $output .= "<div class='col-md-6 col-sm-12'><div class='portlet yellow-crusta box'>";

        $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['lastname']) . "</div></div>";

        $output .= "<div class='portlet-body'>
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
            <div class='col-md-5 name'>Registered on </div><div class='col-md-7 value'> " . date("d-M-Y", $patient['created']) . "</div>
          </div>
        </div>";

        $output .= "</div></div>";
      }
    }

    if (empty($data['patient']) && !apiHelper::check_user_role('pharmacist')) {
      $output .= "<div class='col-sm-12'>No patient yet.</div>";
    }

    return array('#markup' => "<div class='row'>" . $output . "</div>", '#cache' => ['max-age' => 0,]);
  }
}