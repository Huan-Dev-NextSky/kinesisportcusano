<?php
/**
 * Renders service cards, one per upcoming date.
 * First page is PHP; set $ksc_items_only to echo only the next <li> batch.
 * Expects: $services, $ksc_services_limit, $ksc_format_price, $cta_label,
 *          $load_more_label, $duration_suffix, $settings, SITE_URL
 */
if (!isset($ksc_services_limit)) {
	$ksc_services_limit = 10;
}
if (!isset($ksc_offset)) {
	$ksc_offset = 0;
}
$ksc_offset = max(0, (int)$ksc_offset);
$ksc_services_limit = max(1, (int)$ksc_services_limit);
$ksc_items_only = !empty($ksc_items_only);
if (!is_array($services)) {
	$services = array();
}
if (!function_exists('ksc_italian_date_label')) {
	function ksc_italian_date_label($date = null) {
		$dt = ($date instanceof DateTime) ? $date : new DateTime($date ? $date : 'now');
		$days = array('Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab');
		$months = array(1 => 'Gen', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mag', 6 => 'Giu', 7 => 'Lug', 8 => 'Ago', 9 => 'Set', 10 => 'Ott', 11 => 'Nov', 12 => 'Dic');
		return $days[(int)$dt->format('w')] . ', ' . $dt->format('j') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
	}
}
/* Only show services that have both a price and a duration */
$services = array_values(array_filter($services, function ($s_arr) {
	$price = isset($s_arr['price']) ? (float)$s_arr['price'] : 0;
	$duration = isset($s_arr['duration']) ? (int)$s_arr['duration'] : 0;
	return $price > 0 && $duration > 0;
}));

$ksc_months_ahead = 2;
if (isset($settings) && is_object($settings)) {
	$configured = (int)$settings->get_option('ct_max_advance_booking_time');
	if ($configured > 0) {
		$ksc_months_ahead = min($configured, 6);
	}
}
$ksc_range_start = new DateTime('today');
$ksc_range_end = new DateTime('today');
$ksc_range_end->modify('+' . $ksc_months_ahead . ' months');

/* Per service: dates with API ∩ Doctor admin slots (ANY doctor) */
$ksc_cards = array();
if (isset($conn) && isset($settings) && is_object($settings) && count($services) > 0) {
	include_once dirname(dirname(dirname(__FILE__))) . '/assets/lib/ksc_dates_with_slots.php';
	global $first_step;
	if (!isset($first_step) || !is_object($first_step)) {
		if (!class_exists('cleanto_first_step')) {
			include_once dirname(dirname(dirname(__FILE__))) . '/objects/class_front_first_step.php';
		}
		$first_step = new cleanto_first_step();
		$first_step->conn = $conn;
	}
	foreach ($services as $s_arr) {
		$slot_dates = ksc_dates_with_slots_for_service($conn, $settings, $first_step, $s_arr, $ksc_range_start, $ksc_range_end);
		foreach ($slot_dates as $slot_date) {
			$row = $s_arr;
			$row['_ksc_date'] = clone $slot_date;
			$ksc_cards[] = $row;
		}
	}
	usort($ksc_cards, function ($a, $b) {
		$da = $a['_ksc_date']->format('Y-m-d');
		$db = $b['_ksc_date']->format('Y-m-d');
		if ($da === $db) {
			return ((int)$a['id']) - ((int)$b['id']);
		}
		return strcmp($da, $db);
	});
}
$total_cards = count($ksc_cards);
$ksc_page_cards = array_slice($ksc_cards, $ksc_offset, $ksc_services_limit);
$ksc_page_count = count($ksc_page_cards);
$ksc_next_offset = $ksc_offset + $ksc_page_count;

if (!$ksc_items_only) { ?>
<ul class="services-list ksc-service-cards" id="ksc-service-cards" data-page-size="<?php echo (int)$ksc_services_limit; ?>">
<?php if ($total_cards === 0) { ?>
	<li class="ksc-service-empty"><?php echo htmlspecialchars(isset($ksc_empty_label) ? $ksc_empty_label : 'Nessun servizio trovato.'); ?></li>
<?php }
}
foreach ($ksc_page_cards as $s_arr) {
	$sid = (int)$s_arr['id'];
	$card_date = $s_arr['_ksc_date'];
	$date_key = $card_date->format('j-m-Y');
	$date_label = ksc_italian_date_label($card_date);
	$title = isset($s_arr['title']) ? $s_arr['title'] : '';
	$desc = isset($s_arr['description']) ? trim(strip_tags($s_arr['description'])) : '';
	$raw_image = isset($s_arr['image']) ? trim((string)$s_arr['image']) : '';
	$placeholder_images = array('', 'default.png', 'default_service.png');
	$image = (!in_array($raw_image, $placeholder_images, true)) ? $raw_image : '';
	$price = isset($s_arr['price']) ? (float)$s_arr['price'] : 0;
	$duration = isset($s_arr['duration']) ? (int)$s_arr['duration'] : 0;
	$monogram = function_exists('mb_substr') ? mb_strtoupper(mb_substr($title, 0, 1)) : strtoupper(substr($title, 0, 1));
	?>
	<li class="ksc-service-card">
		<input type="radio" name="service-radio" id="ct-service-<?php echo $sid; ?>-<?php echo $card_date->format('Ymd'); ?>" class="make_service_disable" />

		<div class="ksc-card-media<?php echo $image !== '' ? ' has-image' : ''; ?>">
			<?php if ($image !== '') { ?>
				<img class="ct-image" src="<?php echo SITE_URL; ?>assets/images/services/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy" />
			<?php } else { ?>
				<span class="ksc-card-monogram" aria-hidden="true"><?php echo htmlspecialchars($monogram); ?></span>
			<?php } ?>
		</div>

		<div class="ksc-card-body">
			<h4 class="ksc-card-title service-name"><?php echo htmlspecialchars($title); ?></h4>
			<div class="ksc-card-meta"><?php echo (int)$duration; ?> <?php echo htmlspecialchars($duration_suffix); ?></div>
			<?php if ($desc !== '') { ?>
				<div class="ksc-card-tags"><?php echo htmlspecialchars($desc); ?></div>
			<?php } ?>
		</div>

		<div class="ksc-card-aside">
			<div class="ksc-card-slot"><?php echo htmlspecialchars($date_label); ?></div>
			<div class="ksc-card-price"><?php echo htmlspecialchars($ksc_format_price($price)); ?></div>
			<button type="button"
				class="ksc-book-btn remove_service_class"
				data-servicetitle="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>"
				data-id="<?php echo $sid; ?>"
				data-ksc-date="<?php echo htmlspecialchars($date_key, ENT_QUOTES); ?>"
				data-price="<?php echo htmlspecialchars($ksc_format_price($price), ENT_QUOTES); ?>"
				data-duration="<?php echo (int)$duration . ' ' . htmlspecialchars($duration_suffix, ENT_QUOTES); ?>">
				<?php echo htmlspecialchars($cta_label); ?>
			</button>
		</div>
	</li>
	<?php
}
if (!$ksc_items_only) { ?>
</ul>
<?php if ($ksc_next_offset < $total_cards) { ?>
<div class="ksc-load-more-wrap" id="ksc-load-more-wrap">
	<button type="button" class="ksc-load-more-btn" id="ksc-load-more-services"
		data-offset="<?php echo (int)$ksc_next_offset; ?>"
		data-total="<?php echo (int)$total_cards; ?>"
		data-step="<?php echo (int)$ksc_services_limit; ?>">
		<?php echo htmlspecialchars($load_more_label); ?>
	</button>
</div>
<?php } else { ?>
<div class="ksc-load-more-wrap is-hidden" id="ksc-load-more-wrap"></div>
<?php }
}
