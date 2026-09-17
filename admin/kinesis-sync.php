<?php
include(dirname(__FILE__) . '/header.php');
include(dirname(__FILE__) . '/user_session_check.php');
require_once(dirname(dirname(__FILE__)) . '/integrations/awwapi/AwwApiMigration.php');

$con = new cleanto_db();
$conn = $con->connect();
if (!isset($setting) || !$setting) {
  $setting = new cleanto_setting();
  $setting->conn = $conn;
}

AwwApiMigration::run($conn);

$kinesisLastTime = $setting->get_option('kinesis_last_sync_time') ?: 'Never';
$kinesisLastStatus = $setting->get_option('kinesis_last_sync_status') ?: 'Not synced yet';
$gcalLastTime = $setting->get_option('gcal_last_sync_time') ?: 'Never';
$gcalLastStatus = $setting->get_option('gcal_last_sync_status') ?: 'Not synced yet';
?>

<style>
/* Modern Sync Hub Styles */
.sync-hub-container {
  padding: 15px 35px 40px 35px;
  max-width: 100%;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

@media (max-width: 768px) {
  .sync-hub-container {
    padding: 10px 15px 30px 15px;
  }
}

/* Header Card */
.sync-header-box {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px 24px;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.sync-header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.sync-header-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-size: 22px;
  box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
  flex-shrink: 0;
}

.sync-header-title {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  line-height: 1.2;
}

.sync-header-subtitle {
  font-size: 13px;
  color: #64748b;
  margin-top: 4px;
}

.sync-header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.sync-btn-step {
  font-size: 13px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  text-decoration: none !important;
}

.sync-btn-step1 {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: #ffffff !important;
}

.sync-btn-step1:hover {
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
}

.sync-btn-step2 {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff !important;
}

.sync-btn-step2:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

.sync-btn-refresh {
  background: #f8fafc;
  color: #334155 !important;
  border: 1px solid #cbd5e1;
}

.sync-btn-refresh:hover {
  background: #e2e8f0;
  color: #0f172a !important;
  transform: translateY(-1px);
}

/* KPI Metric Cards */
.sync-kpi-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}

@media (max-width: 1200px) {
  .sync-kpi-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .sync-kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .sync-kpi-grid {
    grid-template-columns: 1fr;
  }
}

.sync-kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 18px;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}

.sync-kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
  border-color: #cbd5e1;
}

.sync-kpi-info {
  display: flex;
  flex-direction: column;
}

.sync-kpi-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #64748b;
  margin-bottom: 6px;
}

.sync-kpi-val {
  font-size: 26px;
  font-weight: 800;
  line-height: 1;
  color: #0f172a;
}

.sync-kpi-icon-box {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

/* KPI Theme Accents */
.kpi-total .sync-kpi-icon-box { background: #f1f5f9; color: #475569; }
.kpi-total .sync-kpi-val { color: #1e293b; }

.kpi-synced .sync-kpi-icon-box { background: #dcfce7; color: #15803d; }
.kpi-synced .sync-kpi-val { color: #15803d; }

.kpi-pending .sync-kpi-icon-box { background: #fef3c7; color: #b45309; }
.kpi-pending .sync-kpi-val { color: #d97706; }

.kpi-cancelled .sync-kpi-icon-box { background: #fee2e2; color: #b91c1c; }
.kpi-cancelled .sync-kpi-val { color: #dc2626; }

.kpi-gcal .sync-kpi-icon-box { background: #e0f2fe; color: #0369a1; }
.kpi-gcal .sync-kpi-val { color: #0284c7; }

.kpi-kinesis .sync-kpi-icon-box { background: #f3e8ff; color: #7e22ce; }
.kpi-kinesis .sync-kpi-val { color: #9333ea; }

/* Filter & Search Toolbar */
.sync-filter-toolbar {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 18px;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.sync-seg-group {
  display: inline-flex;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 8px;
  gap: 4px;
}

.sync-seg-btn {
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.sync-seg-btn:hover {
  color: #0f172a;
}

.sync-seg-btn.active {
  background: #ffffff;
  color: #0f172a;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
}

.sync-filter-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.sync-select-control {
  height: 38px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  padding: 6px 12px;
  font-size: 13px;
  color: #334155;
  background-color: #ffffff;
  outline: none;
  transition: border-color 0.2s;
}

.sync-select-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
}

.sync-search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.sync-search-input {
  height: 38px;
  width: 260px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  padding: 6px 36px 6px 12px;
  font-size: 13px;
  color: #334155;
  outline: none;
  transition: all 0.2s;
}

.sync-search-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
  width: 290px;
}

.sync-search-icon-btn {
  position: absolute;
  right: 8px;
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sync-search-icon-btn:hover {
  color: #3b82f6;
}

/* Data Table Container */
.sync-table-box {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
  overflow: hidden;
}

.sync-modern-table {
  width: 100%;
  margin-bottom: 0;
  border-collapse: collapse;
}

.sync-modern-table thead th {
  background-color: #f8fafc;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 14px 16px;
  border-bottom: 1px solid #e2e8f0 !important;
  border-top: none !important;
  vertical-align: middle;
}

.sync-modern-table tbody td {
  padding: 14px 16px;
  vertical-align: middle;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13px;
  color: #334155;
}

.sync-modern-table tbody tr:hover {
  background-color: #f8fafc;
}

/* Soft Badges */
.badge-soft-success { background: #dcfce7; color: #15803d; font-weight: 600; padding: 4px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
.badge-soft-warning { background: #fef3c7; color: #b45309; font-weight: 600; padding: 4px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
.badge-soft-danger { background: #fee2e2; color: #b91c1c; font-weight: 600; padding: 4px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
.badge-soft-info { background: #e0f2fe; color: #0369a1; font-weight: 600; padding: 4px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
.badge-soft-purple { background: #f3e8ff; color: #7e22ce; font-weight: 600; padding: 4px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
.badge-soft-gray { background: #f1f5f9; color: #475569; font-weight: 600; padding: 4px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
</style>

<div id="cta-sync-history-page" class="sync-hub-container">
  <!-- Header Card -->
  <div class="sync-header-box">
    <div class="sync-header-left">
      <div class="sync-header-icon">
        <i class="fa fa-history"></i>
      </div>
      <div>
        <h1 class="sync-header-title">Synchronization History &amp; Audit Logs</h1>
        <div class="sync-header-subtitle">
          Real-time tracking: <strong>Google Calendar &rarr; Local System DB (Step 1)</strong> &amp; <strong>Local System DB &rarr; Kinesis API (Step 2)</strong>
        </div>
      </div>
    </div>
    <div class="sync-header-actions">
      <button type="button" class="sync-btn-step sync-btn-step1" id="btn_sync_gcal_top" title="Import appointments from Google Calendar into local database">
        <i class="fa fa-calendar-check-o"></i> Step 1: Sync GCal &rarr; System
      </button>
      <button type="button" class="sync-btn-step sync-btn-step2" id="btn_sync_kinesis_top" title="Push pending appointments from local database to Kinesis API">
        <i class="fa fa-cloud-upload"></i> Step 2: Sync System &rarr; Kinesis
      </button>
      <button type="button" class="sync-btn-step sync-btn-refresh" id="btn_refresh_sync_history" title="Refresh synchronization history list">
        <i class="fa fa-refresh"></i> Refresh
      </button>
    </div>
  </div>

  <!-- Alert Message Box -->
  <div id="sync_page_alert" style="display: none; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: 500;"></div>

  <!-- KPI Statistics Cards -->
  <div class="sync-kpi-grid">
    <div class="sync-kpi-card kpi-total">
      <div class="sync-kpi-info">
        <div class="sync-kpi-title">Total Logs</div>
        <div class="sync-kpi-val" id="stat_total">0</div>
      </div>
      <div class="sync-kpi-icon-box">
        <i class="fa fa-database"></i>
      </div>
    </div>

    <div class="sync-kpi-card kpi-synced">
      <div class="sync-kpi-info">
        <div class="sync-kpi-title">Synced</div>
        <div class="sync-kpi-val" id="stat_synced">0</div>
      </div>
      <div class="sync-kpi-icon-box">
        <i class="fa fa-check-circle"></i>
      </div>
    </div>

    <div class="sync-kpi-card kpi-pending">
      <div class="sync-kpi-info">
        <div class="sync-kpi-title">Pending</div>
        <div class="sync-kpi-val" id="stat_pending">0</div>
      </div>
      <div class="sync-kpi-icon-box">
        <i class="fa fa-clock-o"></i>
      </div>
    </div>

    <div class="sync-kpi-card kpi-cancelled">
      <div class="sync-kpi-info">
        <div class="sync-kpi-title">Cancelled</div>
        <div class="sync-kpi-val" id="stat_cancelled">0</div>
      </div>
      <div class="sync-kpi-icon-box">
        <i class="fa fa-ban"></i>
      </div>
    </div>

    <div class="sync-kpi-card kpi-gcal">
      <div class="sync-kpi-info">
        <div class="sync-kpi-title">GCal Events</div>
        <div class="sync-kpi-val" id="stat_gcal">0</div>
      </div>
      <div class="sync-kpi-icon-box">
        <i class="fa fa-google"></i>
      </div>
    </div>

    <div class="sync-kpi-card kpi-kinesis">
      <div class="sync-kpi-info">
        <div class="sync-kpi-title">Kinesis Appts</div>
        <div class="sync-kpi-val" id="stat_kinesis">0</div>
      </div>
      <div class="sync-kpi-icon-box">
        <i class="fa fa-cloud"></i>
      </div>
    </div>
  </div>

  <!-- Filters & Search Toolbar -->
  <div class="sync-filter-toolbar">
    <div class="sync-seg-group" id="sync_type_filter">
      <button type="button" class="sync-seg-btn active" data-type="all">
        <i class="fa fa-list"></i> All Logs
      </button>
      <button type="button" class="sync-seg-btn" data-type="gcal">
        <i class="fa fa-google text-info"></i> Step 1: GCal &rarr; System
      </button>
      <button type="button" class="sync-seg-btn" data-type="kinesis">
        <i class="fa fa-cloud-upload text-success"></i> Step 2: System &rarr; Kinesis
      </button>
    </div>

    <div class="sync-filter-right">
      <select class="sync-select-control" id="sync_status_filter" style="width: 140px;">
        <option value="all">All Statuses</option>
        <option value="SYNCED">SYNCED</option>
        <option value="PENDING">PENDING</option>
        <option value="CANCELLED">CANCELLED</option>
        <option value="FAILED">FAILED</option>
      </select>

      <div class="sync-search-box">
        <input type="text" class="sync-search-input" id="sync_search_input" placeholder="Search customer, order, IDs..." />
        <button class="sync-search-icon-btn" id="btn_sync_search" type="button" title="Search">
          <i class="fa fa-search"></i>
        </button>
      </div>

      <select class="sync-select-control" id="sync_limit_select" style="width: 105px;">
        <option value="20">20 rows</option>
        <option value="50" selected>50 rows</option>
        <option value="100">100 rows</option>
      </select>
    </div>
  </div>

  <!-- Sync History Data Table Box -->
  <div class="sync-table-box">
    <div class="table-responsive">
      <table class="table sync-modern-table" id="sync_history_table">
        <thead>
          <tr>
            <th style="width: 145px;">Sync Time</th>
            <th style="width: 85px;">Order #</th>
            <th>Customer</th>
            <th style="width: 155px;">Appointment Date</th>
            <th>Service / Doctor</th>
            <th style="width: 120px;">GCal Event</th>
            <th style="width: 150px;">Kinesis ID</th>
            <th style="width: 90px;">Action</th>
            <th style="width: 105px;">Status</th>
            <th>Message</th>
            <th style="width: 80px; text-align: center;">Action</th>
          </tr>
        </thead>
        <tbody id="sync_history_tbody">
          <tr>
            <td colspan="11" class="text-center text-muted" style="padding: 40px;">
              <i class="fa fa-spinner fa-spin fa-2x" style="color: #3b82f6;"></i><br />
              <span style="margin-top: 10px; display: inline-block; font-weight: 500;">Loading synchronization history...</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function ($) {
  jQuery('.ct-loading-main').hide();
  var ajax_url = typeof ajaxurlObj !== 'undefined' ? ajaxurlObj.ajax_url : (typeof ajax_url !== 'undefined' ? ajax_url : '<?php echo AJAX_URL; ?>');

  fetchSyncHistory();

  function showAlert(msg, type) {
    var cls = type === 'success' ? 'alert alert-success' : (type === 'info' ? 'alert alert-info' : 'alert alert-danger');
    var icon = type === 'success' ? 'fa-check-circle' : (type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle');
    $("#sync_page_alert").removeClass('alert-success alert-info alert-danger').addClass(cls).html('<i class="fa ' + icon + '"></i> ' + msg).slideDown();
    setTimeout(function () {
      $("#sync_page_alert").slideUp();
    }, 6000);
  }

  function fetchSyncHistory() {
    var type = $("#sync_type_filter .sync-seg-btn.active").data("type") || "all";
    var status = $("#sync_status_filter").val() || "all";
    var search = $("#sync_search_input").val().trim();
    var limit = $("#sync_limit_select").val() || 50;

    var $tbody = $("#sync_history_tbody");
    $tbody.html('<tr><td colspan="11" class="text-center text-muted" style="padding: 35px;"><i class="fa fa-spinner fa-spin fa-2x" style="color: #3b82f6;"></i><br /><span style="margin-top: 8px; display: inline-block; font-weight: 500;">Loading synchronization history...</span></td></tr>');

    $.ajax({
      type: "post",
      url: ajax_url + "setting_ajax.php",
      dataType: "json",
      data: {
        action: "get_sync_history",
        type: type,
        status: status,
        search: search,
        limit: limit
      },
      success: function (res) {
        if (!res.success || !res.logs || res.logs.length === 0) {
          $tbody.html('<tr><td colspan="11" class="text-center text-muted" style="padding: 40px;"><i class="fa fa-info-circle fa-2x" style="color: #94a3b8;"></i><br /><span style="margin-top: 8px; display: inline-block; font-size: 14px;">No synchronization history found matching your filters.</span></td></tr>');
          updateStats(res.stats);
          return;
        }

        updateStats(res.stats);

        var html = "";
        $.each(res.logs, function (i, row) {
          var syncTime = row.updated_at || row.created_at || "-";
          var orderId = row.local_order_id ? ('#' + row.local_order_id) : (row.local_booking_id ? ('Bk #' + row.local_booking_id) : '-');
          var custName = row.customer_name || 'Guest';
          var custEmail = row.customer_email || '';
          var custPhone = row.customer_phone || '';
          var apptDate = row.event_start || row.booking_date_time || '-';
          var serviceName = row.service_name || '';
          if (!serviceName) {
            serviceName = (row.sync_status === 'FAILED') ? '—' : (row.event_summary || '—');
          }
          var staffName = row.staff_name ? ('<br /><small class="text-muted"><i class="fa fa-user-md"></i> ' + row.staff_name + '</small>') : '';

          var gcalBadge = '<span class="text-muted">-</span>';
          if (row.google_event_id) {
            var shortGId = row.google_event_id.length > 10 ? (row.google_event_id.substring(0, 10) + '...') : row.google_event_id;
            gcalBadge = '<span class="badge-soft-info" title="Google Event ID: ' + row.google_event_id + '"><i class="fa fa-google"></i> ' + shortGId + '</span>';
          }

          var kinesisBadges = '<span class="text-muted">None</span>';
          if (row.kinesis_appointment_id && row.kinesis_appointment_id > 0) {
            kinesisBadges = '<span class="badge-soft-purple" title="Kinesis Appointment ID">Appt #' + row.kinesis_appointment_id + '</span>';
            if (row.kinesis_customer_id && row.kinesis_customer_id > 0) {
              kinesisBadges += ' <span class="badge-soft-gray" title="Kinesis Customer ID">Cust #' + row.kinesis_customer_id + '</span>';
            }
          }

          var actionBadge = '<span class="badge-soft-gray">' + (row.sync_action || 'SYNC') + '</span>';
          if (row.sync_action === 'CREATE') {
            actionBadge = '<span class="badge-soft-success">CREATE</span>';
          } else if (row.sync_action === 'UPDATE') {
            actionBadge = '<span class="badge-soft-warning">UPDATE</span>';
          } else if (row.sync_action === 'CANCEL') {
            actionBadge = '<span class="badge-soft-danger">CANCEL</span>';
          }

          var statusBadge = '<span class="badge-soft-gray">' + (row.sync_status || 'UNKNOWN') + '</span>';
          if (row.sync_status === 'SYNCED') {
            statusBadge = '<span class="badge-soft-success"><i class="fa fa-check"></i> SYNCED</span>';
          } else if (row.sync_status === 'PENDING') {
            statusBadge = '<span class="badge-soft-warning"><i class="fa fa-clock-o"></i> PENDING</span>';
          } else if (row.sync_status === 'CANCELLED') {
            statusBadge = '<span class="badge-soft-danger"><i class="fa fa-ban"></i> CANCELLED</span>';
          } else if (row.sync_status === 'FAILED') {
            statusBadge = '<span class="badge-soft-danger"><i class="fa fa-times"></i> FAILED</span>';
          }

          var msg = row.last_sync_message || '-';

          var actionBtn = '-';
          if (row.sync_status === 'PENDING' && row.local_order_id) {
            actionBtn = '<button type="button" class="btn btn-xs btn-success btn-sync-single-order" data-order-id="' + row.local_order_id + '" title="Sync this appointment now to Kinesis API"><i class="fa fa-upload"></i> Sync</button>';
          }

          html += '<tr>' +
            '<td style="white-space: nowrap;"><small>' + syncTime + '</small></td>' +
            '<td><strong style="color: #0f172a;">' + orderId + '</strong></td>' +
            '<td><strong style="color: #0f172a;">' + custName + '</strong>' + (custEmail ? ('<br /><small class="text-muted">' + custEmail + (custPhone ? (' | ' + custPhone) : '') + '</small>') : '') + '</td>' +
            '<td style="white-space: nowrap;"><small>' + apptDate + '</small></td>' +
            '<td><strong>' + serviceName + '</strong>' + staffName + '</td>' +
            '<td>' + gcalBadge + '</td>' +
            '<td>' + kinesisBadges + '</td>' +
            '<td>' + actionBadge + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td><small style="color: #64748b;">' + msg + '</small></td>' +
            '<td style="text-align: center;">' + actionBtn + '</td>' +
            '</tr>';
        });

        $tbody.html(html);
      },
      error: function () {
        $tbody.html('<tr><td colspan="11" class="text-center text-danger" style="padding: 30px;"><i class="fa fa-exclamation-triangle"></i> Failed to load synchronization history.</td></tr>');
      }
    });
  }

  function updateStats(stats) {
    if (!stats) return;
    $("#stat_total").text(stats.total_count || 0);
    $("#stat_synced").text(stats.synced_count || 0);
    $("#stat_pending").text(stats.pending_count || 0);
    $("#stat_cancelled").text(stats.cancelled_count || 0);
    $("#stat_gcal").text(stats.gcal_count || 0);
    $("#stat_kinesis").text(stats.kinesis_count || 0);
  }

  $(document).on("click", "#btn_refresh_sync_history", function () {
    fetchSyncHistory();
  });

  $(document).on("click", "#sync_type_filter .sync-seg-btn", function () {
    $("#sync_type_filter .sync-seg-btn").removeClass("active");
    $(this).addClass("active");
    fetchSyncHistory();
  });

  $(document).on("change", "#sync_status_filter, #sync_limit_select", function () {
    fetchSyncHistory();
  });

  $(document).on("click", "#btn_sync_search", function () {
    fetchSyncHistory();
  });

  $(document).on("keypress", "#sync_search_input", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      fetchSyncHistory();
    }
  });

  $(document).on("click", ".btn-sync-single-order", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var orderId = $btn.data("order-id");
    $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
      type: "post",
      url: ajax_url + "setting_ajax.php",
      dataType: "json",
      data: {
        action: "sync_single_order_to_kinesis",
        order_id: orderId
      },
      success: function (res) {
        if (res.success) {
          showAlert("Order #" + orderId + " synced successfully to Kinesis API!", "success");
          fetchSyncHistory();
        } else {
          showAlert(res.error || "Failed to sync order.", "danger");
          $btn.prop("disabled", false).html('<i class="fa fa-upload"></i> Sync');
        }
      },
      error: function () {
        showAlert("Server error occurred while syncing order.", "danger");
        $btn.prop("disabled", false).html('<i class="fa fa-upload"></i> Sync');
      }
    });
  });

  /* Step 1: Google Calendar -> System Sync Trigger */
  $(document).on("click", "#btn_sync_gcal_top", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var origHtml = $btn.html();
    $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Syncing GCal &rarr; System...');

    $.ajax({
      type: "post",
      url: ajax_url + "setting_ajax.php",
      dataType: "json",
      data: {
        action: "sync_gcal_to_system",
        days_past: 7,
        days_future: 60
      },
      success: function (res) {
        $btn.prop("disabled", false).html(origHtml);
        if (res.success) {
          showAlert(res.message, "success");
          fetchSyncHistory();
        } else {
          showAlert(res.message, "danger");
        }
      },
      error: function () {
        $btn.prop("disabled", false).html(origHtml);
        showAlert("Error triggering Google Calendar synchronization.", "danger");
      }
    });
  });

  /* Step 2: System -> Kinesis Sync Trigger */
  $(document).on("click", "#btn_sync_kinesis_top", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var origHtml = $btn.html();
    $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Syncing System &rarr; Kinesis...');

    $.ajax({
      type: "post",
      url: ajax_url + "setting_ajax.php",
      dataType: "json",
      data: {
        action: "sync_system_to_kinesis"
      },
      success: function (res) {
        $btn.prop("disabled", false).html(origHtml);
        if (res.success) {
          showAlert(res.message, "success");
          fetchSyncHistory();
        } else {
          showAlert(res.message, "danger");
        }
      },
      error: function () {
        $btn.prop("disabled", false).html(origHtml);
        showAlert("Error triggering Kinesis API synchronization.", "danger");
      }
    });
  });
});
</script>

<?php
include(dirname(__FILE__) . '/footer.php');
?>
