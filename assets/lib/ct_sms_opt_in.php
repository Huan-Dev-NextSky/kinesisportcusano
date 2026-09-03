<?php
/**
 * Shared SMS opt-in helper for Phase 4 customer preference.
 * Returns true when the client may receive booking-related SMS.
 */
if (!function_exists('ct_client_sms_allowed')) {
	function ct_client_sms_allowed($conn, $client_id = 0, $order_id = 0) {
		$client_id = (int)$client_id;
		$order_id = (int)$order_id;

		if ($client_id <= 0 && $order_id > 0 && $conn) {
			$res = @mysqli_query($conn, "SELECT `client_id` FROM `ct_bookings` WHERE `order_id` = {$order_id} LIMIT 1");
			if ($res && ($row = mysqli_fetch_assoc($res))) {
				$client_id = (int)$row['client_id'];
			}
		}

		if ($client_id <= 0 || !$conn) {
			return true;
		}

		$res = @mysqli_query($conn, "SELECT `sms_opt_in` FROM `ct_users` WHERE `id` = {$client_id} LIMIT 1");
		if ($res && ($row = mysqli_fetch_assoc($res))) {
			return (!isset($row['sms_opt_in']) || $row['sms_opt_in'] === '' || $row['sms_opt_in'] === 'Y');
		}
		return true;
	}
}
