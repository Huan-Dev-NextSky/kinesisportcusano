<?php
/**
 * Cup24 availability: Kinesis API slots ∩ admin Doctor schedule.
 * A time is bookable if ANY linked doctor has it in both API and local schedule.
 */

if (!function_exists('ksc_availability_tz')) {
	function ksc_availability_tz($settings) {
		$name = '';
		if (is_object($settings)) {
			$name = trim((string)$settings->get_option('ct_timezone'));
		}
		if ($name === '') {
			$name = 'Europe/Rome';
		}
		try {
			return new DateTimeZone($name);
		} catch (Exception $e) {
			return new DateTimeZone('Europe/Rome');
		}
	}
}

if (!function_exists('ksc_doctors_for_service')) {
	/**
	 * All bookable Doctors (service assignment via admin service_ids is unused —
	 * availability is decided by Kinesis API ∩ local schedule).
	 * @return array[] each: id (local), external_employee_id (int|null)
	 */
	function ksc_doctors_for_service($conn, $service_id) {
		$out = array();
		if (!$conn) {
			return $out;
		}
		$sql = "SELECT `id`, `external_employee_id` FROM `ct_admin_info`
			WHERE `enable_booking` = 'Y' AND `role` != 'admin'
			ORDER BY `id` ASC";
		$res = mysqli_query($conn, $sql);
		if ($res) {
			while ($row = mysqli_fetch_assoc($res)) {
				$out[] = array(
					'id' => (int)$row['id'],
					'external_employee_id' => !empty($row['external_employee_id']) ? (int)$row['external_employee_id'] : null,
				);
			}
		}
		return $out;
	}
}

if (!function_exists('ksc_local_free_times')) {
	/**
	 * Free HH:mm times for one doctor on Y-m-d from admin schedule (no Google Calendar).
	 * @return string[]
	 */
	function ksc_local_free_times($first_step, $settings, $ymd, $staff_id) {
		$staff_id = (string)$staff_id;
		$time_interval = (int)$settings->get_option('ct_time_interval');
		if ($time_interval <= 0) {
			$time_interval = 30;
		}
		$schedule_type = $settings->get_option('ct_time_slots_schedule_type');
		$advance = $settings->get_option('ct_min_advance_booking_time');
		$pad_before = $settings->get_option('ct_service_padding_time_before');
		$pad_after = $settings->get_option('ct_service_padding_time_after');
		$book_pad = $settings->get_option('ct_booking_padding_time');
		$allow_multi = $settings->get_option('ct_allow_multiple_booking_for_same_timeslot_status');

		$t_zone_value = $settings->get_option('ct_timezone');
		$server_timezone = date_default_timezone_get();
		$timezonediff = 0;
		if (isset($t_zone_value) && $t_zone_value !== '') {
			$timezonediff = $first_step->get_timezone_offset($server_timezone, $t_zone_value) / 3600;
		}

		if ($first_step->check_off_day($ymd, $staff_id)) {
			return array();
		}
		$week_id = ($schedule_type === 'weekly') ? 1 : $first_step->get_week_of_month_by_date($ymd);
		$day_id = (int)date('N', strtotime($ymd));
		$hours = $first_step->time_slots($day_id, $week_id, $time_interval, $staff_id);
		if (!is_array($hours) || empty($hours['daystart_time']) || empty($hours['dayend_time'])) {
			return array();
		}

		$sched = $first_step->get_day_time_slot_by_provider_id(
			$schedule_type,
			$ymd,
			$time_interval,
			$staff_id,
			0,
			'No',
			$advance,
			$pad_before,
			$pad_after,
			$timezonediff,
			$book_pad
		);
		if (empty($sched['slots']) || !empty($sched['off_day'])) {
			return array();
		}

		$booked = isset($sched['booked']) && is_array($sched['booked']) ? $sched['booked'] : array();
		$breaks = isset($sched['breaks']) && is_array($sched['breaks']) ? $sched['breaks'] : array();
		$offtimes = isset($sched['offtimes']) && is_array($sched['offtimes']) ? $sched['offtimes'] : array();
		$free = array();
		foreach ($sched['slots'] as $slot) {
			$slot_ts = strtotime($slot);
			foreach ($breaks as $daybreak) {
				if ($slot_ts >= strtotime($daybreak['break_start']) && $slot_ts < strtotime($daybreak['break_end'])) {
					continue 2;
				}
			}
			foreach ($offtimes as $offtime) {
				$slot_dt = strtotime($ymd . ' ' . $slot);
				if ($slot_dt >= strtotime($offtime['offtime_start']) && $slot_dt < strtotime($offtime['offtime_end'])) {
					continue 2;
				}
			}
			$complete = mktime(
				(int)date('H', strtotime($slot)),
				(int)date('i', strtotime($slot)),
				(int)date('s', strtotime($slot)),
				(int)date('n', strtotime($sched['date'])),
				(int)date('j', strtotime($sched['date'])),
				(int)date('Y', strtotime($sched['date']))
			);
			if ($allow_multi !== 'Y' && in_array($complete, $booked, true)) {
				continue;
			}
			$free[] = date('H:i', strtotime($slot));
		}
		return array_values(array_unique($free));
	}
}

if (!function_exists('ksc_api_client')) {
	function ksc_api_client($conn, $settings) {
		if (!is_object($settings) || $settings->get_option('kinesis_api_status') !== 'Y') {
			return null;
		}
		$path = dirname(dirname(dirname(__FILE__))) . '/integrations/awwapi/AwwApiClient.php';
		if (!is_file($path)) {
			return null;
		}
		if (!class_exists('AwwApiClient')) {
			require_once $path;
		}
		try {
			return new AwwApiClient($conn);
		} catch (Exception $e) {
			return null;
		}
	}
}

if (!function_exists('ksc_api_times_for_range')) {
	/**
	 * Fetch API availability for one employee (or all) across a date range.
	 * Chunks by week to avoid API timeouts on long horizons.
	 * @return array|null  ymd => [ 'H:i' => true ] ; null = API request failed
	 */
	function ksc_api_times_for_range($apiClient, $extServiceId, DateTime $range_start, DateTime $range_end, $extEmployeeId, DateTimeZone $tz) {
		$cache_key = 'ksc_api_av_' . (int)$extServiceId . '_' . (int)$extEmployeeId . '_' . $range_start->format('Ymd') . '_' . $range_end->format('Ymd');
		$now = time();
		if (!empty($_SESSION[$cache_key]['until']) && (int)$_SESSION[$cache_key]['until'] > $now
			&& array_key_exists('map', $_SESSION[$cache_key])) {
			return $_SESSION[$cache_key]['map'];
		}

		$map = array();
		$failed = false;
		$cursor = clone $range_start;
		$cursor->setTimezone($tz);
		$limit = clone $range_end;
		$limit->setTimezone($tz);
		while ($cursor <= $limit) {
			$chunkEnd = clone $cursor;
			$chunkEnd->modify('+6 days');
			if ($chunkEnd > $limit) {
				$chunkEnd = clone $limit;
			}
			$startIso = (clone $cursor)->setTime(0, 0, 0)->format('Y-m-d\TH:i:sP');
			$endIso = (clone $chunkEnd)->setTime(23, 59, 59)->format('Y-m-d\TH:i:sP');
			$emp = ($extEmployeeId !== null && (int)$extEmployeeId > 0) ? (int)$extEmployeeId : null;
			$res = $apiClient->getAvailability((int)$extServiceId, $startIso, $endIso, $emp);
			if (empty($res['success'])) {
				$failed = true;
				break;
			}
			if (!empty($res['data']) && is_array($res['data'])) {
				foreach ($res['data'] as $slot) {
					if (empty($slot['dateTime'])) {
						continue;
					}
					try {
						$dt = new DateTime($slot['dateTime']);
						$dt->setTimezone($tz);
						$ymd = $dt->format('Y-m-d');
						$hi = $dt->format('H:i');
						if (!isset($map[$ymd])) {
							$map[$ymd] = array();
						}
						$map[$ymd][$hi] = true;
					} catch (Exception $e) {
						continue;
					}
				}
			}
			$cursor = clone $chunkEnd;
			$cursor->modify('+1 day');
		}

		$result = $failed ? null : $map;
		$_SESSION[$cache_key] = array('until' => $now + 180, 'map' => $result);
		return $result;
	}
}

if (!function_exists('ksc_intersected_slots_for_service_date')) {
	/**
	 * Bookable HH:mm for a local service on Y-m-d.
	 * API ∩ each doctor's admin schedule; union across doctors (ANY).
	 *
	 * @return array[] each: time (H:i), staff_id
	 */
	function ksc_intersected_slots_for_service_date($conn, $settings, $first_step, $service_row, $ymd) {
		$slots = array();
		$seen = array();
		$service_id = isset($service_row['id']) ? (int)$service_row['id'] : 0;
		$extServiceId = !empty($service_row['external_service_id']) ? (int)$service_row['external_service_id'] : 0;
		$doctors = ksc_doctors_for_service($conn, $service_id);
		$apiClient = ksc_api_client($conn, $settings);
		$useApi = ($apiClient && $extServiceId > 0);
		$tz = ksc_availability_tz($settings);

		$dayStart = new DateTime($ymd . ' 00:00:00', $tz);
		$dayEnd = new DateTime($ymd . ' 23:59:59', $tz);

		$apiMaps = array();
		if ($useApi) {
			$hasExtDoc = false;
			$apiFailed = false;
			foreach ($doctors as $doc) {
				if (!empty($doc['external_employee_id'])) {
					$hasExtDoc = true;
					$map = ksc_api_times_for_range(
						$apiClient, $extServiceId, $dayStart, $dayEnd, (int)$doc['external_employee_id'], $tz
					);
					if ($map === null) {
						$apiFailed = true;
						break;
					}
					$apiMaps[(int)$doc['external_employee_id']] = $map;
				}
			}
			if (!$apiFailed && !$hasExtDoc) {
				$map = ksc_api_times_for_range($apiClient, $extServiceId, $dayStart, $dayEnd, null, $tz);
				if ($map === null) {
					$apiFailed = true;
				} else {
					$apiMaps[0] = $map;
				}
			}
			if ($apiFailed) {
				$useApi = false;
				$apiMaps = array();
			}
		}

		foreach ($doctors as $doc) {
			$local = ksc_local_free_times($first_step, $settings, $ymd, $doc['id']);
			if (!$local) {
				continue;
			}
			if ($useApi) {
				$ext = !empty($doc['external_employee_id']) ? (int)$doc['external_employee_id'] : 0;
				$mapKey = $ext > 0 ? $ext : 0;
				if (!isset($apiMaps[$mapKey])) {
					continue;
				}
				$apiDay = isset($apiMaps[$mapKey][$ymd]) ? $apiMaps[$mapKey][$ymd] : array();
				if (!$apiDay) {
					continue;
				}
				foreach ($local as $hi) {
					if (empty($apiDay[$hi])) {
						continue;
					}
					if (isset($seen[$hi])) {
						continue;
					}
					$seen[$hi] = true;
					$slots[] = array('time' => $hi, 'staff_id' => (int)$doc['id']);
				}
			} else {
				foreach ($local as $hi) {
					if (isset($seen[$hi])) {
						continue;
					}
					$seen[$hi] = true;
					$slots[] = array('time' => $hi, 'staff_id' => (int)$doc['id']);
				}
			}
		}

		usort($slots, function ($a, $b) {
			return strcmp($a['time'], $b['time']);
		});
		return $slots;
	}
}

if (!function_exists('ksc_dates_with_slots_for_service')) {
	/**
	 * Dates (DateTime) for one service that have ≥1 intersected slot.
	 */
	function ksc_dates_with_slots_for_service($conn, $settings, $first_step, $service_row, DateTime $range_start, DateTime $range_end) {
		$service_id = isset($service_row['id']) ? (int)$service_row['id'] : 0;
		$extServiceId = !empty($service_row['external_service_id']) ? (int)$service_row['external_service_id'] : 0;
		/* Keep API horizon practical (avoids multi-month timeouts) */
		$apiClient = ksc_api_client($conn, $settings);
		$useApi = ($apiClient && $extServiceId > 0);
		if ($useApi) {
			$maxEnd = clone $range_start;
			$maxEnd->modify('+45 days');
			if ($range_end > $maxEnd) {
				$range_end = $maxEnd;
			}
		}
		$start_ymd = $range_start->format('Y-m-d');
		$end_ymd = $range_end->format('Y-m-d');
		$cache_key = 'ksc_svc_dates_v2_' . $service_id . '_' . $extServiceId . '_' . $start_ymd . '_' . $end_ymd;
		$now = time();
		if (!empty($_SESSION[$cache_key]['until']) && (int)$_SESSION[$cache_key]['until'] > $now
			&& !empty($_SESSION[$cache_key]['dates']) && is_array($_SESSION[$cache_key]['dates'])) {
			$out = array();
			foreach ($_SESSION[$cache_key]['dates'] as $ymd) {
				$out[] = new DateTime($ymd);
			}
			return $out;
		}

		$doctors = ksc_doctors_for_service($conn, $service_id);
		$tz = ksc_availability_tz($settings);

		$apiMaps = array();
		if ($useApi) {
			$hasExtDoc = false;
			$apiFailed = false;
			foreach ($doctors as $doc) {
				if (!empty($doc['external_employee_id'])) {
					$hasExtDoc = true;
					$map = ksc_api_times_for_range(
						$apiClient, $extServiceId, $range_start, $range_end, (int)$doc['external_employee_id'], $tz
					);
					if ($map === null) {
						$apiFailed = true;
						break;
					}
					$apiMaps[(int)$doc['external_employee_id']] = $map;
				}
			}
			if (!$apiFailed && !$hasExtDoc) {
				$map = ksc_api_times_for_range($apiClient, $extServiceId, $range_start, $range_end, null, $tz);
				if ($map === null) {
					$apiFailed = true;
				} else {
					$apiMaps[0] = $map;
				}
			}
			if ($apiFailed) {
				$useApi = false;
				$apiMaps = array();
			}
		}

		$ymd_list = array();
		$dates = array();
		for ($cursor = clone $range_start; $cursor <= $range_end; $cursor->modify('+1 day')) {
			$ymd = $cursor->format('Y-m-d');
			$has = false;
			foreach ($doctors as $doc) {
				$local = ksc_local_free_times($first_step, $settings, $ymd, $doc['id']);
				if (!$local) {
					continue;
				}
				if ($useApi) {
					$ext = !empty($doc['external_employee_id']) ? (int)$doc['external_employee_id'] : 0;
					$mapKey = $ext > 0 ? $ext : 0;
					if (!isset($apiMaps[$mapKey])) {
						continue;
					}
					$apiDay = isset($apiMaps[$mapKey][$ymd]) ? $apiMaps[$mapKey][$ymd] : array();
					foreach ($local as $hi) {
						if (!empty($apiDay[$hi])) {
							$has = true;
							break;
						}
					}
				} else {
					$has = true;
				}
				if ($has) {
					break;
				}
			}
			if ($has) {
				$dates[] = clone $cursor;
				$ymd_list[] = $ymd;
			}
		}

		$_SESSION[$cache_key] = array('until' => $now + 180, 'dates' => $ymd_list);
		return $dates;
	}
}

if (!function_exists('ksc_api_doctors')) {
	/**
	 * Bookable Doctors that can be checked on Kinesis (have external_employee_id).
	 * @return array[] each: id, external_employee_id
	 */
	function ksc_api_doctors($conn) {
		$out = array();
		if (!$conn) {
			return $out;
		}
		$res = mysqli_query($conn, "SELECT `id`, `external_employee_id` FROM `ct_admin_info`
			WHERE `enable_booking` = 'Y'
			AND `role` != 'admin'
			AND `external_employee_id` IS NOT NULL
			AND `external_employee_id` > 0
			ORDER BY `id` ASC");
		if ($res) {
			while ($row = mysqli_fetch_assoc($res)) {
				$out[] = array(
					'id' => (int)$row['id'],
					'external_employee_id' => (int)$row['external_employee_id'],
				);
			}
		}
		return $out;
	}
}

if (!function_exists('ksc_assign_doctor_for_datetime')) {
	/**
	 * Pick a Doctor who STILL has this service datetime free on the Kinesis API.
	 * Prefers $preferred_staff_id when that Doctor still has the slot.
	 * Does not fall back to admin/local-only guesses.
	 *
	 * @return int|null local staff id, or null if none available / API unavailable
	 */
	function ksc_assign_doctor_for_datetime($conn, $settings, $first_step, $service_id, $booking_date_time, $preferred_staff_id = null, $local_only = false) {
		$service_id = (int)$service_id;
		if ($service_id <= 0 || !$booking_date_time) {
			return null;
		}
		$ts = strtotime($booking_date_time);
		if (!$ts) {
			return null;
		}
		$ymd = date('Y-m-d', $ts);
		$hi = date('H:i', $ts);

		$service_row = null;
		$q = mysqli_query($conn, "SELECT * FROM `ct_services` WHERE `id` = " . $service_id . " LIMIT 1");
		if ($q && mysqli_num_rows($q) > 0) {
			$service_row = mysqli_fetch_assoc($q);
		}
		if (!$service_row) {
			return null;
		}

		$extServiceId = !empty($service_row['external_service_id']) ? (int)$service_row['external_service_id'] : 0;
		$apiClient = ksc_api_client($conn, $settings);
		/* Checkout assign is API-only (local_only ignored for availability source) */
		if (!$apiClient || $extServiceId <= 0) {
			return null;
		}

		$doctors = ksc_api_doctors($conn);
		if (!$doctors) {
			return null;
		}

		$ordered = array();
		$pref = $preferred_staff_id !== null ? (int)$preferred_staff_id : 0;
		if ($pref > 0) {
			foreach ($doctors as $doc) {
				if ((int)$doc['id'] === $pref) {
					$ordered[] = $doc;
				}
			}
		}
		foreach ($doctors as $doc) {
			if ($pref > 0 && (int)$doc['id'] === $pref) {
				continue;
			}
			$ordered[] = $doc;
		}

		$tz = ksc_availability_tz($settings);
		$dayStart = new DateTime($ymd . ' 00:00:00', $tz);
		$dayEnd = new DateTime($ymd . ' 23:59:59', $tz);

		foreach ($ordered as $doc) {
			$extEmp = (int)$doc['external_employee_id'];
			if ($extEmp <= 0) {
				continue;
			}
			$map = ksc_api_times_for_range($apiClient, $extServiceId, $dayStart, $dayEnd, $extEmp, $tz);
			if ($map === null) {
				/* API failure for this doctor — skip; if all fail, caller gets null */
				continue;
			}
			$apiDay = isset($map[$ymd]) ? $map[$ymd] : array();
			if (empty($apiDay[$hi])) {
				continue;
			}
			return (int)$doc['id'];
		}
		return null;
	}
}

if (!function_exists('ksc_dates_with_slots')) {
	/**
	 * Backward-compatible: union of dates across all given services, or admin-only for staff_id.
	 * Prefer ksc_dates_with_slots_for_service for per-service cards.
	 */
	function ksc_dates_with_slots($conn, $settings, DateTime $range_start, DateTime $range_end, $staff_id = null) {
		global $first_step;
		if (!isset($first_step) || !is_object($first_step)) {
			if (!class_exists('cleanto_first_step')) {
				include_once dirname(dirname(dirname(__FILE__))) . '/objects/class_front_first_step.php';
			}
			$first_step = new cleanto_first_step();
			$first_step->conn = $conn;
		}
		if ($staff_id === null || $staff_id === '') {
			$staff_id = (!empty($_SESSION['staff_id_cal']) && $_SESSION['staff_id_cal'] !== 'random')
				? (string)$_SESSION['staff_id_cal']
				: '1';
		}
		$ymd_list = array();
		$dates = array();
		for ($cursor = clone $range_start; $cursor <= $range_end; $cursor->modify('+1 day')) {
			$ymd = $cursor->format('Y-m-d');
			if (ksc_local_free_times($first_step, $settings, $ymd, $staff_id)) {
				$dates[] = clone $cursor;
				$ymd_list[] = $ymd;
			}
		}
		return $dates;
	}
}
