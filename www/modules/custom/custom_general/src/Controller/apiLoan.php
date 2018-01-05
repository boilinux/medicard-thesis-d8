<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\apiHelper;

class apiLoan extends ControllerBase {

	public function loan_paid(NodeInterface $loan, NodeInterface $payable_loan_node) {

		if (apiHelper::check_loan_ownership($payable_loan_node->getOwnerId())) {
			$payable_loan_node->field_status->value = 'paid';
			$payable_loan_node->field_paid_on->value = \Drupal::time()->getRequestTime();

			$payable_loan_node->save();

			drupal_set_message("Successfully paid " . $payable_loan_node->field_amount_payable->value);
		}
		else {
			return AccessResult::forbidden();
		}

		$response = new \Symfony\Component\HttpFoundation\RedirectResponse("/user/loan/" . $loan->id());
    $response->send();
	}

	/**
	 * Archive loan.
	 */
	public function loan_archive(NodeInterface $loan) {

		if (apiHelper::check_loan_ownership($loan->getOwnerId())) {
			$loan->field_archive = 1;
			$loan->save();

			drupal_set_message("Archive loan for " . $loan->field_name->value);
		}
		else {
			drupal_set_message("Loan not found.", "error");
		}

		$response = new \Symfony\Component\HttpFoundation\RedirectResponse("/user/dashboard");
    $response->send();
	}

	/**
   * Loan amount.
   */
  public static function loan_amount($nid) {
    $query = \Drupal::database()->query("SELECT nfa.field_amount_value AS amount FROM node_field_data AS nfd
      LEFT JOIN node__field_amount AS nfa ON nfa.entity_id = nfd.nid
      WHERE nfd.nid = " . $nid . " AND nfd.type = 'loan'")->fetchField();

    return number_format($query);
  }

	/**
   * Loan payable amount.
   */
  public static function loan_amount_payable($nid) {
    $query = \Drupal::database()->query("SELECT nfap.field_amount_payable_value AS amount FROM node_field_data AS nfd
      LEFT JOIN node__field_amount_payable AS nfap ON nfap.entity_id = nfd.nid
      WHERE nfd.nid = " . $nid . " AND nfd.type = 'loan_payable'")->fetchField();

    return number_format($query, 2);
  }

  /**
   * Loan payment start.
   */
  public static function loan_payment_start($nid) {
  	$query = \Drupal::database()->query("SELECT nfsp.field_start_payment_value AS start_date FROM node_field_data AS nfd
      LEFT JOIN node__field_start_payment AS nfsp ON nfsp.entity_id = nfd.nid
      WHERE nfd.nid = " . $nid . " AND nfd.type = 'loan'")->fetchField();

    return date("M d, Y", $query);
  }

	/**
   * Loan balance.
   */
  public static function loan_balance($nid) {
    $query = \Drupal::database()->query("SELECT nfap.field_amount_payable_value AS payable, nfs.field_status_value AS status FROM node_field_data AS nfd
      LEFT JOIN node__field_loan_reference AS nflr ON nflr.field_loan_reference_target_id = nfd.nid
      LEFT JOIN node__field_amount_payable AS nfap ON nfap.entity_id = nflr.entity_id
      LEFT JOIN node__field_status AS nfs ON nfs.entity_id = nflr.entity_id
      WHERE nfd.nid = " . $nid . " AND nfd.type = 'loan'")->fetchAll();

    $balance = 0;
    foreach ($query as $res) {
      if ($res->status == 'unpaid') {
        $balance += $res->payable;
      }
    }

    return number_format($balance);
  }

  /**
   * Loan paid.
   */
  public static function loan_paid_total($nid) {
    $query = \Drupal::database()->query("SELECT nfap.field_amount_payable_value AS payable, nfs.field_status_value AS status FROM node_field_data AS nfd
      LEFT JOIN node__field_loan_reference AS nflr ON nflr.field_loan_reference_target_id = nfd.nid
      LEFT JOIN node__field_amount_payable AS nfap ON nfap.entity_id = nflr.entity_id
      LEFT JOIN node__field_status AS nfs ON nfs.entity_id = nflr.entity_id
      WHERE nfd.nid = " . $nid . " AND nfd.type = 'loan'")->fetchAll();

    $paid = 0;
    foreach ($query as $res) {
      if ($res->status == 'paid') {
        $paid += $res->payable;
      }
    }

    return number_format($paid, 2);
  }
}