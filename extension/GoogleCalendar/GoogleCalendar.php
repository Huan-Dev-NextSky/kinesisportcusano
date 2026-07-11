<?php      
if(!function_exists("gc_setting_menu")){
function gc_setting_menu(){
	global $label_language_values;
	return '<li><a href="#google-calendar-settings" class="sot-form-fields" data-toggle="pill"><i class="fa fa-calendar fa-2x"></i><br />'.$label_language_values['Google_Calender_Settings'].'</a></li>';
}
}
if(!function_exists("gc_settings_menu_content")){
function gc_settings_menu_content(){
	global $label_language_values, $setting, $conn;
	mysqli_query($conn,"CREATE TABLE IF NOT EXISTS `ct_staff_gc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gc_id` varchar(70) DEFAULT NULL,
  `gc_status` enum('Y','N') NOT NULL DEFAULT 'N',
  `gc_token` text DEFAULT NULL,
  `gc_status_configure` enum('Y','N') NOT NULL DEFAULT 'N',
  `gc_status_sync_configure` enum('Y','N') NOT NULL DEFAULT 'N',
  `gc_client_id` varchar(200) DEFAULT NULL,
  `gc_client_secret` varchar(200) DEFAULT NULL,
  `gc_frontend_url` varchar(200) DEFAULT NULL,
  `gc_admin_url` varchar(200) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
	`gc_frontend_url` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
	$menu_content = '';
	$menu_content .= "<div class='tab-pane fade in' id='google-calendar-settings'>
				<form id='ct-gc-settings' method='post' type='' class='ct-labels-settings' >
                    <div class='panel panel-default'>
                        <div class='panel-heading cta-top-right'>
                            <h1 class='panel-title'>".$label_language_values['Google_Calender_Settings']." <a href='#gc_calender_settings' role='button' data-toggle='modal' class='btn btn-link'>[".$label_language_values['How_it_works']."]</a></h1>
                            <span class='pull-right cta-setting-fix-btn'> <a href='javascript:void(0);' class='btn btn-success save_google_calender_settings' type='submit'>".$label_language_values['save_setting']."</a></span>
                        </div>
						<div class='panel-body pt-50 plr-10'>
							<div class='table-responsive'>
								<table class='form-inline ct-common-table'>
									<tbody>
										<tr>
											<td><label>".$label_language_values['Add_Appointments_To_Google_Calender']."</label></td>
											<td>
												<div class='form-group'>
													<label class='ctoggle-gc-status' for='gc-status'>
														<input class='cta-toggle-checkbox' data-toggle='toggle' data-size='small' type='checkbox'";
														if($setting->get_option('ct_gc_status')=='Y'){
															$menu_content .= 'checked';
														}
															$menu_content .= " id='ct-gc-status' data-onstyle='success' data-offstyle='danger' />
													</label>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Id']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' class='form-control ct_gc_id' name='ct_gc_id v_c' size='50' data-names='ct_gc_id' value='".$setting->get_option('ct_gc_id')."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['Your_Google_calendar_id_where_you_need_to_get_alerts_its_normaly_your_Gmail_ID']."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Client_Id']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_client_id' name='ct_gc_client_id v_c' data-names='ct_gc_client_id' value='".$setting->get_option('ct_gc_client_id')."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['You_can_get_your_client_ID_from_your_Google_Calendar_Console']."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Client_Secret']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_client_secret' name='ct_gc_client_secret v_c' data-names='ct_gc_client_secret' value='".$setting->get_option('ct_gc_client_secret')."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['You_can_get_your_client_secret_from_your_Google_Calendar_Console']."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Frontend_URL']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_frontend_url' name='ct_gc_frontend_url v_c' data-names='ct_gc_frontend_url' value='".$setting->get_option('ct_gc_frontend_url')."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['its_your_Cleanto_booking_form_page_url']." e.g. ".SITE_URL."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Admin_URL']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_admin_url' name='ct_gc_admin_url v_c' data-names='ct_gc_admin_url' value='".$setting->get_option('ct_gc_admin_url')."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['Its_your_Cleanto_Google_Settings_page_url']." e.g. ".SITE_URL."admin/
													settings.php'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_API_Key']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_api_key' name='ct_gc_api_key v_c' data-names='ct_gc_api_key' value='".$setting->get_option('ct_gc_api_key')."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='Google Calendar API Key'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
									</tbody>
									<tfoot>
										<tr>
											<td></td>
											<td>
												<a id='gc_setting' name='' class='btn btn-success save_google_calender_settings' >".$label_language_values['save_setting']."</a>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</form>
				<!-- Modal HTML -->
				<div id='gc_calender_settings' class='modal fade'>
					<div class='modal-dialog'>
						<div class='modal-content'>
							<div class='modal-header'>
								<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
								<h4 class='modal-title'>Steps To Configure Google Calender Settings</h4>
							</div>
							<div class='modal-body pl-30'>
								<div>
									<h3 class='ml--15'>To find your client ID and client secret, do the following:</h3>
									<ol class='cta-ol-steps'>
										<li>Go to the <a style='color:#1E8CBE' href='https://console.developers.google.com/' target='_blank'>Google Developers Console</a>.</li>
										<li>Select a project, or create a new one.</li>
										<li>Click in the upper left part to see a sliding sidebar. Next, click <b>API Manager</b>. In the list of APIs look for <b>Calendar API</b> and make sure it is enabled.</li>
										<li>In the sidebar on the left, select <b>Credentials</b>.</li>
										<li>Go to <b>OAuth consent screen</b> tab and give a name to the product, then click <b>Save</b>.</li>
										<li>Go to <b>Credentials</b> tab and in <b>New credentials</b> drop-down menu select <b>OAuth client ID</b>.</li>
										<li>Select <b>Web application</b> and create your project's OAuth 2.0 credentials by providing the necessary information. For <b>Authorized redirect URIs</b> enter the <b>Redirect URI</b> found below on this page. Click <b>Create</b>.</li>
										<li>In the popup window look for the <b>Client ID</b> and <b>Client secret</b>. Use them in the form below on this page.</li>
										<li>Save Settings  and click <b>Verify Account</b> which is located at the bottom of this page.</li>
									</ol>
								</div>
							</div>
							<div class='modal-footer'>
								<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
							</div>
						</div>
					</div>
				</div>
				";
				if($setting->get_option('ct_gc_status')=='Y' && $setting->get_option('ct_gc_id') != '' && $setting->get_option('ct_gc_client_id') != '' && $setting->get_option('ct_gc_client_secret') != '') {
					$GcclientID = $setting->get_option('ct_gc_client_id');
					$GcclientSecret = $setting->get_option('ct_gc_client_secret');
					$GcEDvalue = $setting->get_option('ct_gc_status');
					$ct_gc_admin_url = $setting->get_option('ct_gc_admin_url');
					$ct_gc_api_key = $setting->get_option('ct_gc_api_key');
					$ct_gc_id = $setting->get_option('ct_gc_id');
					$client = new Google_Client();
					$client->setApplicationName('Google Calender');
					$client->setClientId($GcclientID);
					$client->setClientSecret($GcclientSecret);
					$client->setRedirectUri($ct_gc_admin_url);
					$client->setDeveloperKey($ct_gc_api_key);
					$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/calendar','https://www.google.com/calendar/feeds/'));
					$client->setAccessType('offline');
					$client->setApprovalPrompt( 'force' );
					
					if(isset($_GET['action']) && $_GET['action']=='gcd'){
						$revokeaccesstoken = $setting->get_option('ct_gc_token'); 
						$client->revokeToken($revokeaccesstoken);
					}
					
					
					if(isset($_GET['code']) && $_GET['code']!=''){
						$access_token =  $client->authenticate($_GET['code']);
						$setting->set_option('ct_gc_token',$access_token);
						header('Location:'.$ct_gc_admin_url);
					}
					
					$curlcalenders = curl_init();
					curl_setopt_array($curlcalenders, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/callist.php?pid=0',
						CURLOPT_FRESH_CONNECT =>true,
						CURLOPT_USERAGENT => 'Cleanto'
					));
					$response = curl_exec($curlcalenders);
					
					curl_close($curlcalenders);
					if(isset($response)){
						$calenders = json_decode($response);
					}else{
						$calenders = array();
					}
				$menu_content .= "<form id='ct-gc-configure' method='post' type='' class='ct-labels-settings'>
                    <div class='panel panel-default'>
                        <div class='panel-heading cta-top-right gc_config_panel'>
                            <h1 class='panel-title'>".$label_language_values['Google_Calender_Configuration']." </h1>
                        </div>
						<div class='panel-body pt-50 plr-10'>
							<div class='table-responsive'>
								<table class='form-inline ct-common-table'>
									<tbody>
										<tr>
											<td><label>".$label_language_values['Add_Appointments_To_Google_Calender']."</label></td>
											<td>
												<div class='form-group'>
													<label class='ctoggle-gc-status_configure' for='gc-status'>
														<input class='cta-toggle-checkbox' data-toggle='toggle' data-size='small' type='checkbox'"; 
														if($setting->get_option('ct_gc_status_configure')=='Y'){	
															$menu_content .= 'checked';
														} 
														$menu_content .= " id='ct-gc-status-configure' data-onstyle='success' data-offstyle='danger' />
													</label>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Two_Way_Sync']."</label></td>
											<td>
												<div class='form-group'>
													<label class='ctoggle-gc-sync_configure' for='gc-sync'>
														<input class='cta-toggle-checkbox' data-toggle='toggle' data-size='small' type='checkbox'"; 
														if($setting->get_option('ct_gc_status_sync_configure')=='Y'){	
															$menu_content .= 'checked';
														} 
														$menu_content .= " id='ct-gc-status-sync-configure' data-onstyle='success' data-offstyle='danger' />
													</label>
												</div>
											</td>
										</tr>";
										if(sizeof((array)$calenders)==0){
										$menu_content .= "<tr>
											<td><label></label></td>
											<td>
												<div class='form-group'>";
												$authUrl = $client->createAuthUrl();
												$menu_content .= "<a class='verify_gc_account' style='color:#1E8CBE' href='javascript:void(0)' data-hreflink=".$authUrl.">".$label_language_values['Verify_Account']."</a>
												</div>
											</td>
										</tr>";
										} else {
										$menu_content .= "<tr>
													<td>".$label_language_values['Select_Calendar']."</td>
													<td><select name='ct_gc_ids' class='selectpicker' id='ct_gc_ids'>";
													foreach($calenders as $value){
														foreach($value as $calinfo){
															$calenderInfo = explode('##==##',$calinfo);
															$selected='';
															if($ct_gc_id[0]==$calenderInfo[1]){ $selected='selected';}
															$menu_content .=  "<option ".$selected." value='".$calenderInfo[1]."'>".$calenderInfo[0]."</option>";
														}
													}
													$menu_content .=  "</select> <a style='text-decoration:underline;color:#1E8CBE;' href='' id='ct_gc_disconnect'>".$label_language_values['Disconnect']."</a></td>
												</tr>";
										}
									$menu_content .=  "</tbody>
									<tfoot>
										<tr>
											<td></td>
											<td>
												<a id='gc_setting_configure' name='' class='btn btn-success save_google_calender_configure' >".$label_language_values['save_setting']."</a>
												
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</form>
				";
				}
			$menu_content .=  "</div>";
			return $menu_content;
}
}
if(!function_exists("gc_staff_settings_menu_content")){
function gc_staff_settings_menu_content(){
	global $label_language_values, $setting;
	$menu_content = '';
	$menu_content .= "<div class='tab-pane fade in' id='google-calendar-settings'>
				<form id='ct-gc-settings' method='post' type='' class='ct-labels-settings' >
                    <div class='panel panel-default'>
                        <div class='panel-heading cta-top-right'>
                            <h1 class='panel-title'>".$label_language_values['Google_Calender_Settings']." <a href='#gc_calender_settings_staff' role='button' data-toggle='modal' class='btn btn-link'>[".$label_language_values['How_it_works']."]</a></h1>
                        </div>
						<div class='panel-body pt-50 plr-10'>
							<div class='table-responsive'>
								<table class='form-inline ct-common-table'>
									<tbody>
										<tr>
											<td><label>".$label_language_values['Add_Appointments_To_Google_Calender']."</label></td>
											<td>
												<div class='form-group'>
													<label class='ctoggle-gc-status' for='ct-gc-status'>
														<input class='cta-toggle-checkbox' data-toggle='toggle' data-size='small' type='checkbox'";
														if($setting->get_staff_option('gc_status',$_SESSION['ct_staffid'])=='Y'){
															$menu_content .= 'checked';
														}
															$menu_content .= " id='ct-gc-status' data-onstyle='success' data-offstyle='danger' />
													</label>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Id']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_id' name='ct_gc_id v_c' data-names='ct_gc_id' value='".$setting->get_staff_option('gc_id',$_SESSION['ct_staffid'])."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['Your_Google_calendar_id_where_you_need_to_get_alerts_its_normaly_your_Gmail_ID']."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Client_Id']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_client_id' name='ct_gc_client_id v_c' data-names='ct_gc_client_id' value='".$setting->get_staff_option('gc_client_id',$_SESSION['ct_staffid'])."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['You_can_get_your_client_ID_from_your_Google_Calendar_Console']."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Client_Secret']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_client_secret' name='ct_gc_client_secret v_c' data-names='ct_gc_client_secret' value='".$setting->get_staff_option('gc_client_secret',$_SESSION['ct_staffid'])."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['You_can_get_your_client_secret_from_your_Google_Calendar_Console']."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Frontend_URL']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_frontend_url' name='ct_gc_frontend_url v_c' data-names='ct_gc_frontend_url' value='".$setting->get_staff_option('gc_frontend_url',$_SESSION['ct_staffid'])."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['its_your_Cleanto_booking_form_page_url']." e.g. ".SITE_URL."'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Google_Calender_Admin_URL']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_admin_url' name='ct_gc_admin_url v_c' data-names='ct_gc_admin_url' value='".$setting->get_staff_option('gc_admin_url',$_SESSION['ct_staffid'])."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='".$label_language_values['Its_your_Cleanto_Google_Settings_page_url']." e.g. ".SITE_URL."admin/
													settings.php'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
																				<tr>
											<td><label>".$label_language_values['Google_Calender_API_Key']."</label></td>
											<td>
												<div class='form-group'>
													<input type='text' size='50' class='form-control ct_gc_api_key' name='ct_gc_api_key v_c' data-names='ct_gc_api_key' value='".$setting->get_staff_option('gc_api_key',$_SESSION['ct_staffid'])."'>
													<a class='ct-tooltip-link' href='#' data-toggle='tooltip' title='Google Calendar API Key'><i class='fa fa-info-circle fa-lg'></i></a>
												</div>
											</td>
										</tr>
									</tbody>
									<tfoot>
										<tr>
											<td></td>
											<td>
												<a href='javascript:void(0);' id='gc_setting' name='' class='btn btn-success save_google_calender_staff_settings' >".$label_language_values['save_setting']."</a>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</form>
				<!-- Modal HTML -->
				<div id='gc_calender_settings_staff' class='modal fade'>
					<div class='modal-dialog'>
						<div class='modal-content'>
							<div class='modal-header'>
								<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
								<h4 class='modal-title'>Steps To Configure Google Calender Settings</h4>
							</div>
							<div class='modal-body pl-30'>
								<div>
									<h3 class='ml--15'>To find your client ID and client secret, do the following:</h3>
									<ol class='cta-ol-steps'>
										<li>Go to the <a style='color:#1E8CBE' href='https://console.developers.google.com/' target='_blank'>Google Developers Console</a>.</li>
										<li>Select a project, or create a new one.</li>
										<li>Click in the upper left part to see a sliding sidebar. Next, click <b>API Manager</b>. In the list of APIs look for <b>Calendar API</b> and make sure it is enabled.</li>
										<li>In the sidebar on the left, select <b>Credentials</b>.</li>
										<li>Go to <b>OAuth consent screen</b> tab and give a name to the product, then click <b>Save</b>.</li>
										<li>Go to <b>Credentials</b> tab and in <b>New credentials</b> drop-down menu select <b>OAuth client ID</b>.</li>
										<li>Select <b>Web application</b> and create your project's OAuth 2.0 credentials by providing the necessary information. For <b>Authorized redirect URIs</b> enter the <b>Redirect URI</b> found below on this page. Click <b>Create</b>.</li>
										<li>In the popup window look for the <b>Client ID</b> and <b>Client secret</b>. Use them in the form below on this page.</li>
										<li>Save Settings  and click <b>Verify Account</b> which is located at the bottom of this page.</li>
									</ol>
								</div>
							</div>
							<div class='modal-footer'>
								<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
							</div>
						</div>
					</div>
				</div>";
				if($setting->get_staff_option('gc_status',$_SESSION['ct_staffid'])=='Y' && $setting->get_staff_option('gc_id',$_SESSION['ct_staffid']) != '' && $setting->get_staff_option('gc_client_id',$_SESSION['ct_staffid']) != '' && $setting->get_staff_option('gc_client_secret',$_SESSION['ct_staffid']) != '') {
					$GcclientID = $setting->get_staff_option('gc_client_id',$_SESSION['ct_staffid']);
					$ct_gc_api_key = $setting->get_staff_option('ct_gc_api_key',$_SESSION['ct_staffid']);
					$GcclientSecret = $setting->get_staff_option('gc_client_secret',$_SESSION['ct_staffid']);
					$GcEDvalue = $setting->get_staff_option('gc_status',$_SESSION['ct_staffid']);
					$ct_gc_admin_url = $setting->get_staff_option('gc_admin_url',$_SESSION['ct_staffid']);
					$ct_gc_id = $setting->get_staff_option('gc_id',$_SESSION['ct_staffid']);
					$client = new Google_Client();
					$client->setApplicationName('Google Calender');
					$client->setClientId($GcclientID);
					$client->setClientSecret($GcclientSecret);
					$client->setRedirectUri($ct_gc_admin_url);
					$client->setDeveloperKey($ct_gc_api_key);
					$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/calendar','https://www.google.com/calendar/feeds/'));
					$client->setAccessType('offline');
					$client->setApprovalPrompt( 'force' );
					
					if(isset($_GET['action']) && $_GET['action']=='gcd'){
						$revokeaccesstoken = $setting->get_staff_option('gc_token',$_SESSION['ct_staffid']);
						$client->revokeToken($revokeaccesstoken);
					}
					
					if(isset($_GET['code']) && $_GET['code']!=''){
						$access_token =  $client->authenticate($_GET['code']);
						$setting->set_staff_option('gc_token',$access_token,$_SESSION['ct_staffid']);
						header('Location:'.$ct_gc_admin_url);
					}
					
					$curlcalenders = curl_init();
					curl_setopt_array($curlcalenders, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/staff_callist.php?pid='.$_SESSION['ct_staffid'],
						CURLOPT_FRESH_CONNECT =>true,
						CURLOPT_USERAGENT => 'Cleanto'
					));
					$response = curl_exec($curlcalenders);
					/* print_r($response); */
					curl_close($curlcalenders);
					if(isset($response)){
						$calenders = json_decode($response);
					}else{
						$calenders = array();
					}
					
				$menu_content .= "<form id='ct-gc-configure' method='post' type='' class='ct-labels-settings'>
                    <div class='panel panel-default'>
                        <div class='panel-heading cta-top-right gc_config_panel'>
                            <h1 class='panel-title'>".$label_language_values['Google_Calender_Configuration']."</h1>
                        </div>
						<div class='panel-body pt-50 plr-10'>
							<div class='table-responsive'>
								<table class='form-inline ct-common-table'>
									<tbody>
										<tr>
											<td><label>".$label_language_values['Add_Appointments_To_Google_Calender']."</label></td>
											<td>
												<div class='form-group'>
													<label class='ctoggle-gc-status_configure' for='gc-status'>
														<input class='cta-toggle-checkbox' data-toggle='toggle' data-size='small' type='checkbox'"; 
														
														if($setting->get_staff_option('gc_status_configure',$_SESSION['ct_staffid'])=='Y'){	
															$menu_content .= 'checked';
														} 
														$menu_content .= " id='ct-gc-status-configure' data-onstyle='success' data-offstyle='danger' />
													</label>
												</div>
											</td>
										</tr>
										<tr>
											<td><label>".$label_language_values['Two_Way_Sync']."</label></td>
											<td>
												<div class='form-group'>
													<label class='ctoggle-gc-sync_configure' for='gc-sync'>
														<input class='cta-toggle-checkbox' data-toggle='toggle' data-size='small' type='checkbox'"; 
														if($setting->get_staff_option('gc_status_sync_configure',$_SESSION['ct_staffid'])=='Y'){	
															$menu_content .= 'checked';
														} 
														$menu_content .= " id='ct-gc-status-sync-configure' data-onstyle='success' data-offstyle='danger' />
													</label>
												</div>
											</td>
										</tr>";
										if(sizeof((array)$calenders)==0){
										$menu_content .= "<tr>
											<td><label></label></td>
											<td>
												<div class='form-group'>";
												$authUrl = $client->createAuthUrl();
												$menu_content .= "<a class='verify_gc_account' style='color:#1E8CBE' href='javascript:void(0)' data-hreflink=".$authUrl.">".$label_language_values['Verify_Account']."</a>
												</div>
											</td>
										</tr>";
										} else {
										$menu_content .= "<tr>
													<td>".$label_language_values['Select_Calendar']."</td>
													<td><select name='ct_gc_ids' class='selectpicker' id='ct_gc_ids'>";
													foreach($calenders as $value){
														foreach($value as $calinfo){
															$calenderInfo = explode('##==##',$calinfo);
															$selected='';
															if($ct_gc_id[0]==$calenderInfo[1]){ $selected='selected';}
															$menu_content .=  "<option ".$selected." value='".$calenderInfo[1]."'>".$calenderInfo[0]."</option>";
														}
													}
													$menu_content .=  "</select> <a style='text-decoration:underline;color:#1E8CBE;' href='javascript:void(0);' id='ct_gc_disconnect'>".$label_language_values['Disconnect']."</a></td>
												</tr>";
										}
									$menu_content .=  "</tbody>
									<tfoot>
										<tr>
											<td></td>
											<td>
												<a id='gc_setting_configure' name='' class='btn btn-success save_google_calender_staff_configure' >".$label_language_values['save_setting']."</a>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</form>
				";
				}
			$menu_content .=  "</div>";
			return $menu_content;
}
}
if(!function_exists("gc_settings_save_js")){
function gc_settings_save_js() {
	$settings_save_js = "<script type='text/javascript'>
			jQuery(document).on('click','.save_google_calender_settings',function () {
				jQuery('.ct-loading-main').show();
				var gc_status = jQuery('#ct-gc-status').prop('checked');
				if(gc_status == true) {
					var ct_gc_status = 'Y';
				} else {
					var ct_gc_status = 'N';
				}
				var ct_gc_id = jQuery('.ct_gc_id').val();
				var ct_gc_client_id = jQuery('.ct_gc_client_id').val();
				var ct_gc_client_secret = jQuery('.ct_gc_client_secret').val();
				var ct_gc_frontend_url = jQuery('.ct_gc_frontend_url').val();
				var ct_gc_admin_url = jQuery('.ct_gc_admin_url').val();
				var ct_gc_api_key = jQuery('.ct_gc_api_key').val();
				var datastring = {
					'ct_gc_status' : ct_gc_status,
					'ct_gc_id' : ct_gc_id,
					'ct_gc_client_id' : ct_gc_client_id,
					'ct_gc_client_secret' : ct_gc_client_secret,
					'ct_gc_frontend_url' : ct_gc_frontend_url,
					'ct_gc_admin_url' : ct_gc_admin_url,
					'ct_gc_api_key' : ct_gc_api_key,
					'ct_gc_settings' : '1'
				};
				jQuery('#ct-gc-settings').validate();
				
				jQuery('.ct_gc_id').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_ID}
				});
				jQuery('.ct_gc_client_id').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_client_ID}
				});
				jQuery('.ct_gc_client_secret').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_client_secret}
				});
				jQuery('.ct_gc_frontend_url').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_frontend_url}
				});
				jQuery('.ct_gc_admin_url').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_admin_url}
				});
				jQuery('.ct_gc_api_key').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_admin_url}
				});
				if(jQuery('#ct-gc-settings').valid()){
					jQuery.ajax({
						type: 'post',
						data: datastring,
						url: ajax_url + 'setting_ajax.php',
						success: function (res) {
							jQuery('.ct-loading-main').hide();
							location.reload();
						}
					});
				}else{
					jQuery('.ct-loading-main').hide();
				}
			});
		</script>
	";
	return $settings_save_js;
}
}
if(!function_exists("gc_staff_settings_save_js")){
function gc_staff_settings_save_js() {
	$settings_save_js = "<script type='text/javascript'>
			jQuery(document).on('click','.save_google_calender_staff_settings',function () {
				jQuery('.ct-loading-main').show();
				var gc_status = jQuery('#ct-gc-status').prop('checked');
				if(gc_status == true) {
					var ct_gc_status = 'Y';
				} else {
					var ct_gc_status = 'N';
				}
				var ct_gc_id = jQuery('.ct_gc_id').val();
				var ct_gc_client_id = jQuery('.ct_gc_client_id').val();
				var ct_gc_client_secret = jQuery('.ct_gc_client_secret').val();
				var ct_gc_frontend_url = jQuery('.ct_gc_frontend_url').val();
				var ct_gc_admin_url = jQuery('.ct_gc_admin_url').val();
				var ct_gc_api_key = jQuery('.ct_gc_api_key').val();
				var datastring = {
					'ct_gc_status' : ct_gc_status,
					'ct_gc_id' : ct_gc_id,
					'ct_gc_client_id' : ct_gc_client_id,
					'ct_gc_client_secret' : ct_gc_client_secret,
					'ct_gc_frontend_url' : ct_gc_frontend_url,
					'ct_gc_admin_url' : ct_gc_admin_url,
					'ct_gc_api_key' : ct_gc_api_key,
					'ct_gc_staff_settings' : '1'
				};
				jQuery('#ct-gc-settings').validate();
				
				jQuery('.ct_gc_id').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_ID}
				});
				jQuery('.ct_gc_client_id').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_client_ID}
				});
				jQuery('.ct_gc_client_secret').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_client_secret}
				});
				jQuery('.ct_gc_frontend_url').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_frontend_url}
				});
				jQuery('.ct_gc_admin_url').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_admin_url}
				});
				jQuery('.ct_gc_api_key').rules('add', {
					required:true,
					messages:{required:errorobj_please_enter_google_calender_admin_url}
				});
				if(jQuery('#ct-gc-settings').valid()){
					jQuery.ajax({
						type: 'post',
						data: datastring,
						url: ajax_url + 'setting_ajax.php',
						success: function (res) {
							jQuery('.ct-loading-main').hide();
							location.reload();
						}
					});
				}else{
					jQuery('.ct-loading-main').hide();
				}
			});
			function payway_aud_currency_check_js() { return ''; }
		</script>
	";
	return $settings_save_js;
}
}
if(!function_exists("gc_settings_save_ajax")){
function gc_settings_save_ajax() {
	global $setting;
	if(isset($_POST['ct_gc_settings']) && $_POST['ct_gc_settings'] == '1') {
		$ct_gc_status = $_POST['ct_gc_status'];
		$ct_gc_id = $_POST['ct_gc_id'];
		$ct_gc_client_id = $_POST['ct_gc_client_id'];
		$ct_gc_client_secret = $_POST['ct_gc_client_secret'];
		$ct_gc_frontend_url = $_POST['ct_gc_frontend_url'];
		$ct_gc_admin_url = $_POST['ct_gc_admin_url'];
		$ct_gc_api_key = $_POST['ct_gc_api_key'];
		$GC_option = array(
			'ct_gc_status'=>$ct_gc_status,
			'ct_gc_id'=>$ct_gc_id,
			'ct_gc_client_id'=>$ct_gc_client_id,
			'ct_gc_client_secret'=>$ct_gc_client_secret,
			'ct_gc_frontend_url'=>$ct_gc_frontend_url,
			'ct_gc_admin_url'=>$ct_gc_admin_url,
			'ct_gc_api_key'=>$ct_gc_api_key
		);
		foreach($GC_option as $option_key=>$option_value){
			$GC=$setting->set_option($option_key,$option_value);
		}
	}
}
}
if(!function_exists("gc_staff_settings_save_ajax")){
function gc_staff_settings_save_ajax() {
	global $setting;
	if(isset($_POST['ct_gc_staff_settings']) && $_POST['ct_gc_staff_settings'] == '1') {
		$ct_gc_status = $_POST['ct_gc_status'];
		$ct_gc_id = $_POST['ct_gc_id'];
		$ct_gc_client_id = $_POST['ct_gc_client_id'];
		$ct_gc_client_secret = $_POST['ct_gc_client_secret'];
		$ct_gc_frontend_url = $_POST['ct_gc_frontend_url'];
		$ct_gc_admin_url = $_POST['ct_gc_admin_url'];
		$ct_gc_api_key = $_POST['ct_gc_api_key'];
		$GC_option = array(
			'gc_status'=>$ct_gc_status,
			'gc_id'=>$ct_gc_id,
			'gc_client_id'=>$ct_gc_client_id,
			'gc_client_secret'=>$ct_gc_client_secret,
			'gc_frontend_url'=>$ct_gc_frontend_url,
			'gc_admin_url'=>$ct_gc_admin_url,
			'ct_gc_api_key'=>$ct_gc_api_key
		);
		foreach($GC_option as $option_key=>$option_value){
			/* echo $option_key."==".$option_value."==".$_SESSION['ct_staffid']; */
			$GC=$setting->set_staff_option($option_key,$option_value,$_SESSION['ct_staffid']);
		}
	}
}
}
if(!function_exists("gc_setting_configure_js")){
function gc_setting_configure_js() {
	$gc_settings_configure_js = "<script type='text/javascript'>
	jQuery(document).on('click','.save_google_calender_configure',function () {
		var ct_gc_id = jQuery('.ct_gc_id').val();
		var gc_status_configure = jQuery('#ct-gc-status-configure').prop('checked');
		if(gc_status_configure == true) {
			var ct_gc_status_configure = 'Y';
		} else {
			var ct_gc_status_configure = 'N';
		}
		var gc_sync_configure = jQuery('#ct-gc-status-sync-configure').prop('checked');
		if(gc_sync_configure == true) {
			var ct_gc_status_sync_configure = 'Y';
		} else {
			var ct_gc_status_sync_configure = 'N';
		}
		var datastring = {
			ct_gc_status_configure: ct_gc_status_configure,
			ct_gc_status_sync_configure: ct_gc_status_sync_configure,
			ct_gc_id: ct_gc_id,
			'gc_setting_configure': 1
		};
		jQuery('.ct-loading-main').show();
		jQuery.ajax({
			type: 'post',
			data: datastring,
			url: ajax_url + 'setting_ajax.php',
			success: function (res) {
				jQuery('.ct-loading-main').hide();
				location.reload();
			}
		});
	});
	</script>
	";
	return $gc_settings_configure_js;
}
}
if(!function_exists("gc_staff_setting_configure_js")){
function gc_staff_setting_configure_js() {
	$gc_settings_configure_js = "<script type='text/javascript'>
	jQuery(document).on('click','.save_google_calender_staff_configure',function () {
		var ct_gc_id = jQuery('.ct_gc_id').val();
		var gc_status_configure = jQuery('#ct-gc-status-configure').prop('checked');
		if(gc_status_configure == true) {
			var ct_gc_status_configure = 'Y';
		} else {
			var ct_gc_status_configure = 'N';
		}
		var gc_sync_configure = jQuery('#ct-gc-status-sync-configure').prop('checked');
		if(gc_sync_configure == true) {
			var ct_gc_status_sync_configure = 'Y';
		} else {
			var ct_gc_status_sync_configure = 'N';
		}
		var datastring = {
			ct_gc_status_configure: ct_gc_status_configure,
			ct_gc_status_sync_configure: ct_gc_status_sync_configure,
			ct_gc_id: ct_gc_id,
			'gc_staff_setting_configure': 1
		};
		jQuery('.ct-loading-main').show();
		jQuery.ajax({
			type: 'post',
			data: datastring,
			url: ajax_url + 'setting_ajax.php',
			success: function (res) {
				jQuery('.ct-loading-main').hide();
				location.reload();
			}
		});
	});
	</script>
	";
	return $gc_settings_configure_js;
}
}
if(!function_exists("gc_setting_configure_ajax")){
function gc_setting_configure_ajax() {
	global $setting;
	if(isset($_POST['gc_setting_configure']) && $_POST['gc_setting_configure'] == '1') {
		$ct_gc_id = $_POST['ct_gc_id'];
		$ct_gc_status_configure = $_POST['ct_gc_status_configure'];
		$ct_gc_status_sync_configure = $_POST['ct_gc_status_sync_configure'];
		$GC_id_configure = array(
			'ct_gc_id'=>$ct_gc_id,
			'ct_gc_status_configure'=>$ct_gc_status_configure,
			'ct_gc_status_sync_configure'=>$ct_gc_status_sync_configure
		);
		foreach($GC_id_configure as $option_key=>$option_value){
			$GC_id=$setting->set_option($option_key,$option_value);
		}
	}
}
}
if(!function_exists("gc_staff_setting_configure_ajax")){
function gc_staff_setting_configure_ajax() {
	global $setting;
	if(isset($_POST['gc_staff_setting_configure']) && $_POST['gc_staff_setting_configure'] == '1') {
		$ct_gc_id = $_POST['ct_gc_id'];
		$ct_gc_status_configure = $_POST['ct_gc_status_configure'];
		$ct_gc_status_sync_configure = $_POST['ct_gc_status_sync_configure'];
		$GC_id_configure = array(
			'gc_id'=>$ct_gc_id,
			'gc_status_configure'=>$ct_gc_status_configure,
			'gc_status_sync_configure'=>$ct_gc_status_sync_configure
		);
		foreach($GC_id_configure as $option_key=>$option_value){
			$GC_id=$setting->set_staff_option($option_key,$option_value,$_SESSION['ct_staffid']);
		}
	}
}
}
if(!function_exists("gc_setting_disconnect_js")){
function gc_setting_disconnect_js() {
	$gc_setting_disconnect_js = "<script type='text/javascript'>
	jQuery(document).on('click','#ct_gc_disconnect',function () {
		jQuery('.ct-loading-main').show();
		
		jQuery.ajax({
			type: 'post',
			data: {'disconnect_gc' : 1},
			url: ajax_url + 'setting_ajax.php',
			success: function (res) {
				jQuery('.ct-loading-main').hide();
			}
		});
	});
	</script>
	";
	return $gc_setting_disconnect_js;
}
}
if(!function_exists("gc_staff_setting_disconnect_js")){
function gc_staff_setting_disconnect_js() {
	$gc_staff_setting_disconnect_js = "<script type='text/javascript'>
	jQuery(document).on('click','#ct_gc_disconnect',function () {
		jQuery('.ct-loading-main').show();
		
		jQuery.ajax({
			type: 'post',
			data: {'disconnect_gc_staff' : 1},
			url: ajax_url + 'setting_ajax.php',
			success: function (res) {
				jQuery('.ct-loading-main').hide();
				location.reload();
			}
		});
	});
	</script>
	";
	return $gc_staff_setting_disconnect_js;
}
}
if(!function_exists("gc_setting_disconnect_ajax")){
function gc_setting_disconnect_ajax() {
	global $setting;
	if(isset($_POST['disconnect_gc']) && $_POST['disconnect_gc'] == '1') {
		$GC_option_unset = array(
			'ct_gc_status_configure'=>'N',
			'ct_gc_token'=>''
		);
		foreach($GC_option_unset as $option_key=>$option_value){
			$GC_unset=$setting->set_option($option_key,$option_value);
		}
	}
}
}
if(!function_exists("gc_staff_setting_disconnect_ajax")){
function gc_staff_setting_disconnect_ajax() {
	global $setting;
	if(isset($_POST['disconnect_gc_staff']) && $_POST['disconnect_gc_staff'] == '1') {
		$GC_option_unset = array(
			'gc_status_configure'=>'N',
			'gc_token'=>''
		);
		foreach($GC_option_unset as $option_key=>$option_value){
			$GC_unset=$setting->set_staff_option($option_key,$option_value,$_SESSION['ct_staffid']);
		}
	}
}
}
if(!function_exists("gc_setting_verify_js")){
function gc_setting_verify_js() {
	$gc_setting_verify_js = "<script type='text/javascript'>
	jQuery(document).on('click','.verify_gc_account',function () {
		var redirect_link = jQuery(this).data('hreflink');
		window.open(redirect_link, '_self');
	});
	</script>
	";
	return $gc_setting_verify_js;
}
}
if(!function_exists("gc_staff_setting_verify_js")){
function gc_staff_setting_verify_js() {
	$gc_staff_setting_verify_js = "<script type='text/javascript'>
	jQuery(document).on('click','.verify_gc_account',function () {
		var redirect_link = jQuery(this).data('hreflink');
		window.open(redirect_link, '_self');
	});
	</script>
	";
	return $gc_staff_setting_verify_js;
}
}
if(!function_exists("gc_add_booking_ajax")){
function gc_add_booking_ajax() {
	global $settings,$booking_date_time,$conn,$booking;
	$add_booking = '';
	if (!function_exists('ct_addevent_googlecalender_provider')) {
		function ct_addevent_googlecalender_provider($provider_id,$provider_gc_id,$gc_token,$summary,$location,$description,$event_color,$date,$start,$end,$GcclientID,$GcclientSecret,$GcEDvalue,$providerTZ,$ct_gc_frontend_url,$ct_gc_api_key){
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php";
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/contrib/Google_CalendarService.php";
			$clientP = new Google_Client();
			$clientP->setApplicationName("Cleanto Google Calender");
			$clientP->setClientId($GcclientID);
			$clientP->setClientSecret($GcclientSecret);			
			$clientP->setRedirectUri($ct_gc_frontend_url);
			$clientP->setDeveloperKey($ct_gc_api_key);
			$clientP->setScopes( 'https://www.googleapis.com/auth/calendar' );
			$clientP->setAccessType('offline');
			
			$calP = new Google_CalendarService($clientP);
			
			$clientP->setAccessToken($gc_token);
			$accesstoken = json_decode($gc_token);		
			
			if ($gc_token) {
				if ($clientP->isAccessTokenExpired()) {
					$clientP->refreshToken($accesstoken->refresh_token);
				}
			}
			if ($clientP->getAccessToken()){
				$startTP = new Google_EventDateTime();
				$endTP = new Google_EventDateTime();
				$eventP = new Google_Event();
				$calendarId = $provider_gc_id;
				$startTP->setTimeZone($providerTZ);
				$startTP->setDateTime($date."T".$start);
				$endTP->setTimeZone($providerTZ);
				$endTP->setDateTime($date."T".$end);
				$eventP->setSummary($summary);
				$eventP->setColorId($event_color);
				$eventP->setLocation($location);
				$eventP->setDescription($description);
				$eventP->setStart($startTP);
				$eventP->setEnd($endTP);
				
				$insert = $calP->events->insert($provider_gc_id,$eventP);
			}
			
			if(isset($insert)){
				return $insert;
			}else{
				return '';
			}		
		}	
	}
	/* Admin Booking GC */
	if(isset($_SESSION['ct_details']['email']) &&  $_SESSION['ct_details']['email']==''){
		$client_email = $_SESSION['ct_details']['existing_username'];	
	}else{
		$client_email = $_SESSION['ct_details']['email'];	
	}
	$ct_gc_frontend_url = $settings->get_option('ct_gc_frontend_url');
	$gc_token = $settings->get_option('ct_gc_token');
	$dateFormatted = date("Y-m-d h:i  A", strtotime($_SESSION['ct_details']['booking_date_time']));
	$currency_sybl_with_price = $settings->get_option('ct_currency_symbol').''.$_SESSION['ct_details']['net_amount'];									
	$summary = ucwords($_SESSION['ct_details']['firstname']).' '.ucwords($_SESSION['ct_details']['lastname']).'-'.ucwords($_SESSION['ct_details']['service_name']);
$description = '<table border="0"><tr><td>With</td><td>&nbsp;&nbsp;'.ucwords($_SESSION['ct_details']['firstname']).' '.ucwords($_SESSION['ct_details']['lastname']).'</td></tr><tr><td>For</td><td>&nbsp;&nbsp;'.ucwords($_SESSION['ct_details']['service_names']).'</td></tr></tr><tr><td>Email</td><td>&nbsp;&nbsp;'.$client_email.'</td></tr><tr><td>Phone</td><td>&nbsp;&nbsp;'.$_SESSION['ct_details']['phone'].'</td></tr><tr><td>Address</td><td>&nbsp;&nbsp;'.$_SESSION['ct_details']['full_address'].'</td></tr><tr><td>Booking Date</td><td>&nbsp;&nbsp;'.$dateFormatted.'</td></tr><tr><td>Methods</td><td>&nbsp;&nbsp;'.$_SESSION['ct_details']['uniquemethod_name'].'</td></tr><tr><td>Units</td><td>&nbsp;&nbsp;'.$_SESSION['ct_details']['commaSeparatedUnitNames'].'</td></tr><tr><td>Addons</td><td>&nbsp;&nbsp;'.$_SESSION['ct_details']['commaSeparatedAddonNames'].'</td></tr><tr><td>Price</td><td>&nbsp;&nbsp;'.$currency_sybl_with_price.'</td></tr><tr><td>Notes</td><td>&nbsp;&nbsp;'.$_SESSION['ct_details']['notes'].'</td></tr></table>';
	$location = $_SESSION['ct_details']['full_address'];
	$event_color = '9';
	$GcclientID = $settings->get_option('ct_gc_client_id');
	$ct_gc_api_key = $settings->get_option('ct_gc_api_key');
	$GcclientSecret = $settings->get_option('ct_gc_client_secret');
	$GcEDvalue = $settings->get_option('ct_gc_status_configure');
	$providerTZ = $settings->get_option('ct_timezone');
	$provider_gc_id = $settings->get_option('ct_gc_id');
	$provider_id = '';
	
	$datetime = explode(' ',$booking_date_time);
	$date = $datetime[0];
	$start = $datetime[1];
	if($_SESSION['time_duration'] != 0){
		$event_endtime = date('H:i:s',strtotime("+".$_SESSION['time_duration']." minutes", strtotime($datetime[1])));
	}else{
		if($settings->get_option('ct_time_interval') != ''){
			$event_endtime = date('H:i:s',strtotime("+".$settings->get_option('ct_time_interval')." minutes", strtotime($datetime[1])));
		}else{
			$event_endtime = date('H:i:s',strtotime("+30 minutes", strtotime($datetime[1])));
		}
	}
	$end = $event_endtime;
	
	if($gc_token != '' && $GcEDvalue == 'Y' && $GcclientID!='' && $GcclientSecret!=''){
		$event_Status = ct_addevent_googlecalender_provider($provider_id,$provider_gc_id,$gc_token,$summary,$location,$description,$event_color,$date,$start,$end,$GcclientID,$GcclientSecret,$GcEDvalue,$providerTZ,$ct_gc_frontend_url,$ct_gc_api_key);
		
		if(isset($event_Status) && $event_Status!=''){
			$gc_event_id = $event_Status['id'];
			$last_id = $booking->last_booking_id();
			$update_gc_event_query = $booking->update_gc_event_id($last_id,$gc_event_id);
		}
	}
}
}
if(!function_exists("gc_add_staff_booking_ajax")){
function gc_add_staff_booking_ajax() {
	global $settings,$booking_date_time,$conn,$booking;
	if (!function_exists('ct_staff_addevent_googlecalender_provider')) {
		function ct_staff_addevent_googlecalender_provider($provider_id,$provider_gc_id,$gc_token,$summary,$location,$description,$event_color,$date,$start,$end,$GcclientID,$GcclientSecret,$GcEDvalue,$providerTZ,$ct_gc_frontend_url,$ct_gc_api_key){
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php";
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/contrib/Google_CalendarService.php";
			$clientP = new Google_Client();
			$clientP->setApplicationName("Cleanto Google Calender");
			$clientP->setClientId($GcclientID);
			$clientP->setClientSecret($GcclientSecret);			
			$clientP->setRedirectUri($ct_gc_frontend_url);
			$clientP->setDeveloperKey($ct_gc_api_key);
			$clientP->setScopes( 'https://www.googleapis.com/auth/calendar' );
			$clientP->setAccessType('offline');
			
			$calP = new Google_CalendarService($clientP);
			
			$clientP->setAccessToken($gc_token);
			$accesstoken = json_decode($gc_token);		
			
			if ($gc_token) {
				if ($clientP->isAccessTokenExpired()) {
					$clientP->refreshToken($accesstoken->refresh_token);
				}
			}
			if ($clientP->getAccessToken()){
				$startTP = new Google_EventDateTime();
				$endTP = new Google_EventDateTime();
				$eventP = new Google_Event();
				$calendarId = $provider_gc_id;
				$startTP->setTimeZone($providerTZ);
				$startTP->setDateTime($date."T".$start);
				$endTP->setTimeZone($providerTZ);
				$endTP->setDateTime($date."T".$end);
				$eventP->setSummary($summary);
				$eventP->setColorId($event_color);
				$eventP->setLocation($location);
				$eventP->setDescription($description);
				$eventP->setStart($startTP);
				$eventP->setEnd($endTP);
				
				$insert = $calP->events->insert($provider_gc_id,$eventP);
			}
			
			if(isset($insert)){
				return $insert;
			}else{
				return '';
			}		
		}	
	}
	/* Staff Booking GC */
	if(isset($_SESSION['ct_details']['email']) &&  $_SESSION['ct_details']['email']==''){
		$client_email = $_SESSION['ct_details']['existing_username'];	
	}else{
		$client_email = $_SESSION['ct_details']['email'];	
	}
	$s_ct_gc_frontend_url = $settings->get_staff_option('gc_frontend_url',$_SESSION['ct_details']['staff_id']);
	$s_gc_token = $settings->get_staff_option('gc_token',$_SESSION['ct_details']['staff_id']);
	$s_summary= ucwords($_SESSION['ct_details']['firstname']).' '.ucwords($_SESSION['ct_details']['lastname']).'-'.ucwords($_SESSION['ct_details']['service_name']);
	$s_description= '<table border"0"><tr><td>With</td><td>&nbsp;&nbsp;'.ucwords($_SESSION['ct_details']['firstname']).' '.ucwords($_SESSION['ct_details']['lastname']).'</td></tr><tr><td>For</td><td>&nbsp;&nbsp;'.ucwords($_SESSION['ct_details']['service_name']).'</td></tr></tr><tr><td>Email</td><td>&nbsp;&nbsp;'.$client_email.'</td></tr><tr><td>Phone</td><td>&nbsp;&nbsp;'.$_SESSION['ct_details']['phone'].'</td></tr></table>';
	$s_location = '';
	$s_event_color = '9';
	$s_GcclientID = $settings->get_staff_option('gc_client_id',$_SESSION['ct_details']['staff_id']);
	$s_GcclientSecret = $settings->get_staff_option('gc_client_secret',$_SESSION['ct_details']['staff_id']);
	$s_GcEDvalue = $settings->get_staff_option('gc_status_configure',$_SESSION['ct_details']['staff_id']);
	$s_providerTZ = $settings->get_option('ct_timezone');
	$s_provider_gc_id = $settings->get_staff_option('gc_id',$_SESSION['ct_details']['staff_id']);
	$s_provider_id = '';
	
	$s_datetime = explode(' ',$booking_date_time);
	$s_date = $s_datetime[0];
	$s_start = $s_datetime[1];
	if($_SESSION['time_duration'] != 0){
		$s_event_endtime = date('H:i:s',strtotime("+".$_SESSION['time_duration']." minutes", strtotime($s_datetime[1])));
	}else{
		if($settings->get_option('ct_time_interval') != ''){
			$s_event_endtime = date('H:i:s',strtotime("+".$settings->get_option('ct_time_interval')." minutes", strtotime($s_datetime[1])));
		}else{
			$s_event_endtime = date('H:i:s',strtotime("+30 minutes", strtotime($s_datetime[1])));
		}
	}
	$s_end = $s_event_endtime;
	
	if($s_gc_token != '' && $s_GcEDvalue == 'Y' && $s_GcclientID!='' && $s_GcclientSecret!=''){
		$s_event_Status = ct_staff_addevent_googlecalender_provider($s_provider_id,$s_provider_gc_id,$s_gc_token,$s_summary,$s_location,$s_description,$s_event_color,$s_date,$s_start,$s_end,$s_GcclientID,$s_GcclientSecret,$s_GcEDvalue,$s_providerTZ,$s_ct_gc_frontend_url);
		
		if(isset($s_event_Status) && $s_event_Status!=''){
			$s_gc_event_id = $s_event_Status['id'];
			$s_last_id = $booking->last_booking_id();
			$s_update_gc_event_query = $booking->update_gc_staffid_event_id($s_last_id,$s_gc_event_id);
		}
	}
}
}
if(!function_exists("gc_reschedule_booking_ajax")){
function gc_reschedule_booking_ajax() {
	global $setting,$dates,$timess,$order_duration;
	$gcevent_id = $_POST['gc_event_id'];
	$gc_staff_event_id = $_POST['gc_staff_event_id'];
	$pid = $_POST['pid'];
	if(isset($gcevent_id) && $gcevent_id!='') {
		$provider_gc_id = $setting->get_option('ct_gc_id');
		$provider_gc_data = $setting->get_option('ct_gc_token');
		$GcclientID = $setting->get_option('ct_gc_client_id');
		$GcclientSecret = $setting->get_option('ct_gc_client_secret');
		$GcEDvalue = $setting->get_option('ct_gc_status_configure');
		$gc_admin_url = $setting->get_option('ct_gc_admin_url');
		$ct_gc_api_key = $setting->get_option('ct_gc_api_key');
		
		if($provider_gc_id!='' && $provider_gc_data!=''){					
			$providertzinfo = $setting->get_option('ct_timezone');
			if(isset($providertzinfo)){
				$providerTZ = $providertzinfo;
			}else{
				$providerTZ = $setting->get_option('ct_timezone');
				if($providerTZ == ''){
					$providerTZ='America/New_York';
				}
			}
			
			$date = date('Y-m-d',strtotime($dates));
			$start = date('H:i:s',strtotime($timess));
			if(isset($order_duration)){
				$event_endtime = date('H:i:s',strtotime("+".$order_duration." minutes", strtotime($timess)));
			}else{
				if($setting->get_option('ct_time_interval') != ''){
					$event_endtime = date('H:i:s',strtotime("+".$setting->get_option('ct_time_interval')." minutes", strtotime($timess)));
				}else{
					$event_endtime = date('H:i:s',strtotime("+30 minutes", strtotime($timess)));
				}
			}
			$end = $event_endtime;
		
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php";
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/contrib/Google_CalendarService.php";
			
			$provider_events  =	update_google_cal_event($provider_gc_id,$provider_gc_data,$gcevent_id,$date,$start,$end,$providerTZ,$GcclientID,$GcclientSecret,$GcEDvalue,$gc_admin_url,$ct_gc_api_key);
		}
	}
	if(isset($gc_staff_event_id) && $gc_staff_event_id!='') {
		$GcclientID = $setting->get_staff_option('gc_client_id',$_REQUEST['pid']);
		$GcclientSecret = $setting->get_staff_option('gc_client_secret',$_REQUEST['pid']);
		$GcEDvalue = $setting->get_staff_option('gc_status',$_REQUEST['pid']);
		$provider_gc_data = $setting->get_staff_option('gc_token',$_REQUEST['pid']);
		$provider_gc_id = $setting->get_staff_option('gc_id',$_REQUEST['pid']);
		$gc_admin_url = $setting->get_staff_option('gc_admin_url',$_REQUEST['pid']);
		$ct_gc_api_key = $setting->get_staff_option('ct_gc_api_key',$_REQUEST['pid']);
		
		if($provider_gc_id!='' && $provider_gc_data!=''){					
			$providertzinfo = $setting->get_option('ct_timezone');
			if(isset($providertzinfo)){
				$providerTZ = $providertzinfo;
			}else{
				$providerTZ = $setting->get_option('ct_timezone');
				if($providerTZ == ''){
					$providerTZ='America/New_York';
				}
			}
			
			$date = date('Y-m-d',strtotime($dates));
			$start = date('H:i:s',strtotime($timess));
			if(isset($order_duration)){
				$event_endtime = date('H:i:s',strtotime("+".$order_duration." minutes", strtotime($timess)));
			}else{
				if($setting->get_option('ct_time_interval') != ''){
					$event_endtime = date('H:i:s',strtotime("+".$setting->get_option('ct_time_interval')." minutes", strtotime($timess)));
				}else{
					$event_endtime = date('H:i:s',strtotime("+30 minutes", strtotime($timess)));
				}
			}
			$end = $event_endtime;
		
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php";
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/contrib/Google_CalendarService.php";
			
			$provider_events  =	update_google_cal_event($provider_gc_id,$provider_gc_data,$gc_staff_event_id,$date,$start,$end,$providerTZ,$GcclientID,$GcclientSecret,$GcEDvalue,$gc_admin_url,$ct_gc_api_key);
		}
	}
}
}
if(!function_exists("gc_reschedule_booking_by_reminder_ajax")){
function gc_reschedule_booking_by_reminder_ajax() {
	global $setting,$dates,$timess,$pid,$gcevent_id,$gc_staff_event_id;
	if(isset($gcevent_id) && $gcevent_id!='') {
		$provider_gc_id = $setting->get_option('ct_gc_id');
		$provider_gc_data = $setting->get_option('ct_gc_token');
		$GcclientID = $setting->get_option('ct_gc_client_id');
		$GcclientSecret = $setting->get_option('ct_gc_client_secret');
		$GcEDvalue = $setting->get_option('ct_gc_status_configure');
		$gc_admin_url = $setting->get_option('ct_gc_admin_url');
		$ct_gc_api_key = $setting->get_option('ct_gc_api_key');
		
		if($provider_gc_id!='' && $provider_gc_data!=''){					
			$providertzinfo = $setting->get_option('ct_timezone');
			if(isset($providertzinfo)){
				$providerTZ = $providertzinfo;
			}else{
				$providerTZ = $setting->get_option('ct_timezone');
				if($providerTZ == ''){
					$providerTZ='America/New_York';
				}
			}
			
			$date = date('Y-m-d',strtotime($dates));
			$start = date('H:i:s',strtotime($timess));
			if($setting->get_option('ct_time_interval') != ''){
				$event_endtime = date('H:i:s',strtotime("+".$setting->get_option('ct_time_interval')." minutes", strtotime($timess)));
			}else{
				$event_endtime = date('H:i:s',strtotime("+30 minutes", strtotime($timess)));
			}
			$end = $event_endtime;
		
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php";
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/contrib/Google_CalendarService.php";
			
			$provider_events  =	update_google_cal_event($provider_gc_id,$provider_gc_data,$gcevent_id,$date,$start,$end,$providerTZ,$GcclientID,$GcclientSecret,$GcEDvalue,$gc_admin_url,$ct_gc_api_key);
		}
	}
	if(isset($gc_staff_event_id) && $gc_staff_event_id!='') {
		$GcclientID = $setting->get_staff_option('gc_client_id',$_REQUEST['pid']);
		$GcclientSecret = $setting->get_staff_option('gc_client_secret',$_REQUEST['pid']);
		$GcEDvalue = $setting->get_staff_option('gc_status',$_REQUEST['pid']);
		$provider_gc_data = $setting->get_staff_option('gc_token',$_REQUEST['pid']);
		$provider_gc_id = $setting->get_staff_option('gc_id',$_REQUEST['pid']);
		$gc_admin_url = $setting->get_staff_option('gc_admin_url',$_REQUEST['pid']);
		$ct_gc_api_key = $setting->get_staff_option('ct_gc_api_key',$_REQUEST['pid']);
		
		if($provider_gc_id!='' && $provider_gc_data!=''){					
			$providertzinfo = $setting->get_option('ct_timezone');
			if(isset($providertzinfo)){
				$providerTZ = $providertzinfo;
			}else{
				$providerTZ = $setting->get_option('ct_timezone');
				if($providerTZ == ''){
					$providerTZ='America/New_York';
				}
			}
			
			$date = date('Y-m-d',strtotime($dates));
			$start = date('H:i:s',strtotime($timess));
			if($setting->get_option('ct_time_interval') != ''){
				$event_endtime = date('H:i:s',strtotime("+".$setting->get_option('ct_time_interval')." minutes", strtotime($timess)));
			}else{
				$event_endtime = date('H:i:s',strtotime("+30 minutes", strtotime($timess)));
				$end = $event_endtime;
			}
		
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php";
			require_once dirname(dirname(dirname(__FILE__)))."/extension/GoogleCalendar/google-api-php-client/src/contrib/Google_CalendarService.php";
			
			$provider_events  =	update_google_cal_event($provider_gc_id,$provider_gc_data,$gc_staff_event_id,$date,$start,$end,$providerTZ,$GcclientID,$GcclientSecret,$GcEDvalue,$gc_admin_url,$ct_gc_api_key);
		}
	}
}
}
if(!function_exists("gc_cancel_reject_booking_ajax")){
function gc_cancel_reject_booking_ajax(){
	global $gc_staff_event_id, $gc_event_id, $pid;
	if(isset($gc_event_id) && $gc_event_id != ''){
		$curldeleteevent = curl_init();
		curl_setopt_array($curldeleteevent, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/deleteevent.php?eid='.$gc_event_id.'&pid=0',
			CURLOPT_FRESH_CONNECT =>true,
			CURLOPT_USERAGENT => 'Cleanto'
		));
		$respdelete = curl_exec($curldeleteevent);
		curl_close($curldeleteevent);
	}
	if(isset($gc_staff_event_id) && $gc_staff_event_id != ''){
		$providers = explode(',',$pid);
		foreach($providers as $p){
			$curlstaffdeleteevent = curl_init();
			curl_setopt_array($curlstaffdeleteevent, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/staff_deleteevent.php?eid='.$gc_staff_event_id.'&pid='.$p,
				CURLOPT_FRESH_CONNECT =>true,
				CURLOPT_USERAGENT => 'Cleanto'
			));
			$respdelete = curl_exec($curlstaffdeleteevent);
			curl_close($curlstaffdeleteevent);
		}
	}
}
}
if(!function_exists("gc_remove_sampledata_booking_ajax")){
function gc_remove_sampledata_booking_ajax(){
	global $dummy,$conn;
	
	$q2 = "select `option_value` from `ct_settings` where `option_name` = 'ct_remove_data_array'";
	$reslt=mysqli_query($conn,$q2);
	$val = mysqli_fetch_array($reslt);
	$id = explode(',',$val[0]);
	
	for($i=0;$i<=3;$i++){		
		$bookings_list = $dummy->get_all_bookings_byserviceid($id[$i]);
		while($booking = mysqli_fetch_array($bookings_list)){
			$gc_staff_event_id = $booking['gc_staff_event_id'];
			$gc_event_id = $booking['gc_event_id'];
			$pid = $booking['staff_ids'];
			if(isset($gc_event_id) && $gc_event_id != ''){
				$curldeleteevent = curl_init();
				curl_setopt_array($curldeleteevent, array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/deleteevent.php?eid='.$gc_event_id.'&pid=0',
					CURLOPT_FRESH_CONNECT =>true,
					CURLOPT_USERAGENT => 'Cleanto'
				));
				$respdelete = curl_exec($curldeleteevent);
				curl_close($curldeleteevent);
			}
			if(isset($gc_staff_event_id) && $gc_staff_event_id != ''){
				$providers = explode(',',$pid);
				foreach($providers as $p){
					$curlstaffdeleteevent = curl_init();
					curl_setopt_array($curlstaffdeleteevent, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/staff_deleteevent.php?eid='.$gc_staff_event_id.'&pid='.$p,
						CURLOPT_FRESH_CONNECT =>true,
						CURLOPT_USERAGENT => 'Cleanto'
					));
					$respdelete = curl_exec($curlstaffdeleteevent);
					curl_close($curlstaffdeleteevent);
				}
			}
		}
	}
}
}
if(!function_exists("update_google_cal_event")){
function update_google_cal_event($calendarId,$provider_access_token,$eventid,$date,$start,$end,$providerTZ,$GcclientID,$GcclientSecret,$GcEDvalue,$ct_gc_admin_url,$ct_gc_api_key){
	$clientP = new Google_Client();
	$clientP->setApplicationName("Cleanto Google Calender");
	$clientP->setClientId($GcclientID);
	$clientP->setClientSecret($GcclientSecret);
	$clientP->setRedirectUri($ct_gc_admin_url);
	$clientP->setDeveloperKey($ct_gc_api_key);
	$clientP->setScopes('https://www.googleapis.com/auth/calendar');
	$clientP->setAccessType('offline');
	$calP = new Google_CalendarService($clientP);	
	
	$clientP->setAccessToken($provider_access_token);
	$accesstoken = json_decode($provider_access_token);
	if($provider_access_token){
		if ($clientP->isAccessTokenExpired()) {
			$clientP->refreshToken($accesstoken->refresh_token);
		}
	}
	if ($clientP->getAccessToken()){
		$eventP = new Google_Event();						
		$startTP = new Google_EventDateTime();
		$endTP = new Google_EventDateTime();
		
		$eventd = $calP->events->get($calendarId,$eventid);
		$location = '';
		$summary = "";
		if(isset($eventd['summary'])){
			$summary = $eventd['summary'];
		}
		$colorId = "9";
		if(isset($eventd['colorId'])){
			$colorId = $eventd['colorId'];
		}
		$description = "";
		if(isset($eventd['description'])){
			$description = $eventd['description'];
		}
		$startTP->setTimeZone($providerTZ);
		$startTP->setDateTime($date."T".$start);
		$endTP->setTimeZone($providerTZ);
		$endTP->setDateTime($date."T".$end);
		$eventP->setStart($startTP);
		$eventP->setEnd($endTP);
		$eventP->setSummary($summary);
		$eventP->setColorId($colorId);
		$eventP->setLocation($location);
		$eventP->setDescription($description);	
		
		$updatedEvent = $calP->events->update($calendarId,$eventid,$eventP);
		if(isset($updatedEvent)){
			return $updatedEvent;
		}else{
			return '';
		}
	}	
}
}
if(!function_exists("google_cal_TwoSync_ajax")){
function google_cal_TwoSync_ajax(){
	global $setting, $settings, $settings, $start_date, $staff_id, $providerCalenderBooking, $currDateTime_withTZ;
	
	if($setting != ""){
		$adminTwoSync = $setting->get_option('ct_gc_status_sync_configure');
	}
	else{
		$adminTwoSync = $settings->get_option('ct_gc_status_sync_configure');
	}
	if($staff_id != '' && $staff_id != '1'){
		$providerTwoSync = $setting->get_staff_option('gc_status_sync_configure',$staff_id);
	}else{
		$providerTwoSync = 'N';
	}
	if($adminTwoSync=='Y'){
		$curlevents = curl_init();
		curl_setopt_array($curlevents, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/event.php?cdate='.$start_date,
			CURLOPT_FRESH_CONNECT =>true,
			CURLOPT_USERAGENT => 'Cleanto'
		));
		$response = curl_exec($curlevents);				
		curl_close($curlevents);
		
		$provider_events = array();
		if(isset($response)){
			$provider_events = json_decode($response);
		}
		if(isset($provider_events,$adminTwoSync)){
			foreach($provider_events as $providerevent){
				$start_arr = explode('T',$providerevent->start);
				$sdate = $start_arr[0];
				$stime = substr($start_arr[1], 0, 8);
				$end_arr = explode('T',$providerevent->end);
				$edate = $end_arr[0];
				$etime = substr($end_arr[1], 0, 8);
				$GCslotstart = strtotime($sdate." ".$stime,$currDateTime_withTZ);
				$GCslotend = strtotime($edate." ".$etime,$currDateTime_withTZ);
				$providerCalenderBooking[] = array('start'=>$GCslotstart,'end'=>$GCslotend);
			}
		}
	}
	if($providerTwoSync=='Y'){
		$curlevents = curl_init();
		curl_setopt_array($curlevents, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/staff_event.php?cdate='.$start_date.'&pid='.$staff_id,
			CURLOPT_FRESH_CONNECT =>true,
			CURLOPT_USERAGENT => 'Cleanto'
		));
		$response = curl_exec($curlevents);				
		curl_close($curlevents);
		
		$provider_events = array();
		if(isset($response)){
			$provider_events = json_decode($response);
		}
		
		if(isset($provider_events,$providerTwoSync)){
			foreach($provider_events as $providerevent){
				$start_arr = explode('T',$providerevent->start);
				$sdate = $start_arr[0];
				$stime = substr($start_arr[1], 0, 8);
				$end_arr = explode('T',$providerevent->end);
				$edate = $end_arr[0];
				$etime = substr($end_arr[1], 0, 8);
				$GCslotstart = strtotime($sdate." ".$stime,$currDateTime_withTZ);
				$GCslotend = strtotime($edate." ".$etime,$currDateTime_withTZ);
				$providerCalenderBooking[] = array('start'=>$GCslotstart,'end'=>$GCslotend);
			}
		}
	}
	return $providerCalenderBooking;
}
}
if(!function_exists("google_cal_TwoSync_admin_ajax")){
function google_cal_TwoSync_admin_ajax(){
	global $setting, $settings, $CalenderBooking, $start_date, $end_date, $currDateTime_withTZ;
	if($setting != ""){
		$adminTwoSync = $setting->get_option('ct_gc_status_sync_configure');
	}
	else{
		$adminTwoSync = $settings->get_option('ct_gc_status_sync_configure');
	}
	if($adminTwoSync=='Y'){
		$curlevents = curl_init();
		curl_setopt_array($curlevents, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/calendar_events.php?sdate='.$start_date.'&edate='.$end_date,
			CURLOPT_FRESH_CONNECT =>true,
			CURLOPT_USERAGENT => 'Cleanto'
		));
		$response = curl_exec($curlevents);				
		curl_close($curlevents);
		
		$provider_events = array();
		if(isset($response)){
			$provider_events = json_decode($response);
		}
		if(isset($provider_events,$adminTwoSync)){
			foreach($provider_events as $providerevent){
				$start_arr = explode('T',$providerevent->start);
				$sdate = $start_arr[0];
				$stime = substr($start_arr[1], 0, 8);
				$end_arr = explode('T',$providerevent->end);
				$edate = $end_arr[0];
				$etime = substr($end_arr[1], 0, 8);
				$upd_arr = explode('T',$providerevent->update_date);
				$udate = $upd_arr[0];
				$utime = substr($upd_arr[1], 0, 8);
				$GCslotstart = strtotime($sdate." ".$stime,$currDateTime_withTZ);
				$GCslotend = strtotime($edate." ".$etime,$currDateTime_withTZ);
				$GCupdate = strtotime($udate." ".$utime,$currDateTime_withTZ);
				$new_array = array('id'=>$providerevent->id,'title'=>$providerevent->title,'color'=>$providerevent->color,'start'=>$GCslotstart,'end'=>$GCslotend,'timezone'=>$providerevent->timezone,'update_date'=>$GCupdate);
				$CalenderBooking[] = $new_array;
			}
		}
	}
	return $CalenderBooking;
}
}
if(!function_exists("google_cal_TwoSync_one_event_admin_ajax")){
function google_cal_TwoSync_one_event_admin_ajax(){
	global $setting, $settings, $CalenderBooking, $event_id, $currDateTime_withTZ;
	if($setting != ""){
		$adminTwoSync = $setting->get_option('ct_gc_status_sync_configure');
	}
	else{
		$adminTwoSync = $settings->get_option('ct_gc_status_sync_configure');
	}
	if($adminTwoSync=='Y'){
		$curlevents = curl_init();
		curl_setopt_array($curlevents, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => SITE_URL.'extension/GoogleCalendar/one_event_detail.php?e_id='.$event_id,
			CURLOPT_FRESH_CONNECT =>true,
			CURLOPT_USERAGENT => 'Cleanto'
		));
		$response = curl_exec($curlevents);
		curl_close($curlevents);
		
		$provider_events = array();
		if(isset($response)){
			$provider_events = json_decode($response);
		}
		if(isset($provider_events,$adminTwoSync)){
			$start_arr = explode('T',$provider_events->start);
			$sdate = $start_arr[0];
			$stime = substr($start_arr[1], 0, 8);
			$end_arr = explode('T',$provider_events->end);
			$edate = $end_arr[0];
			$etime = substr($end_arr[1], 0, 8);
			$crt_arr = explode('T',$provider_events->created_date);
			$cdate = $crt_arr[0];
			$ctime = substr($crt_arr[1], 0, 8);
			$upd_arr = explode('T',$provider_events->update_date);
			$udate = $upd_arr[0];
			$utime = substr($upd_arr[1], 0, 8);
			$GCslotstart = strtotime($sdate." ".$stime,$currDateTime_withTZ);
			$GCslotend = strtotime($edate." ".$etime,$currDateTime_withTZ);
			$GCcreate = strtotime($cdate." ".$ctime,$currDateTime_withTZ);
			$GCupdate = strtotime($udate." ".$utime,$currDateTime_withTZ);
			$CalenderBooking = array('id'=>$provider_events->id,'title'=>$provider_events->title,'description'=>$provider_events->description,'color'=>$provider_events->color,'start'=>$GCslotstart,'end'=>$GCslotend,'created_date'=>$GCcreate,'update_date'=>$GCupdate);
		}
	}
	return $CalenderBooking;
}
}