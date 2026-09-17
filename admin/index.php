<?php 

include_once(dirname(dirname(__FILE__)).'/header.php');
$filename = '../config.php';
$file = file_exists($filename);
if($file){	
	if(filesize($filename) > 0){	}	
		else{		
			header('Location:'.SITE_URL.'ct_install.php');	
		}
	}
	include(dirname(dirname(__FILE__)).'/objects/class_connection.php');
	include(dirname(dirname(__FILE__))."/objects/class_setting.php");
	include(dirname(dirname(__FILE__))."/objects/class_version_update.php");
	include(dirname(dirname(__FILE__)) . "/objects/class_staff_commision.php");
	session_start();
	$con = new cleanto_db();$conn = $con->connect();
	if(isset($_SESSION['ct_adminid']))
		{	
			header('Location:'.SITE_URL."admin/calendar.php");
		}
	elseif(isset($_SESSION['ct_staffid']))
		{	
			header('Location:'.SITE_URL."staff/staff-dashboard.php");
		}
	elseif(isset($_SESSION['ct_login_user_id']))
		{    
			header('Location:'.SITE_URL."admin/user-profile.php");
		}

	$query = "select * from ct_admin_info";
	$info =  $conn->query($query);
	if(@mysqli_num_rows($info) == 0)
		{    
			header("Location:../");
		}
	$staff_commision = new cleanto_staff_commision();
	$staff_commision->conn=$conn;
	$settings = new cleanto_setting();
	$settings->conn = $conn;
	$objcheckversion = new cleanto_version_update();
	$objcheckversion->conn = $conn;
	$current = $settings->get_option('ct_version');
	if($current == "")
		{  
			$objcheckversion->insert_option("ct_version","1.1");
		}
	if($current < 1.1)
		{	
			$settings->set_option("ct_version","1.1");	
			$objcheckversion->update1_1();
		}
	if($current < 1.2)
		{	
			$settings->set_option("ct_version","1.2");	
			$objcheckversion->update1_2();
		}
	if($current < 1.3)
		{	
			$settings->set_option("ct_version","1.3");	
			$objcheckversion->update1_3();
		}
	if($current < 1.4)
		{	
			$settings->set_option("ct_version","1.4");	
			$objcheckversion->update1_4();
		}
	if($current < 1.5)
		{	
			$settings->set_option("ct_version","1.5");	
			$objcheckversion->update1_5();
		}
	if($current < 1.6)
		{	
			$settings->set_option("ct_version","1.6");	
			$objcheckversion->update1_6();
		}
	if($current < 2.0)
		{	
			$settings->set_option("ct_version","2.0");	
			$objcheckversion->update2_0();
		}
	if($current < 2.1)
		{  
			$settings->set_option("ct_version","2.1");
		}
	if($current < 2.2)
		{  
			$settings->set_option("ct_version","2.2");	
			$objcheckversion->update2_2();
		}
	if($current < 2.3)
		{  
			$settings->set_option("ct_version","2.3");	
			$objcheckversion->update2_3();
		}
	if($current < 2.4)
		{  
			$settings->set_option("ct_version","2.4");	
			$objcheckversion->update2_4();
		}
	if($current < 2.5)
		{  
			$settings->set_option("ct_version","2.5");	
			$objcheckversion->update2_5();
		}
	if($current < 2.6)
		{  
			$settings->set_option("ct_version","2.6");	
			$objcheckversion->update2_6();
		}
	if($current < 2.7)
		{  
			$settings->set_option("ct_version","2.7");	
			$objcheckversion->update2_7();
		}
	if($current < 2.8)
		{  
			$settings->set_option("ct_version","2.8");	
			$objcheckversion->update2_8();
		}
	if($current < 3.0)
		{  
			$settings->set_option("ct_version","3.0");	
			$objcheckversion->update3_0();
		}
	if($current < 3.1)
		{  
			$settings->set_option("ct_version","3.1");
		}
	if($current < 3.2)
		{  
			$settings->set_option("ct_version","3.2");	
			$objcheckversion->update3_2();
		}
	if($current < 3.3)
		{  
			$settings->set_option("ct_version","3.3");	
			$objcheckversion->update3_3();
	}
	if($current < 4.0)
		{  
			$settings->set_option("ct_version","4.0");	
			$objcheckversion->update4_0();
		}
	if($current < 4.1)
		{  
			$settings->set_option("ct_version","4.1");	
			$objcheckversion->update4_1();
		}
	if($current < 4.2)
		{  
			$settings->set_option("ct_version","4.2");	
			$objcheckversion->update4_2();
		}
	if($current < 4.3)
		{  
			$settings->set_option("ct_version","4.3");	
			$objcheckversion->update4_3();
		}
	if($current < 4.4)
		{  
			$settings->set_option("ct_version","4.4");	
			$objcheckversion->update4_4();
	}
	if($current < 5.0)
		{  
			$settings->set_option("ct_version","5.0");	
			$objcheckversion->update5_0();
		}
	if($current < 5.1)
		{  
			$settings->set_option("ct_version","5.1");
		}
	if($current < 5.2)
		{  
			$settings->set_option("ct_version","5.2");	
			$objcheckversion->update5_2();
		}
	if($current < 5.3)
		{  
			$settings->set_option("ct_version","5.3");	
			$objcheckversion->update5_3();
		}
	if($current < 6.0)
		{  
			$settings->set_option("ct_version","6.0");	
			$objcheckversion->update6_0();
		}
	if($current < 6.1)
		{  
			$settings->set_option("ct_version","6.1");
		}
	if($current < 6.2)
		{  
			$settings->set_option("ct_version","6.2");	
			$objcheckversion->update6_2();
		}
	if($current < 6.3)
		{  
			$settings->set_option("ct_version","6.3");	
			$objcheckversion->update6_3();
		}
	if($current < 6.4)
		{  
			$settings->set_option("ct_version","6.4");	
			$objcheckversion->update6_4();
		}
	if($current < 6.5)
		{  
			$settings->set_option("ct_version","6.5");	
			$objcheckversion->update6_5();
		}
	if($current < 7.0)
		{  
			$settings->set_option("ct_version","7.0");	
			$objcheckversion->update7_0();
		}
	if($current < 7.1)
		{  
			$settings->set_option("ct_version","7.1");
		}
    if($current < 7.2)
    	{  
			$settings->set_option("ct_version","7.2");
		} 
	if($current < 7.3)
	{
		  	$settings->set_option("ct_version","7.3");
			$objcheckversion->update7_3();
	}if($current < 7.4)
	{
		  	$settings->set_option("ct_version","7.4");
			$objcheckversion->update7_4();
	}
	if($current < 7.5)
	{
		  	$settings->set_option("ct_version","7.5");
			$objcheckversion->update7_5();
	}
	if($current < 7.6)
	{
		  	$settings->set_option("ct_version","7.6");
			$objcheckversion->update7_6();
	}
	if($current < 7.7)
	{
		  	$settings->set_option("ct_version","7.7");
			$objcheckversion->update7_7();
	}
	if($current < 7.8){
			$settings->set_option("ct_version","7.8");
			$objcheckversion->update7_7();
	}
	if($current < 7.8){
			$settings->set_option("ct_version","7.8");
   }
	 if($current < 7.9){
			$settings->set_option("ct_version","7.9");
   }
	 if($current < 8.0){
			$settings->set_option("ct_version","8.0");
   }
   if($current < 8.1){
      $settings->set_option("ct_version","8.1");
   }
   if ($current < 8.2) {
      $setting->set_option("ct_version", "8.2");
   }
	if ($current < 8.3) {
		$setting->set_option("ct_version", "8.3");
	}
	if ($current < 8.4) {
		$setting->set_option("ct_version", "8.4");
 	}
	if($current < 8.5){
  $settings->set_option("ct_version","8.5");
  $objcheckversion->update8_5();
}
$lang = $settings->get_option("ct_language");
$label_language_values = array();
$language_label_arr = $settings->get_all_labelsbyid($lang);
if ($language_label_arr[1] != "" || $language_label_arr[3] != "" || $language_label_arr[4] != "" || $language_label_arr[5] != "" || $language_label_arr[6] != "")
	{	
		$default_language_arr = $settings->get_all_labelsbyid("en");	
		if($language_label_arr[1] != '')
			{		
				$label_decode_front = base64_decode($language_label_arr[1]);	
			}else
			{		
				$label_decode_front = base64_decode($default_language_arr[1]);	
			}	
			if($language_label_arr[3] != '')
				{		
					$label_decode_admin = base64_decode($language_label_arr[3]);	
				}
				else{		
					$label_decode_admin = base64_decode($default_language_arr[3]);	
				}	
				if($language_label_arr[4] != '')
					{		
						$label_decode_error = base64_decode($language_label_arr[4]);	
					}else{		
						$label_decode_error = base64_decode($default_language_arr[4]);	
					}	
					if($language_label_arr[5] != '')
						{		
							$label_decode_extra = base64_decode($language_label_arr[5]);	
						}else{		
							$label_decode_extra = base64_decode($default_language_arr[5]);	
						}	
						if($language_label_arr[6] != '')
							{		
								$label_decode_front_form_errors = base64_decode($language_label_arr[6]);	
							}else{		
								$label_decode_front_form_errors = base64_decode($default_language_arr[6]);	
							}		
							$label_decode_front_unserial = unserialize($label_decode_front);	
							$label_decode_admin_unserial = unserialize($label_decode_admin);	
							$label_decode_error_unserial = unserialize($label_decode_error);	
							$label_decode_extra_unserial = unserialize($label_decode_extra);	
							$label_decode_front_form_errors_unserial = unserialize($label_decode_front_form_errors);    

							$label_language_arr = array_merge($label_decode_front_unserial,
							$label_decode_admin_unserial,$label_decode_error_unserial,
							$label_decode_extra_unserial,$label_decode_front_form_errors_unserial);	
							foreach($label_language_arr as $key => $value){		
								$label_language_values[$key] = urldecode($value);	
							}
						    }
						    else{    
						    	$default_language_arr = $settings->get_all_labelsbyid("en");
						    	$label_decode_front = base64_decode($default_language_arr[1]);	
						    	$label_decode_admin = base64_decode($default_language_arr[3]);	
						    	$label_decode_error = base64_decode($default_language_arr[4]);	
						    	$label_decode_extra = base64_decode($default_language_arr[5]);	
						    	$label_decode_front_form_errors = base64_decode($default_language_arr[6]);		
						    	$label_decode_front_unserial = unserialize($label_decode_front);	
						    	$label_decode_admin_unserial = unserialize($label_decode_admin);	
						    	$label_decode_error_unserial = unserialize($label_decode_error);	
						    	$label_decode_extra_unserial = unserialize($label_decode_extra);	
						    	$label_decode_front_form_errors_unserial = unserialize($label_decode_front_form_errors);    	
						    	$label_language_arr = array_merge($label_decode_front_unserial,
						    		$label_decode_admin_unserial,$label_decode_error_unserial,
						    		$label_decode_extra_unserial,
						    		$label_decode_front_form_errors_unserial);	

						    		foreach($label_language_arr as $key => $value)
						    			{		
						    				$label_language_values[$key] = urldecode($value);	
						    			}
						    		}

				$loginimage=$settings->get_option('ct_login_image');
					if($loginimage!='')
					{	
						$imagepath=SITE_URL."assets/images/backgrounds/".$loginimage;
					}else
					{	
						$imagepath=SITE_URL."assets/images/login-bg.png";
					}?>

	<!DOCTYPE html>
		<html lang="en">
			<head>    
				<meta charset="UTF-8" />    
				<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">    
				<meta name="viewport" content="width=device-width, initial-scale=1.0">    
				<title><?php echo $settings->get_option("ct_page_title"); ?> | Login</title>	
				<link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/backgrounds/<?php echo $settings->get_option('ct_favicon_image');?>"/>    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/login-style.css" />    
				<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/bootstrap/bootstrap.min.css" />    
				<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/bootstrap/bootstrap-theme.min.css" />    

				<!-- **Google - Fonts** -->    
				<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800&display=swap" rel="stylesheet">    

				<!--    <link rel="stylesheet" type="text/css" href="-->

				<?php 
				/* echo BASE_URL; */
				 ?>
				 <!--/assets/css/font-awesome.css" />-->    
				 <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/font-awesome/css/font-awesome.css" />    
				 <script type="text/javascript" src="<?php echo BASE_URL; ?>/assets/js/jquery-2.1.4.min.js"></script>   
				 <script src="<?php echo BASE_URL; ?>/assets/js/jquery.validate.min.js"></script>    
				 <script type="text/javascript" src="<?php echo BASE_URL; ?>/assets/js/bootstrap.min.js"></script>    
				 <script type="text/javascript" src="<?php echo BASE_URL; ?>/assets/js/login.js"></script>    
				 <script type="text/javascript">        
				 	var ajax_url = '<?php echo AJAX_URL;?>';        
				 	var base_url = '<?php echo BASE_URL;?>';   
				  </script>		
				  <style>	
				  #ct-login .bg-image .main-side-login{
				  		font-family: 'Open Sans', sans-serif;		
				  		background: url(<?php echo $imagepath;?>);		
				  		/* background-image: url("<?php echo $imagepath;?>");		
				  		font-weight: 300;	
				  	    font-size: 15px;		
				  	    color: #333;		
				  	    -webkit-font-smoothing: antialiased;	 */
    /* background-position: center; */
    background-repeat: no-repeat;
    background-size: cover;
    /* display: grid; */
    /* place-content: center;*/
				  		}	
				  	</style>
				  </head> 
				  <?php      include(dirname(__FILE__)."/language_js_objects.php");   ?>
<body>

<div id="ct-login">
	<div class="ct-loading-main" style="display: none;">

      <div class="loader">Loading...</div>

    </div>
         <section class="">
			
								<div class="bg-image">
										<div class="col-md-7 custom-padding-wrap">
											<div class="main-side-login">
											
											<?php
/*
											<?php 
													if ($settings->get_option('ct_company_logo') == '') {
													$images="../assets/images/cleanto-logo-admin.png";
													}else{
														$images="../assets/images/services/".$settings->get_option('ct_company_logo');	
													}
												?> 
												<img src="<?php echo $images; ?>">
												*/
?>
											</div>
										</div>

							    </div>
            <div class="col-md-5">
							<div class="main-side-login responsive-bg">
							
								 <div class="login-form-container" id="login-form">
								             
											 <?php 
													if ($settings->get_option('ct_company_logo') == '') {
													$images="../assets/images/cleanto-logo.png";
													}else{
														$images="../assets/images/services/".$settings->get_option('ct_company_logo');	
													}
												?> 
												
												<img class="top-logo-wrap" src="<?php echo $images; ?>">
										
												<form id="" name="" method="POST">
											<div class="top-heading-common">
												
												<h3><?php echo $label_language_values['log_in'];?> </h3>
											</div>
											<div class="form-group fl">
													<i class="fa fa-user" aria-hidden="true"></i>
												<input class="mt-7 form-control effect-2" type="email" id="userEmail" name="txtname" placeholder="Username" onkeydown="if (event.keyCode == 13) document.getElementById('mybtnlog').click()">  
												<span class="focus-border"></span>
											</div>
											<div class="form-group fl">
												<i class="fa fa-lock" aria-hidden="true"></i>
												<input class="mt-7 form-control showpassword effect-2" type="password" id="userPassword" name="txtname"  placeholder="Password" onkeydown="if (event.keyCode == 13) document.getElementById('mybtnlog').click()">		
												<span class="focus-border"></span>												
											<div class="ct-show-pass">
                   							 <input id="show-pass" class="ct-checkbox" name="" value="" type="checkbox">
                    						<label for="show-pass"><span class="ct-pass-check show-pass-text"></span></label>
               								 </div>							
											</div>
											 <div class="login-error" style="color:red; font-size:15px;">
               								 <label><?php echo "Please enter correct username and password";?></label>
											 </div> 
											<div class="username-blank-error" style="color:red;display:none;">
               								  <label><?php echo "Please Enter Username";?></label>
											</div>
											<div class="password-blank-error" style="color:red;display:none;">
               								  <label><?php echo "Please Enter Password";?></label>
											</div>											   
											<div class="ct-custom-checkbox">
											<div class="ct-checkbox-list">
																																								
												<input type="checkbox" id="remember_me" class="ct-checkbox" name="remember_me" <?php   if(isset($_COOKIE['cleanto_remember'])){ echo "checked";}else{ echo "";}?> /> 
												<label for="remember_me"><span></span><?php echo $label_language_values['remember_me'];?></label>                                       
											</div>
									</div>
											<div class="clearfix">
											<a id="mybtnlog" class="btn ct-login-btn btn-lg col-xs-12 mybtnloginadmin" href="javascript:void(0)"><?php echo $label_language_values['login'];?></a> 
											</div>
											<div class="bottom-forgot-pass"> 
												<div class="clearfix">  
												<a class="btn btn-link col-xs-12" id="ct_forget_password" href="javascript:void(0)"><?php echo $label_language_values['forget_password'];?></a>  
												</div>
												<?php 
												if($settings->get_option('ct_staff_registration') == 'Y'){
												?>
												<div class="clearfix">                                 
												 <a type="button" class="btn btn-link col-xs-12" id="register_as_staff_btn">Register As Doctor </a>                   
												 </div>
												<?php } ?>
										</div>
										</form>
								</div> 
							<div class="forget-form-container" id="forgot-pas-form">
									<form id="forget_pass" name="" method="POST">
											<div class="top-heading-common">
												<h3> Reset Password </h3>
												<p>  Enter your email and we will send you instructions on resetting your password. </p>
											</div>
											<div class="form-group fl">
												<i class="fa fa-envelope" aria-hidden="true"></i>
												<input class="mt-7 form-control effect-2" type="email" id="rp_user_email" name="rp_user_email" placeholder="Registered Email"> 
												<span class="focus-border"></span>                    
											</div>
											  <label class="forget_pass_correct"></label>                         
												<label class="forget_pass_incorrect"></label> 
											<div class="forgotpassword-error" style="color:red;">
                                        		<label><?php echo $label_language_values['sorry_wrong_email_or_password'];?></label>                        
                                    		</div>
											<div class="clearfix">
											<a id="reset_pass" class="btn ct-login-btn btn-lg col-xs-12 mt-0" href="javascript:void(0)">Send Mail </a> 
											</div>
												<div class="clearfix">  
												<a class="btn ct-login-btn btn-lg col-xs-12 mt-0" id="forgot_to_login_form" href="">Log In</a>  
												</div>
												
										</form>
								</div> 
								 <div class="staff-form-container" id="staff-form">
									<form id="staff_registration" name="staff_registration" method="POST">
										<p class="register-meesg" id="register-meesg" style="display:none"> Thanks for registering with <?php echo $settings->get_option('ct_company_name'); ?>. Our administrators are currently reviewing your profile. Your staff account will be activated as soon as verification is completed. </p>
											<div class="top-heading-common">
												<h3> Register As Doctor </h3>
											</div>
											<div class="form-group fl">
												<i class="fa fa-user" aria-hidden="true"></i>
												<input class="mt-7 form-control effect-2" type="text" id="staff_name" name="staff_name" placeholder="Name">  
												<span class="focus-border"></span>
											</div>
											<div class="form-group fl">
													<i class="fa fa-envelope" aria-hidden="true"></i>
												<input class="mt-7 form-control effect-2" type="email" id="staff_email" name="staff_email" placeholder="Email">  
												<span class="focus-border"></span>
											</div>
											<div class="form-group fl">
													<i class="fa fa-lock" aria-hidden="true"></i>
												<input class="mt-7 form-control effect-2" type="password" id="staff_pass" name="staff_pass" placeholder="Password" class="showpass"  onkeydown="if (event.keyCode == 13) document.getElementById('ct_staff_register').click()">  
												<span class="focus-border"></span>
											</div>
												<div class="clearfix">  
												<a class="btn ct-login-btn btn-lg col-xs-12 mt-0" id="ct_staff_register" href="">Register Yourself</a><div class="clearfix">  
												<a class="btn ct-login-btn btn-lg col-xs-12 mt-0" id="forgot_to_login_form" href="">Log In</a>  
												</div>  
												</div>
												
										</form>
								</div> 
							</div>
						</div>
						
         </section>
      </div>
			
			<script>
				jQuery(document).ready(function() {
					jQuery("#forgot-pas-form").hide();
					jQuery("#staff-form").hide();
					jQuery(document).on("click", "#ct_forget_password", function() {
						jQuery('#login-form').hide();
						jQuery("#forgot-pas-form").show();
					});
					jQuery(document).on("click", "#forgot_to_login_form", function() {
						jQuery("#forgot-pas-form").hide();
						jQuery("#staff-form").hide();
						jQuery('#login-form').show();
					});
					jQuery(document).on("click", "#register_as_staff_btn", function() {
						jQuery("#login-form").hide();
						jQuery('#staff-form').show();
					});
				});
			</script>
			</body></html>