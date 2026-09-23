<?php

/**
 * Cup24-style service cards for index_one_step.php
 * Expects: $objservice, $settings, $label_language_values, SITE_URL
 */
if (!isset($ksc_services_limit)) {
	$ksc_services_limit = 10;
}

if (isset($_GET['service'])) {
	$service_ids = base64_decode($_GET['service']);
	$services_data = $objservice->readall_for_frontend_services_byservice_id($service_ids);
} else {
	$services_data = $objservice->readall_for_frontend_services();
}

$currency_symbol = $settings->get_option('ct_currency_symbol');
$currency_position = $settings->get_option('ct_currency_symbol_position');
$price_decimals = (int)$settings->get_option('ct_price_format_decimal_places');
if ($price_decimals < 0) {
	$price_decimals = 2;
}

$ksc_format_price = function ($amount) use ($currency_symbol, $currency_position, $price_decimals) {
	$amount = (float)$amount;
	$formatted = number_format($amount, $price_decimals, ',', '.');
	if ($currency_position === 'after' || $currency_position === 'Right' || $currency_position === 'right') {
		return $formatted . $currency_symbol;
	}
	return $currency_symbol . $formatted;
};

/* Cup24 UI — Italian labels */
$cta_label = 'Scegli orario e Prenota';
$load_more_label = 'Carica altri';
$available_label = 'Disponibile';
$duration_suffix = 'minuti';
$ksc_empty_label = 'Nessun servizio trovato.';

$ksc_slot_date = $available_label;
if (class_exists('IntlDateFormatter')) {
	try {
		$fmt = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE, date_default_timezone_get(), IntlDateFormatter::GREGORIAN, 'EEE, d MMM y');
		$formatted = $fmt->format(new DateTime('now'));
		if ($formatted) {
			$ksc_slot_date = ucfirst($formatted);
		}
	} catch (Exception $e) {
		$ksc_slot_date = date('D, j M Y');
	}
} else {
	$ksc_slot_date = date('D, j M Y');
}

$services = array();
if ($services_data) {
	while ($row = mysqli_fetch_assoc($services_data)) {
		$services[] = $row;
	}
}

if (count($services) === 0 && !isset($_GET['service'])) {
	echo '<div class="ct-sm-12 ct-md-12 ct-xs-12 ct-no-service-box">'
		. htmlspecialchars(isset($label_language_values['please_configure_first_cleaning_services_and_settings_in_admin_panel'])
			? $label_language_values['please_configure_first_cleaning_services_and_settings_in_admin_panel']
			: 'No services available.')
		. '</div>';
	return;
}

$total_services = count($services);
?>

<div id="ksc-service-results">
	<?php include dirname(__FILE__) . '/service_cards_list.php'; ?>
</div>

<div id="ksc-datetime-modal" class="ksc-dt-modal" hidden aria-hidden="true">
	<div class="ksc-dt-backdrop" data-ksc-dt-close></div>
	<div class="ksc-dt-dialog" role="dialog" aria-modal="true" aria-labelledby="ksc-dt-title">
		<a href="#" class="ksc-dt-close" data-ksc-dt-close aria-label="Chiudi"><span aria-hidden="true">×</span></a>
		<h3 id="ksc-dt-title" class="ksc-dt-title">Scegli data e ora</h3>

		<div class="ksc-dt-summary">
			<div class="ksc-dt-row">
				<span class="ksc-dt-label">Servizio</span>
				<span class="ksc-dt-value" id="ksc-dt-service">—</span>
			</div>
			<div class="ksc-dt-row" id="ksc-dt-duration-row" hidden>
				<span class="ksc-dt-label">Durata</span>
				<span class="ksc-dt-value" id="ksc-dt-duration">—</span>
			</div>
			<div class="ksc-dt-row" id="ksc-dt-price-row" hidden>
				<span class="ksc-dt-label">Prezzo</span>
				<span class="ksc-dt-value ksc-dt-price-value" id="ksc-dt-price">—</span>
			</div>
		</div>

		<div class="ksc-dt-calendar">
			<div class="ksc-dt-nav">
				<a href="#" class="ksc-dt-nav-btn is-disabled" id="ksc-dt-prev" aria-disabled="true">
					Precedente
				</a>
				<div class="ksc-dt-current-date" id="ksc-dt-date-label">—</div>
				<a href="#" class="ksc-dt-nav-btn" id="ksc-dt-next">
					Successivo
				</a>
			</div>
			<div class="ksc-dt-slots" id="ksc-dt-slots">
				<div class="ksc-dt-slots-loading">Caricamento orari…</div>
			</div>
		</div>

		<a href="#" class="ksc-dt-confirm is-disabled" id="ksc-dt-confirm" aria-disabled="true">Seleziona orario</a>
	</div>
</div>

<script>
	(function($) {
		function kscInitDatetimeModal() {
			var searchTimer = null;
			var searchUrl = (typeof ajaxurlObj !== 'undefined' && ajaxurlObj.ajax_url) ?
				ajaxurlObj.ajax_url + 'ksc_service_search_ajax.php' :
				'<?php echo AJAX_URL; ?>ksc_service_search_ajax.php';
			var checkoutSaveUrl = (typeof ajaxurlObj !== 'undefined' && ajaxurlObj.ajax_url) ?
				ajaxurlObj.ajax_url + 'ksc_save_checkout_session.php' :
				'<?php echo AJAX_URL; ?>ksc_save_checkout_session.php';
			var checkoutPageUrl = (typeof siteurlObj !== 'undefined' && siteurlObj.site_url) ?
				siteurlObj.site_url + 'checkout_one_step.php' :
				'<?php echo SITE_URL; ?>checkout_one_step.php';

			var kscDt = {
				open: false,
				dateIndex: 0,
				selectedSlot: null,
				selectedServiceId: null,
				preparedServiceId: null,
				waitTimer: null,
				slotWait: null,
				animTimer: null,
				animMs: 300
			};

			var kscDtI18n = {
				weekdays: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
				months: ['gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'],
				loading: 'Caricamento orari…',
				loadingAvail: 'Caricamento disponibilità…',
				noSlots: 'Nessun orario disponibile per questa data.',
				noCalendar: 'Calendario non disponibile.',
				calendarError: 'Impossibile caricare il calendario. Riprova.',
				selectSlot: 'Seleziona orario',
				addCart: 'Aggiungi al carrello',
				adding: 'Aggiunta in corso…',
				addFailed: 'Impossibile aggiungere al carrello. Riprova.'
			};

			function kscDtDates() {
				return $('#ct .cal_info .selected_date').toArray().sort(function(a, b) {
					return (parseInt($(a).attr('data-s_date'), 10) || 0) - (parseInt($(b).attr('data-s_date'), 10) || 0);
				});
			}

			function kscDtPad(n) {
				n = String(n);
				return n.length < 2 ? '0' + n : n;
			}

			/** Normalize Cleanto dates like 01-10-2026 / 1-10-2026 → 1-10-2026 */
			function kscDtNormDate(raw) {
				var parts = String(raw || '').split('-');
				if (parts.length < 3) return String(raw || '');
				return parseInt(parts[0], 10) + '-' + kscDtPad(parseInt(parts[1], 10)) + '-' + parseInt(parts[2], 10);
			}

			function kscDtParseRaw(raw) {
				var parts = String(raw || '').split('-');
				if (parts.length < 3) return null;
				var day = parseInt(parts[0], 10);
				var month = parseInt(parts[1], 10);
				var year = parseInt(parts[2], 10);
				if (!day || !month || !year) return null;
				return { day: day, month: month, year: year, raw: kscDtNormDate(raw) };
			}

			function kscDtFindDayEl(raw) {
				var want = kscDtNormDate(raw);
				var found = null;
				$('#ct .cal_info .selected_date').each(function () {
					if (kscDtNormDate($(this).attr('data-selected_dates')) === want) {
						found = this;
						return false;
					}
				});
				return found ? $(found) : $();
			}

			/** Ensure Cleanto calendar shows the month of preferredRaw, then run cb */
			function kscDtEnsureMonth(preferredRaw, cb) {
				var target = kscDtParseRaw(preferredRaw);
				if (!target) {
					cb(false);
					return;
				}
				if (kscDtFindDayEl(preferredRaw).length) {
					cb(true);
					return;
				}
				kscDtLoadCalendarMonth(target.month, target.year, function (ok) {
					cb(!!(ok && kscDtFindDayEl(preferredRaw).length));
				});
			}

			/** One AJAX: replace Cleanto calendar HTML for month/year */
			function kscDtLoadCalendarMonth(month, year, cb) {
				var ajax_url = (typeof ajaxurlObj !== 'undefined' && ajaxurlObj.ajax_url)
					? ajaxurlObj.ajax_url
					: '<?php echo AJAX_URL; ?>';
				$.ajax({
					type: 'POST',
					url: ajax_url + 'calendar_ajax.php',
					data: {
						month: kscDtPad(month),
						year: String(year),
						get_calendar: 1
					},
					success: function (res) {
						$('#ct .cal_info').html(res);
						var sample = ($('#ct .cal_info .selected_date').first().attr('data-selected_dates') || '');
						var cur = kscDtParseRaw(sample);
						var ok = !!(cur && cur.month === parseInt(month, 10) && cur.year === parseInt(year, 10));
						if (typeof cb === 'function') cb(ok);
					},
					error: function () {
						if (typeof cb === 'function') cb(false);
					}
				});
			}

			/** Fetch slots for a date string with exactly one get_slots AJAX */
			function kscDtTodayRaw() {
				var d = new Date();
				return d.getDate() + '-' + kscDtPad(d.getMonth() + 1) + '-' + d.getFullYear();
			}

			function kscDtFormatRawLabel(raw) {
				var p = kscDtParseRaw(raw);
				if (!p) return raw || '—';
				var d = new Date(p.year, p.month - 1, p.day);
				if (isNaN(d.getTime())) return raw;
				return kscDtI18n.weekdays[d.getDay()] + ' ' + p.day + ' ' + kscDtI18n.months[p.month - 1] + ' ' + p.year;
			}

			function kscDtSlotSource() {
				var $src = $('#ksc-dt-slot-source');
				if (!$src.length) {
					$src = $('<div id="ksc-dt-slot-source" class="ksc-dt-slot-source" style="display:none !important;" aria-hidden="true"></div>').appendTo('#ct');
				}
				return $src;
			}

			function kscDtBuildCleantoSlotEl(rawDate, timeHi, staffId) {
				var p = kscDtParseRaw(rawDate);
				var ymd = p ? (p.year + '-' + kscDtPad(p.month) + '-' + kscDtPad(p.day)) : '';
				var label = timeHi;
				var $li = $('<li class="time-slot br-2 time_slotss wer"></li>')
					.attr('data-slot_date_to_display', kscDtFormatRawLabel(rawDate))
					.attr('data-ct_date_selected', kscDtFormatRawLabel(rawDate))
					.attr('data-slot_date', rawDate)
					.attr('data-slot_time', label)
					.attr('data-slotdb_time', timeHi)
					.attr('data-slotdb_date', ymd)
					.attr('data-ct_time_selected', label)
					.attr('data-ksc-staff-id', staffId || '')
					.text(label);
				kscDtSlotSource().empty().append($('<ul class="list-inline time-slot-ul"></ul>').append($li));
				return $li;
			}

			/** One AJAX: API ∩ Doctor admin slots for service + date */
			function kscDtFetchSlotsByDate(selected_dates, serviceId, cb) {
				var raw = kscDtNormDate(selected_dates);
				if (!raw || !serviceId) {
					if (typeof cb === 'function') cb(false, []);
					return;
				}
				var ajax_url = (typeof ajaxurlObj !== 'undefined' && ajaxurlObj.ajax_url)
					? ajaxurlObj.ajax_url
					: '<?php echo AJAX_URL; ?>';
				$.ajax({
					type: 'POST',
					url: ajax_url + 'ksc_slots_ajax.php',
					dataType: 'json',
					data: {
						service_id: serviceId,
						date: raw
					},
					success: function (res) {
						var list = (res && res.ok && Array.isArray(res.slots)) ? res.slots : [];
						if (typeof cb === 'function') cb(true, list);
					},
					error: function () {
						if (typeof cb === 'function') cb(false, []);
					}
				});
			}

			function kscDtRenderIntersectedSlots(list, rawDate) {
				var $box = $('#ksc-dt-slots').empty();
				if (!list || !list.length) {
					$box.html('<div class="ksc-dt-slots-empty">' + kscDtI18n.noSlots + '</div>');
					kscDtSetConfirmEnabled(false);
					kscDt.selectedSlot = null;
					return;
				}
				var $grid = $('<div class="ksc-dt-slot-grid"></div>');
				list.forEach(function (slot) {
					var time = slot.time || slot.label || '';
					var $btn = $('<a href="#" class="ksc-dt-slot"></a>')
						.text(time)
						.attr('data-time', time)
						.attr('data-staff-id', slot.staff_id || '');
					$btn.data('slotMeta', {
						time: time,
						staff_id: slot.staff_id || 0,
						rawDate: rawDate
					});
					$grid.append($btn);
				});
				$box.append($grid);
				kscDtSetConfirmEnabled(false);
				kscDt.selectedSlot = null;
			}

			function kscDtFormatLabel($el) {
				var raw = $el.attr('data-selected_dates') || '';
				var parts = raw.split('-');
				if (parts.length < 3) return raw;
				var day = parseInt(parts[0], 10);
				var month = parseInt(parts[1], 10);
				var year = parseInt(parts[2], 10);
				var d = new Date(year, month - 1, day);
				if (isNaN(d.getTime())) return raw;
				return kscDtI18n.weekdays[d.getDay()] + ' ' + day + ' ' + kscDtI18n.months[month - 1] + ' ' + year;
			}

			function kscDtFormatTime($li) {
				var t = ($li.attr('data-slotdb_time') || '').trim();
				if (/^\d{1,2}:\d{2}/.test(t)) {
					var p = t.split(':');
					return kscDtPad(parseInt(p[0], 10)) + ':' + kscDtPad(parseInt(p[1], 10));
				}
				var text = $.trim($li.text());
				var m = text.match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
				if (!m) return text;
				var h = parseInt(m[1], 10);
				var min = parseInt(m[2], 10);
				var ap = (m[3] || '').toUpperCase();
				if (ap === 'PM' && h < 12) h += 12;
				if (ap === 'AM' && h === 12) h = 0;
				return kscDtPad(h) + ':' + kscDtPad(min);
			}

			function kscDtFormatDuration(raw) {
				raw = (raw || '').toString().trim();
				if (!raw) return '';
				return raw
					.replace(/\bminutes?\b/gi, 'minuti')
					.replace(/\bmins?\b/gi, 'minuti')
					.replace(/\bmin\b/gi, 'minuti');
			}

			function kscDtSetConfirmEnabled(on) {
				var $c = $('#ksc-dt-confirm');
				if (on) {
					$c.removeClass('is-disabled').attr('aria-disabled', 'false').text(kscDtI18n.addCart);
				} else {
					$c.addClass('is-disabled').attr('aria-disabled', 'true').text(kscDtI18n.selectSlot);
				}
			}

			function kscDtCartHasUnits(serviceId) {
				var sid = String(serviceId || kscDt.selectedServiceId || '');
				if (sid) {
					return $('#ct .cart_item_listing' + sid + ' > li').length > 0;
				}
				return $('#ct .cart_service_item_listing .ct-addon-items-list > li').length > 0 ||
					($('#ct #total_cart_count').length && parseInt($('#ct #total_cart_count').val(), 10) >= 2);
			}

			/** Cup24 is one service at a time — drop stale cart (e.g. leftover Linfotecar). */
			function kscDtResetCartUiAndSession(done) {
				var ajaxUrl = (typeof ajaxurlObj !== 'undefined' && ajaxurlObj.ajax_url)
					? ajaxurlObj.ajax_url
					: '<?php echo AJAX_URL; ?>';
				$('#ct .cart_service_item_listing').empty();
				$('#ct #total_cart_count').val('1');
				$('#ct .cart_sub_total, #ct .cart_total, #ct .cart_tax, #ct .cart_discount, #ct .frequent_discount, #ct .total_time_duration_text, #ct .remain_amount, #ct .partial_amount').empty();
				$.ajax({
					type: 'post',
					url: ajaxUrl + 'front_ajax.php',
					data: { ksc_reset_cart: 1 },
					complete: function() {
						if (typeof done === 'function') done();
					}
				});
			}

			/** Auto-pick Cleanto method/unit for the selected service. */
			function kscDtEnsureAddedToCart() {
				var sid = String(kscDt.selectedServiceId || '');
				if (kscDtCartHasUnits(sid)) return true;
				var $scope = sid
					? $('#ct .add_item_in_cart[data-service_id="' + sid + '"]')
					: $();
				var $pick = ($scope.length ? $scope : $('#ct .add_item_in_cart'))
					.filter(function() {
						var $el = $(this);
						var type = ($el.attr('data-type') || '').toLowerCase();
						return type === 'method_units' || type === '' || $el.hasClass('ct-duration-btn') || $el.hasClass('select_bedroom') || $el.hasClass('select_m_u_btn');
					})
					.first();
				if (!$pick.length) {
					$pick = ($scope.length ? $scope : $('#ct .add_item_in_cart')).first();
				}
				if (!$pick.length) return false;
				$pick.trigger('click');
				return true;
			}

			function kscDtScrollToCart() {
				jQuery('.hide_right_side_box').show();
				jQuery('.partial_amount_hide_on_load').show();
				jQuery('.cart_empty_msg').hide();
				var $cart = $('#ct .ct-cart-wrapper').first();
				if (!$cart.length) $cart = $('#ct .ct-main-right').first();
				if (!$cart.length) return;
				$cart.addClass('ksc-cart-pulse');
				setTimeout(function() {
					$cart.removeClass('ksc-cart-pulse');
				}, 1200);
				var top = $cart.offset() && $cart.offset().top;
				if (typeof top === 'number') {
					$('html, body').animate({
						scrollTop: Math.max(0, top - 24)
					}, 400);
				}
			}

			function kscDtSaveAndGoCheckout() {
				var staffId = '';
				if (kscDt.selectedSlot && kscDt.selectedSlot.staff_id) {
					staffId = String(kscDt.selectedSlot.staff_id);
				} else if ($('#ksc-staff-id').length) {
					staffId = String($('#ksc-staff-id').val() || '');
				}
				var payload = {
					service_title: $('#ksc-dt-service').text() || $('.sel-service').text() || '',
					cart_date: $('.cart_date').first().text() || '',
					cart_date_val: $('.cart_date').first().attr('data-date_val') || '',
					cart_time: $('.cart_time').first().text() || '',
					cart_time_val: $('.cart_time').first().attr('data-time_val') || '',
					cart_html: $('.cart_service_item_listing').first().html() || '',
					cart_sub_total: $('.cart_sub_total').first().html() || '',
					cart_tax: $('.cart_tax').first().html() || '',
					cart_total: $('.cart_total').first().html() || '',
					cart_discount: $('.cart_discount').first().html() || '',
					frequent_discount: $('.frequent_discount').first().html() || '',
					duration_text: $('.total_time_duration_text').first().html() || '',
					total_cart_count: $('#total_cart_count').val() || '2',
					staff_id: staffId
				};

				function goCheckout() {
					$.ajax({
						type: 'POST',
						url: checkoutSaveUrl,
						data: payload,
						dataType: 'json'
					}).always(function() {
						window.location.href = checkoutPageUrl;
					});
				}

				if (staffId) {
					var site_url = (typeof siteurlObj !== 'undefined' && siteurlObj.site_url)
						? siteurlObj.site_url
						: '<?php echo SITE_URL; ?>';
					$.ajax({
						type: 'post',
						url: site_url + 'front/firststep.php',
						data: { staff_id: staffId, get_staff_sess: 1 }
					}).always(goCheckout);
					return;
				}
				goCheckout();
			}

			function kscDtSetNavEnabled($el, on) {
				if (on) {
					$el.removeClass('is-disabled').attr('aria-disabled', 'false');
				} else {
					$el.addClass('is-disabled').attr('aria-disabled', 'true');
				}
			}

			function kscDtSetLoading(msg) {
				$('#ksc-dt-slots').html('<div class="ksc-dt-slots-loading">' + (msg || kscDtI18n.loading) + '</div>');
				kscDtSetConfirmEnabled(false);
				kscDt.selectedSlot = null;
			}

			/** Dates available on cards for the open service (j-m-Y), sorted */
			function kscDtServiceCardDates() {
				var sid = String(kscDt.selectedServiceId || '');
				var seen = {};
				var list = [];
				$('.ksc-book-btn').each(function () {
					if (String($(this).attr('data-id') || '') !== sid) return;
					var raw = kscDtNormDate($(this).attr('data-ksc-date'));
					if (!raw || seen[raw]) return;
					seen[raw] = true;
					list.push(raw);
				});
				list.sort(function (a, b) {
					var pa = kscDtParseRaw(a);
					var pb = kscDtParseRaw(b);
					if (!pa || !pb) return 0;
					return (pa.year - pb.year) || (pa.month - pb.month) || (pa.day - pb.day);
				});
				return list;
			}

			function kscDtLoadSlotsForCardDate(preferredRaw) {
				var raw = kscDtNormDate(preferredRaw);
				if (!raw || !kscDt.selectedServiceId) {
					kscDtSetLoading(kscDtI18n.calendarError);
					return;
				}
				$('#ksc-dt-date-label').text(kscDtFormatRawLabel(raw)).attr('data-raw', raw);
				var cardDates = kscDtServiceCardDates();
				var idx = cardDates.indexOf(raw);
				kscDt.dateIndex = idx >= 0 ? idx : 0;
				kscDtSetNavEnabled($('#ksc-dt-prev'), idx > 0);
				kscDtSetNavEnabled($('#ksc-dt-next'), idx >= 0 && idx < cardDates.length - 1);

				kscDtSetLoading();
				kscDtFetchSlotsByDate(raw, kscDt.selectedServiceId, function (ok, list) {
					if (!ok) {
						kscDtSetLoading(kscDtI18n.calendarError);
						return;
					}
					kscDtRenderIntersectedSlots(list, raw);
				});
			}

			function kscDtServiceReady(serviceId) {
				var sid = String(serviceId || kscDt.selectedServiceId || '');
				if (kscDtCartHasUnits(sid)) return true;
				/* Must wait for clickable unit buttons — method tab alone is not enough */
				if (sid && $('#ct .add_item_in_cart[data-service_id="' + sid + '"]').length > 0) return true;
				return $('#ct .add_item_in_cart[data-type="method_units"]').length > 0 ||
					$('#ct .duration_hrs .add_item_in_cart').length > 0 ||
					$('#ct .ser_design_3_units .add_item_in_cart').length > 0 ||
					$('#ct .ser_design_2_units .add_item_in_cart').length > 0;
			}

			/** Run Cleanto service selection once. Skip if already prepared for this service. */
			function kscDtPrepareService($btn, done) {
				var id = String($btn.attr('data-id') || '');
				if (!id) {
					if (typeof done === 'function') done();
					return;
				}
				if (String(kscDt.preparedServiceId) === id && kscDtCartHasUnits(id)) {
					if (typeof done === 'function') done();
					return;
				}

				function startPrepare() {
					kscDt.selectedServiceId = id;
					var $proxy = $('<button type="button" class="ser_details" style="display:none !important;" aria-hidden="true"></button>')
						.attr('data-id', id)
						.attr('data-servicetitle', $btn.attr('data-servicetitle') || '')
						.appendTo('#ct');
					$proxy.trigger('click');
					$proxy.remove();
					var tries = 0;
					var kickedDesign = false;
					clearInterval(kscDt.waitTimer);
					kscDt.waitTimer = setInterval(function () {
						tries++;
						var $method = $('#ct .s_m_units_design[data-service_id="' + id + '"]').first();
						if (!kickedDesign && $method.length && !$('#ct .add_item_in_cart[data-service_id="' + id + '"]').length) {
							kickedDesign = true;
							$method.trigger('click');
						}
						if (kscDtServiceReady(id) || tries > 60) {
							clearInterval(kscDt.waitTimer);
							kscDt.preparedServiceId = id;
							if (typeof done === 'function') done();
						}
					}, 100);
				}

				/* Switching service: clear leftover cart so prior Linfotecar does not short-circuit add */
				var otherInCart = $('#ct .cart_service_item_listing > li').filter(function() {
					return String($(this).attr('data-id') || $(this).data('id') || '') !== id;
				}).length > 0;
				var staleUnits = !kscDtCartHasUnits(id) && (
					$('#ct .cart_service_item_listing .ct-addon-items-list > li').length > 0 ||
					($('#ct #total_cart_count').length && parseInt($('#ct #total_cart_count').val(), 10) >= 2)
				);
				if (otherInCart || staleUnits || (kscDt.preparedServiceId && String(kscDt.preparedServiceId) !== id)) {
					kscDt.preparedServiceId = null;
					kscDtResetCartUiAndSession(startPrepare);
					return;
				}
				startPrepare();
			}

			function kscDtOpen($btn) {
				var title = $btn.attr('data-servicetitle') || '—';
				var price = $btn.attr('data-price') || '';
				var duration = kscDtFormatDuration($btn.attr('data-duration') || '');
				var preferredRaw = $btn.attr('data-ksc-date') || '';
				kscDt.selectedServiceId = String($btn.attr('data-id') || '');

				$('#ksc-dt-service').text(title);
				if (duration) {
					$('#ksc-dt-duration').text(duration);
					$('#ksc-dt-duration-row').prop('hidden', false);
				} else {
					$('#ksc-dt-duration-row').prop('hidden', true);
				}
				if (price) {
					$('#ksc-dt-price').text(price);
					$('#ksc-dt-price-row').prop('hidden', false);
				} else {
					$('#ksc-dt-price-row').prop('hidden', true);
				}

				kscDt.open = true;
				kscDt.selectedSlot = null;
				clearTimeout(kscDt.animTimer);
				var $modal = $('#ksc-datetime-modal');
				$modal
					.prop('hidden', false)
					.attr('aria-hidden', 'false')
					.addClass('is-animating')
					.removeClass('is-open');
				$('body').addClass('ksc-dt-open');
				requestAnimationFrame(function() {
					requestAnimationFrame(function() {
						$modal.addClass('is-open');
					});
				});
				/* Prefetch Cleanto cart prepare while user picks a slot */
				kscDtPrepareService($btn, function () {});
				/* One AJAX: API ∩ Doctor schedule */
				kscDtLoadSlotsForCardDate(preferredRaw);
			}

			function kscDtClose() {
				if (!kscDt.open && !$('#ksc-datetime-modal').hasClass('is-open')) return;
				kscDt.open = false;
				clearTimeout(kscDt.waitTimer);
				clearInterval(kscDt.slotWait);
				clearTimeout(kscDt.animTimer);
				kscDt.selectedSlot = null;

				var $modal = $('#ksc-datetime-modal');
				$modal.removeClass('is-open').attr('aria-hidden', 'true');

				var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
				var done = function() {
					$modal.prop('hidden', true).removeClass('is-animating');
					$('body').removeClass('ksc-dt-open');
				};
				if (reduceMotion) {
					done();
				} else {
					kscDt.animTimer = setTimeout(done, kscDt.animMs);
				}
			}

			$(document).on('click', '.ksc-book-btn', function(e) {
				e.preventDefault();
				e.stopPropagation();
				var $btn = $(this);
				var $card = $btn.closest('.ksc-service-card');
				$card.find('input[type="radio"]').prop('checked', true);
				$('.ksc-service-card').removeClass('is-selected');
				$card.addClass('is-selected');
			});

			/* Capture phase: open modal and block Cleanto .ser_details cascade */
			document.addEventListener('click', function(e) {
				var btn = e.target && e.target.closest ? e.target.closest('.ksc-book-btn') : null;
				if (!btn) return;
				e.preventDefault();
				e.stopPropagation();
				if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
				if (btn.getAttribute('data-ksc-skip-modal') === '1') {
					btn.removeAttribute('data-ksc-skip-modal');
					return;
				}
				kscDtOpen($(btn));
			}, true);

			$(document).on('click', '[data-ksc-dt-close]', function(e) {
				e.preventDefault();
				kscDtClose();
			});

			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && kscDt.open) kscDtClose();
			});

			$(document).on('click', '#ksc-dt-prev', function (e) {
				e.preventDefault();
				if ($(this).hasClass('is-disabled')) return;
				var cardDates = kscDtServiceCardDates();
				var idx = cardDates.indexOf(kscDtNormDate($('#ksc-dt-date-label').attr('data-raw')));
				if (idx > 0) {
					kscDtLoadSlotsForCardDate(cardDates[idx - 1]);
				}
			});

			$(document).on('click', '#ksc-dt-next', function (e) {
				e.preventDefault();
				if ($(this).hasClass('is-disabled')) return;
				var cardDates = kscDtServiceCardDates();
				var idx = cardDates.indexOf(kscDtNormDate($('#ksc-dt-date-label').attr('data-raw')));
				if (idx >= 0 && idx < cardDates.length - 1) {
					kscDtLoadSlotsForCardDate(cardDates[idx + 1]);
				}
			});

			$(document).on('click', '#ksc-dt-slots .ksc-dt-slot', function(e) {
				e.preventDefault();
				$('#ksc-dt-slots .ksc-dt-slot').removeClass('is-active');
				$(this).addClass('is-active');
				kscDt.selectedSlot = $(this).data('slotMeta') || null;
				kscDtSetConfirmEnabled(!!(kscDt.selectedSlot && kscDt.selectedSlot.time));
			});

			$(document).on('click', '#ksc-dt-confirm', function(e) {
				e.preventDefault();
				var $confirm = $(this);
				if ($confirm.hasClass('is-disabled') || $confirm.data('kscBusy')) return;
				if (!kscDt.selectedSlot || !kscDt.selectedSlot.time) return;

				$confirm.data('kscBusy', true).text(kscDtI18n.adding);
				var meta = kscDt.selectedSlot;
				var $btnForService = $('.ksc-service-card.is-selected .ksc-book-btn').first();
				if (!$btnForService.length) {
					$btnForService = $('.ksc-book-btn').filter(function() {
						return String($(this).attr('data-id')) === String(kscDt.selectedServiceId);
					}).first();
				}
				var $slotEl = kscDtBuildCleantoSlotEl(meta.rawDate || $('#ksc-dt-date-label').attr('data-raw'), meta.time, meta.staff_id);
				/* Prefer this Doctor in session; booking_complete re-checks who still has the slot */
				if (meta.staff_id) {
					var site_url = (typeof siteurlObj !== 'undefined' && siteurlObj.site_url)
						? siteurlObj.site_url
						: '<?php echo SITE_URL; ?>';
					$.ajax({
						type: 'post',
						url: site_url + 'front/firststep.php',
						data: { staff_id: meta.staff_id, get_staff_sess: 1 }
					});
				}
				kscDtPrepareService($btnForService.length ? $btnForService : $('<button>'), function() {
					if ($slotEl && $slotEl.length) {
						$slotEl.trigger('click');
					}
					kscDtEnsureAddedToCart();
					var tries = 0;

					function finishAdd() {
						kscDtEnsureAddedToCart();
						var sid = String(kscDt.selectedServiceId || '');
						var ready = kscDtCartHasUnits(sid) &&
							(($('.cart_sub_total').first().text() || '').trim() !== '' ||
								($('.cart_total').first().text() || '').trim() !== '' ||
								($('.cart_item_listing' + sid).find('> li').length > 0));
						if (ready) {
							kscDtSaveAndGoCheckout();
							return;
						}
						if (tries > 50) {
							$confirm.data('kscBusy', false).text(kscDtI18n.addCart);
							alert(kscDtI18n.addFailed);
							return;
						}
						tries++;
						setTimeout(finishAdd, 100);
					}
					setTimeout(finishAdd, 50);
				});
			});

			$(document).on('click', '#ksc-load-more-services', function(e) {
				e.preventDefault();
				var $btn = $(this);
				if ($btn.data('kscLoading') || $btn.prop('disabled') || $btn.hasClass('is-loading')) return;
				var offset = parseInt($btn.attr('data-offset'), 10) || 0;
				var total = parseInt($btn.attr('data-total'), 10) || 0;
				var q = $.trim($('#ksc-service-search').val() || '');
				$btn.data('kscLoading', true).prop('disabled', true).attr('aria-busy', 'true').addClass('is-loading');
				$.ajax({
					type: 'POST',
					url: searchUrl,
					dataType: 'json',
					data: {
						q: q,
						offset: offset,
						partial: '1'
					},
					success: function(res) {
						if (res && res.html) {
							$('#ksc-service-cards').append(res.html);
						}
						var next = res && typeof res.next === 'number' ? res.next : offset;
						var all = res && typeof res.total === 'number' ? res.total : total;
						$btn.attr('data-offset', next).attr('data-total', all);
						var remaining = all - next;
						if (remaining <= 0) {
							$('#ksc-load-more-wrap').addClass('is-hidden');
						}
					},
					complete: function() {
						$btn.data('kscLoading', false).prop('disabled', false).attr('aria-busy', 'false').removeClass('is-loading');
					}
				});
			});

			function kscLoadServices(q) {
				var $wrap = $('#ksc-service-results');
				$wrap.addClass('is-loading');
				$.ajax({
					type: 'POST',
					url: searchUrl,
					data: {
						q: q || ''
					},
					success: function(html) {
						$wrap.html(html);
					},
					error: function() {
						$wrap.html('<div class="ksc-service-empty">Errore di ricerca. Riprova.</div>');
					},
					complete: function() {
						$wrap.removeClass('is-loading');
					}
				});
			}

			$(document).on('input', '#ksc-service-search', function() {
				var q = $.trim($(this).val());
				clearTimeout(searchTimer);
				searchTimer = setTimeout(function() {
					kscLoadServices(q);
				}, 280);
			});

			$(document).on('keydown', '#ksc-service-search', function(e) {
				if (e.key === 'Escape') {
					$(this).val('');
					kscLoadServices('');
				}
			});
		} // end kscInitDatetimeModal

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function() {
				kscInitDatetimeModal();
			});
		} else {
			kscInitDatetimeModal();
		}
	})(jQuery);
</script>
