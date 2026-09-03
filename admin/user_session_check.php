<?php  
if (!isset($_SESSION)) {
    session_start();
}

$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
$isAdminArea = (strpos($scriptName, '/admin/') !== false);
$isStaffArea = (strpos($scriptName, '/staff/') !== false);

// Pages customers are allowed to access under /admin/
$customerAllowedPages = array(
    'my-appointments.php',
    'user-profile.php',
    'wallet-history.php',
    'user_referral_code.php',
);
$isCustomerAllowedPage = false;
foreach ($customerAllowedPages as $allowedPage) {
    if (strpos($scriptName, $allowedPage) !== false) {
        $isCustomerAllowedPage = true;
        break;
    }
}

// Customer Route Guard: redirect customers away from Root Admin screens
if ($isAdminArea && !$isCustomerAllowedPage && isset($_SESSION['ct_login_user_id']) && !isset($_SESSION['ct_adminid']) && !isset($_SESSION['ct_staffid'])) {
?>
	<script type="text/javascript">
		var loginObj = {'site_url':'<?php echo SITE_URL;?>'};
		var login_url = loginObj.site_url;
		window.location = login_url + "admin/my-appointments.php";
	</script>
<?php 
    exit;
}

// Doctor Route Guard: redirect Doctor away from Root Admin screens (never on /staff/)
if ($isAdminArea && !$isCustomerAllowedPage && isset($_SESSION['ct_staffid']) && !isset($_SESSION['ct_adminid'])) {
?>
	<script type="text/javascript">
		var loginObj = {'site_url':'<?php echo SITE_URL;?>'};
		var login_url = loginObj.site_url;
		window.location = login_url + "staff/staff-dashboard.php";
	</script>
<?php 
    exit;
}
?>
