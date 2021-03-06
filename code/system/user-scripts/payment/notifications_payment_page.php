<?php

class SJB_Payment_Notifications extends SJB_Function
{
	public function execute()
	{
		if (!preg_match("(.*/system/payment/notifications/([^/]+)/?)", $_SERVER['REQUEST_URI'], $matches)) {
			echo '<p class="error">Gateway parameter is missing</p>';
			exit();
		}
		$gateway_id = $matches[1];
		$gateway = SJB_PaymentGatewayManager::getObjectByID($gateway_id);
		if (!$gateway) {
			echo '<p class="error">Invalid gateway</p>';
			exit;
		}
		$gateway->handleNotification($_REQUEST);
	}
}