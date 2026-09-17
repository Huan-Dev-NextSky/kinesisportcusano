<?php
include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/admin_session_check.php');
include(dirname(dirname(__FILE__)) . "/objects/class_userdetails.php");
$con = new cleanto_db();
$conn = $con->connect();
$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;

$objuserdetails->id = $_SESSION['ct_login_user_id'];
$userinfo = $objuserdetails->readone_assoc();
$fn = isset($userinfo['first_name']) ? $userinfo['first_name'] : '';
$ln = isset($userinfo['last_name']) ? $userinfo['last_name'] : '';
$em = isset($userinfo['user_email']) ? $userinfo['user_email'] : '';
$display = trim($fn . ' ' . $ln);
$initials = '';
if ($fn !== '') { $initials .= strtoupper(substr($fn, 0, 1)); }
if ($ln !== '') { $initials .= strtoupper(substr($ln, 0, 1)); }
if ($initials === '' && $em !== '') { $initials = strtoupper(substr($em, 0, 1)); }
if ($initials === '') { $initials = 'C'; }
?>
<div id="cta-user-profile">
	<div class="panel-body" style="padding:0;">
		<div class="ct-profile-shell">
			<div class="ct-profile-hero">
				<div class="ct-profile-avatar"><?php echo htmlspecialchars($initials); ?></div>
				<div class="ct-profile-hero-meta">
					<h2><?php echo htmlspecialchars($display !== '' ? $display : 'Customer'); ?></h2>
					<p><?php echo htmlspecialchars($em); ?></p>
					<span class="ct-profile-badge">Customer</span>
				</div>
			</div>

			<form novalidate="novalidate" id="user_info_form">
				<div class="ct-profile-card">
					<h3><i class="fa fa-user"></i> <?php echo $label_language_values['personal_information']; ?></h3>
					<div class="ct-profile-grid">
						<div class="ct-profile-field">
							<label for="userfirstname"><?php echo $label_language_values['first_name']; ?></label>
							<input class="form-control" name="userfirstname" id="userfirstname" value="<?php echo htmlspecialchars($fn); ?>" type="text">
						</div>
						<div class="ct-profile-field">
							<label for="userlastname"><?php echo $label_language_values['last_name']; ?></label>
							<input class="form-control" name="userlastname" id="userlastname" value="<?php echo htmlspecialchars($ln); ?>" type="text">
						</div>
						<div class="ct-profile-field">
							<label for="inputEmail"><?php echo $label_language_values['email']; ?></label>
							<span class="form-control ct-profile-readonly"><?php echo htmlspecialchars($em); ?></span>
						</div>
						<div class="ct-profile-field">
							<label for="userphone"><?php echo $label_language_values['phone']; ?></label>
							<input type="tel" class="form-control phone_number" name="userphone" id="userphone" value="<?php echo htmlspecialchars(isset($userinfo['phone']) ? $userinfo['phone'] : ''); ?>" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" />
						</div>
						<div class="ct-profile-field">
							<label for="userdob">Date of Birth</label>
							<input type="date" class="form-control" name="userdob" id="userdob" value="<?php echo htmlspecialchars(isset($userinfo['dob']) ? $userinfo['dob'] : ''); ?>" />
						</div>
						<div class="ct-profile-field">
							<label for="useraddress"><?php echo $label_language_values['address']; ?></label>
							<input class="form-control" id="useraddress" name="useraddress" value="<?php echo htmlspecialchars(isset($userinfo['address']) ? $userinfo['address'] : ''); ?>" />
						</div>
						<div class="ct-profile-field">
							<label for="usercity"><?php echo $label_language_values['city']; ?></label>
							<input class="form-control value_city" id="usercity" name="usercity" placeholder="<?php echo $label_language_values['city']; ?>" value="<?php echo htmlspecialchars(isset($userinfo['city']) ? $userinfo['city'] : ''); ?>" type="text">
						</div>
						<div class="ct-profile-field">
							<label for="userstate"><?php echo $label_language_values['state']; ?></label>
							<input class="form-control value_state" id="userstate" name="userstate" placeholder="<?php echo $label_language_values['state']; ?>" value="<?php echo htmlspecialchars(isset($userinfo['state']) ? $userinfo['state'] : ''); ?>" type="text">
						</div>
						<?php if ($setting->get_option('ct_user_zip_code') == 'Y') { ?>
						<div class="ct-profile-field">
							<label for="userzip"><?php echo $label_language_values['zip']; ?></label>
							<input class="form-control value_zip" id="userzip" name="userzip" placeholder="<?php echo $label_language_values['zip']; ?>" value="<?php echo htmlspecialchars(isset($userinfo['zip']) ? $userinfo['zip'] : ''); ?>" type="text">
						</div>
						<?php } ?>
						<div class="ct-profile-field full">
							<div class="ct-profile-pref" id="ct-sms-pref-row">
								<span class="ct-profile-pref-icon"><i class="fa fa-commenting"></i></span>
								<span class="ct-profile-pref-text">
									<strong>SMS notifications</strong>
									<small>Receive booking alerts and appointment reminders by SMS</small>
								</span>
								<span class="ct-profile-pref-switch">
									<input type="checkbox" id="user_sms_opt_in" name="user_sms_opt_in" <?php if (!isset($userinfo['sms_opt_in']) || $userinfo['sms_opt_in'] === 'Y') { echo 'checked'; } ?> />
									<span class="ct-profile-pref-slider" aria-hidden="true"></span>
								</span>
							</div>
						</div>
						<script>
						(function () {
							var row = document.getElementById('ct-sms-pref-row');
							var box = document.getElementById('user_sms_opt_in');
							if (row && box) {
								row.addEventListener('click', function (e) {
									if (e.target === box) return;
									box.checked = !box.checked;
									box.dispatchEvent(new Event('change', { bubbles: true }));
								});
							}
						})();
						</script>
					</div>
				</div>

				<div class="ct-profile-card">
					<h3><i class="fa fa-lock"></i> <?php echo $label_language_values['change_password']; ?></h3>
					<div class="ct-profile-actions">
						<a href="javascript:void(0)" id="btn-change-pass" class="btn btn-link pl-0"><?php echo $label_language_values['change_password']; ?></a>
					</div>
					<div class="ct-change-password hide-div ct-profile-pass-card">
						<div class="ct-profile-grid">
							<div class="ct-profile-field full">
								<label for="useroldpass"><?php echo $label_language_values['old_password']; ?></label>
								<input name="userdboldpass" value="" class="form-control" id="userdboldpass" type="hidden">
								<input name="useroldpass" class="form-control u_op" id="useroldpass" type="password">
								<label id="msg_oldps" class="old_pass_msg"></label>
							</div>
							<div class="ct-profile-field">
								<label for="usernewpasswrd"><?php echo $label_language_values['new_password']; ?></label>
								<input name="usernewpasswrd" class="form-control" id="usernewpasswrd" type="password">
							</div>
							<div class="ct-profile-field">
								<label for="userrenewpasswrd"><?php echo $label_language_values['retype_new_password']; ?></label>
								<input name="userrenewpasswrd" class="form-control u_rp" id="userrenewpasswrd" type="password">
								<label id="msg_retype" class="retype_pass_msg"></label>
							</div>
						</div>
					</div>
					<div class="ct-profile-actions">
						<a href="javascript:void(0)" data-zip="<?php echo $setting->get_option('ct_user_zip_code'); ?>" data-id="<?php echo $_SESSION['ct_login_user_id']; ?>" class="btn btn-success ct-btn-width mybtnuserprofile_save"><?php echo $label_language_values['save']; ?></a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php include(dirname(__FILE__).'/footer.php'); ?>
