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

        $output .= '<div class="modal fade" id="loan_modal' . $nid . '" tabindex="-1" role="basic" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Please confirm</h4>
                  </div>
                  <div class="modal-body">
                     <span class="color_green"> </span> loan will be archive.
                  </div>
                  <div class="modal-footer">
                    <a type="button" class="btn default" data-dismiss="modal">Close</a>
                    <a href="/user/loan/' . $nid . '/archive" class="btn blue">Submit</a>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>';

        $output .= "</div></div>";
      }
    }
    else if (apiHelper::check_user_role('doctor')) {
      $data = medicardApi::get_patient();

      foreach ($data['patient'] as $nid => $patient) {

        $output .= "<div class='col-md-6 col-sm-12'><div class='portlet yellow-crusta box'>";

        $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($patient['firstname']) . " " . ucwords($patient['lastname']) . "</div><div class='actions'><a class='btn btn-default btn-sm' href='/update/doctor/patient/" . $nid . "'><i class='fa fa-edit'></i> Update</a></div></div>";

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

        $output .= '<div class="modal fade" id="loan_modal' . $nid . '" tabindex="-1" role="basic" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Please confirm</h4>
                  </div>
                  <div class="modal-body">
                     <span class="color_green"></span> loan will be archive.
                  </div>
                  <div class="modal-footer">
                    <a type="button" class="btn default" data-dismiss="modal">Close</a>
                    <a href="/user/loan/' . $nid . '/archive" class="btn blue">Submit</a>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>';

        $output .= "</div></div>";
      }
    }
    else if (apiHelper::check_user_role('pharmacist')) {
      $output .= "<div class='col-sm-12'>Please insert card.</div>";
    }

    if (empty($data['patient'])) {
      $output .= "<div class='col-sm-12'>No patient yet.</div>";
    }

    return array('#markup' => "<div class='row'>" . $output . "</div>", '#cache' => ['max-age' => 0,]);
  }
}