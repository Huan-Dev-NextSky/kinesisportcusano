<?php
include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/user_session_check.php');
include(dirname(dirname(__FILE__)) . "/objects/class_adminprofile.php");
$con = new cleanto_db();
$conn = $con->connect();
$objadminprofile = new cleanto_adminprofile();
$objadminprofile->conn = $conn;
?>
<script type="text/javascript">
	var ajax_url = '<?php echo AJAX_URL;?>';
	var base_url = '<?php echo BASE_URL;?>';
	var profile_site_url = {'prof_site_url':'<?php echo SITE_URL;?>'};
</script>
<script>
jQuery(document).bind('ready ajaxComplete',function() {
	jQuery(".phone_number").intlTelInput({
		autoPlaceholder: false,
		utilsScript: "../assets/js/utils.js"
	});
});
</script>
<?php
$objadminprofile->id = $_SESSION['ct_adminid'];
$admininfo = $objadminprofile->readone();
$admin_name = isset($admininfo['fullname']) ? $admininfo['fullname'] : (isset($admininfo[3]) ? $admininfo[3] : '');
$admin_email = isset($admininfo['email']) ? $admininfo['email'] : (isset($admininfo[2]) ? $admininfo[2] : '');
$admin_phone = isset($admininfo['phone']) ? $admininfo['phone'] : (isset($admininfo[4]) ? $admininfo[4] : '');
$admin_address = isset($admininfo['address']) ? $admininfo['address'] : (isset($admininfo[5]) ? $admininfo[5] : '');
$admin_city = isset($admininfo['city']) ? $admininfo['city'] : (isset($admininfo[6]) ? $admininfo[6] : '');
$admin_state = isset($admininfo['state']) ? $admininfo['state'] : (isset($admininfo[7]) ? $admininfo[7] : '');
$admin_zip = isset($admininfo['zip']) ? $admininfo['zip'] : (isset($admininfo[8]) ? $admininfo[8] : '');
$admin_country = isset($admininfo['country']) ? $admininfo['country'] : (isset($admininfo[9]) ? $admininfo[9] : '');
$admin_pass = isset($admininfo['password']) ? $admininfo['password'] : (isset($admininfo[1]) ? $admininfo[1] : '');
$initials = '';
$parts = preg_split('/\s+/', trim($admin_name));
foreach ($parts as $p) {
	if ($p !== '') { $initials .= strtoupper(substr($p, 0, 1)); }
	if (strlen($initials) >= 2) break;
}
if ($initials === '') { $initials = 'A'; }
?>
<div id="cta-profile" class="panel tab-content">
	<div class="panel-body" style="padding:0;">
		<div class="ct-profile-shell">
			<div class="ct-profile-hero">
				<div class="ct-profile-avatar"><?php echo htmlspecialchars($initials); ?></div>
				<div class="ct-profile-hero-meta">
					<h2><?php echo htmlspecialchars($admin_name !== '' ? $admin_name : 'Administrator'); ?></h2>
					<p><?php echo htmlspecialchars($admin_email); ?></p>
					<span class="ct-profile-badge">Root Admin</span>
				</div>
			</div>

			<form novalidate="novalidate" id="admin_info_form">
				<div class="ct-profile-card">
					<h3><i class="fa fa-user"></i> <?php echo $label_language_values['personal_information']; ?></h3>
					<div class="ct-profile-grid">
						<div class="ct-profile-field">
							<label for="adminfullname"><?php echo $label_language_values['full_name']; ?></label>
							<input class="form-control" name="fullnamess" id="adminfullname" value="<?php echo htmlspecialchars($admin_name); ?>" type="text">
						</div>
						<div class="ct-profile-field">
							<label for="inputEmail"><?php echo $label_language_values['email']; ?></label>
							<input class="form-control admin_inputEmail" name="fullemail" id="inputEmail" value="<?php echo htmlspecialchars($admin_email); ?>" type="text">
							<input class="form-control admin_inputEmail_old" name="fullemailold" id="inputEmailold" value="<?php echo htmlspecialchars($admin_email); ?>" type="hidden">
						</div>
						<div class="ct-profile-field">
							<label for="adminphone"><?php echo $label_language_values['phone']; ?></label>
							<input type="tel" class="form-control" name="adminphoness" id="adminphone" value="<?php echo htmlspecialchars($admin_phone); ?>" />
						</div>
						<div class="ct-profile-field full">
							<label for="adminaddress"><?php echo $label_language_values['admin_profile_address']; ?></label>
							<textarea class="form-control" id="adminaddress" name="adminaddressss" cols="6"><?php echo htmlspecialchars($admin_address); ?></textarea>
						</div>
						<div class="ct-profile-field">
							<label for="admincity"><?php echo $label_language_values['city']; ?></label>
							<input class="form-control value_city" id="admincity" name="cityss" placeholder="<?php echo $label_language_values['city']; ?>" value="<?php echo htmlspecialchars($admin_city); ?>" type="text">
						</div>
						<div class="ct-profile-field">
							<label for="adminstate"><?php echo $label_language_values['state']; ?></label>
							<input class="form-control value_state" id="adminstate" name="state" placeholder="<?php echo $label_language_values['state']; ?>" value="<?php echo htmlspecialchars($admin_state); ?>" type="text">
						</div>
						<div class="ct-profile-field">
							<label for="adminzip"><?php echo $label_language_values['zip']; ?></label>
							<input class="form-control value_zip" id="adminzip" name="zipss" placeholder="<?php echo $label_language_values['zip']; ?>" value="<?php echo htmlspecialchars($admin_zip); ?>" type="text">
						</div>
						<div class="ct-profile-field">
							<label for="admincountry"><?php echo $label_language_values['country']; ?></label>
							<input class="form-control value_country" id="admincountry" name="countryss" placeholder="<?php echo $label_language_values['country']; ?>" value="<?php echo htmlspecialchars($admin_country); ?>" type="text">
						</div>
					</div>
				</div>

				<div class="ct-profile-card">
					<h3><i class="fa fa-lock"></i> <?php echo $label_language_values['change_password']; ?></h3>
					<div class="ct-profile-actions">
						<a href="javascript:void(0)" id="btn-change-pass" class="btn btn-link"><?php echo $label_language_values['change_password']; ?></a>
					</div>
					<div class="ct-change-password hide-div ct-profile-pass-card">
						<div class="ct-profile-grid">
							<div class="ct-profile-field full">
								<label for="oldpass"><?php echo $label_language_values['old_password']; ?></label>
								<input name="dboldpass" value="<?php echo htmlspecialchars($admin_pass); ?>" class="form-control" id="dboldpass" type="hidden">
								<input name="oldpass" class="form-control u_op" id="oldpass" type="password" value="">
								<label id="msg_oldps" class="old_pass_msg" style="display: none;"></label>
							</div>
							<div class="ct-profile-field">
								<label for="newpass"><?php echo $label_language_values['new_password']; ?></label>
								<input name="newpasswrd" class="form-control" id="newpass" type="password">
							</div>
							<div class="ct-profile-field">
								<label for="retypenewpass"><?php echo $label_language_values['retype_new_password']; ?></label>
								<input name="renewpasswrd" class="form-control u_rp" id="retypenewpass" type="password">
								<label id="msg_retype" class="retype_pass_msg"></label>
							</div>
						</div>
					</div>
					<div class="ct-profile-actions prof-suc-btn">
						<a href="javascript:void(0)" data-id="<?php echo $_SESSION['ct_adminid']; ?>" class="btn btn-success prf-btn ct-btn-width mybtnadminprofile_save"><?php echo $label_language_values['save']; ?></a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php include(dirname(__FILE__).'/footer.php'); ?>
