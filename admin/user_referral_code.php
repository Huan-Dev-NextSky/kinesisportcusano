<?php 
include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/admin_session_check.php');
include(dirname(dirname(__FILE__)) . "/objects/class_userdetails.php");
/* include(dirname(dirname(__FILE__)) . "/objects/class_setting.php"); */
$con = new cleanto_db();
$conn = $con->connect();
$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;

/* $settings = new cleanto_setting();
$settings->conn = $conn; */
?>

<div id="cta-user-profile">
  <div class="panel-body">
    <div class="tab-content">
      <div class="col-lg-3 col-md-2 col-sm-2 col-xs-12">
      <?php 
        $objuserdetails->id = $_SESSION['ct_login_user_id'];
        $userinfo = $objuserdetails->readone();
        $user_refer_code = $userinfo[18];
      ?>
      </div>
      <div class="col-md-8 col-xs-12 col-lg-9 np">
        <div class="form-group col-md-6 col-sm-6 col-xs-12">
          <label for="firstname">Referral code</label>
          <input class="form-control" name="user_referral_code" id="user_referral_code" value="<?php  echo $userinfo[18];?>" type="text">
        </div>                    
      </div>
      <div class="row ct-account-settings-sub-item-parent ct-push-down">
        <div class="col-md-12 col-sm-12 col-xs-12 social-share-box">   
          <div class="social-share-holder col-lg-8 col-lg-offset-2">
            <div class="social-share-btn-holder row">
              <div class="button-holder col-xs-12 col-sm-6 np">
                <a class="btn gmail-btn noloader mail-share-handlers" data-share-type="gmail" target="_blank" href="//mail.google.com/mail/?view=cm&amp;fs=1&amp;tf=1&amp;to=&amp;su=<?php       echo $general->ct_price_format($setting->get_option('ct_refs_value'),$symbol_position,$decimal); ?> OFF your first booking with Emooby Services&amp;body=Hey%2C%0A%0ACheck%20out%20Emooby%20-%20We%20Clean%20For%20You%20-%20is%20where%20you%20can%20book%20your%20domestic%20cleaning%20services%21%0A%0AIf%20you%20sign%20up%20for%20a%20Emooby%20with%20my%20link%2C%20you%27ll%20get%20%C2%A315%20OFF%20your%20first%20service.%0A%0AGive%20it%20a%20try%21%20Just%20click%20the%20link%20or%20paste%20the%20address%20in%20your%20browser.%0A%0A%20https%3A%2F%2F<?php     echo SITE_URL."?refer_code=".$user_refer_code; ?>"><span class="icon gmail-ico"></span><span class="btn-span"> Share via G-mail</span></a>
              </div>
              
              <div class="button-holder col-xs-12 col-sm-6 np">
                <a class="btn email-btn noloader mail-share-handlers" data-share-type="email" target="_blank" href="mailto:?subject=<?php       echo $general->ct_price_format($setting->get_option('ct_refs_value'),$symbol_position,$decimal); ?> OFF your first booking with Emooby Services&amp;body=Hey%2C%0A%0ACheck%20out%20Emooby%20-%20We%20Clean%20For%20You%20-%20is%20where%20you%20can%20book%20your%20domestic%20cleaning%20services%21%0A%0AIf%20you%20sign%20up%20for%20a%20Emooby%20with%20my%20link%2C%20you%27ll%20get%20%C2%A315%20OFF%20your%20first%20service.%0A%0AGive%20it%20a%20try%21%20Just%20click%20the%20link%20or%20paste%20the%20address%20in%20your%20browser.%0A%0A%20https%3A%2F%2F<?php     echo SITE_URL."?refer_code=".$user_refer_code; ?>"><span class="icon email-ico"></span><span class="btn-span">Share via E-mail</span></a>
              </div>
              <div class="button-holder col-xs-12 col-sm-4 np">
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?php     echo SITE_URL."?refer_code=".$user_refer_code; ?>" class="btn  fb-btn  noloader fb-xfbml-parse-ignore"><span class="icon facebook-ico"></span><span class="btn-span"> Facebook</span></a>
              </div>
              
              <div class="button-holder col-xs-12 col-sm-4 np">
              <a class="btn  tw-btn  noloader" href="https://twitter.com/intent/tweet?text=<?php     echo SITE_URL."?refer_code=".$user_refer_code; ?>"><span class="icon twitter-ico"></span><span class="btn-span"> Twitter</span></a>
              </div>
              
              <div class="button-holder col-xs-12 col-sm-4 np">
              <a class="btn  whatsapp-btn  noloader" href="https://wa.me/?text=<?php     echo SITE_URL."?refer_code=".$user_refer_code; ?>" data-action="share/whatsapp/share"><span class="icon whatsapp-ico"></span><span class="btn-span"> WhatsApp</span></a>
              </div>
            </div>
              <div class="col-xs-12 share-after-effect"></div>  
			</div>
			
			<div class="main-title-share-link col-sm-12 col-lg-8 col-lg-offset-3 mt-15 mb-15">
				<p class="account-settings-sub-title share-paragraph">Share your link:</p>
				<?php $design_page=$setting->get_option('ct_booking_page_design');

							if($design_page=='S'){

							 $user_refer_code = SITE_URL."index_one_step.php?refer_code=".$user_refer_code;
							}else{
								$user_refer_code = SITE_URL."?refer_code=".$user_refer_code;
							} ?>
				<div class="copy-share-link">
					<input type="text" class="form-control" id="usr" readonly value="<?php     echo $user_refer_code; ?>">
					<button class="btn btn-success copy_refer_earn">Copy</button>
				</div>
			</div>
			
			<div class="col-xs-12 col-lg-12 col-md-12 col-sm-12 text-right">
				<span class="copied_text">Text Copied Successfully</span>
			</div> 
			
			<div class="col-md-12 col-sm-12 col-xs-12 col-lg-9 col-lg-offset-3 social-share-box so-icon-box">
				<div class="social-share-icon-holder wa-flex wa-items-wrap">
                                                                                                 
                    <div class="social-share-btn-item">
                        <a class="noloader mail-share-handlers text-center" data-share-type="gmail" href="#">
                            <div class="mb-10">
								<img width="60px" src="../../cleanto/assets/images/ICONS-01.svg" alt="">
                            </div>
                            <div class="social-share-btn color-reset">G-mail</div>
                        </a>
                    </div><!--  .col-md-6 -->
                                                
                    <div class="social-share-btn-item">
                        <a class="noloader mail-share-handlers text-center" data-share-type="email" href="#">
                            <div class="mb-10">
                                <img width="60px" src="../../cleanto/assets/images/ICONS-02.svg" alt="">
                            </div>
							<div class="social-share-btn color-reset">E-mail</div>
                        </a>
                    </div> <!--  .col-md-6 -->
					
                    <div class="social-share-btn-item ">
                        <a href="#" data-share-cta="facebook" data-wa-share-link="https://accounts.fantasticservices.com/referral/joaof0e" class="noloader text-center">
                            <div class="mb-10">
                                <img width="60px" src="../../cleanto/assets/images/ICONS-04.svg" alt="">
                            </div>
                            <div class="social-share-btn color-reset">Facebook</div>
                        </a>
                    </div>
					
                  	<div class="social-share-btn-item hidden" data-mobile-only="true">
						<a href="#" class="noloader text-center">
                            <div class="mb-10">
                                <img width="60px" src="../../cleanto/assets/images/ICONS-06.svg" alt="">
                            </div>
							<div class="social-share-btn color-reset">Viber</div>
                        </a>
                    </div>
                                                
					<div class="social-share-btn-item" data-mobile-only="false">
                        <a target="_blank" href="#" class="noloader text-center">
                            <div class="mb-10">
                                <img width="60px" src="../../cleanto/assets/images/ICONS-03.svg" alt="">
                            </div>
                            <div class="social-share-btn color-reset">WhatsApp</div>
                        </a>
                    </div>
                    
					<div class="social-share-btn-item">
                        <a data-share-cta="twitter" href="#" data-wa-share-link="https://accounts.fantasticservices.com/referral/joaof0e" "="" class="noloader text-center">
                            <div class="mb-10">
                                <img width="60px" src="../../cleanto/assets/images/ICONS-08.svg" alt="">
                             </div>
							<div class="social-share-btn color-reset">Twitter</div>
                         </a>
                    </div>
                </div>
            </div>
			
            <div class="col-md-12 col-sm-12 col-xs-12 col-lg-7 col-lg-offset-2 account-settings-sub-item-holder">
                <p class="account-settings-title">How invites work?</p>
            </div>
			
			<div class="col-lg-8 col-lg-offset-2 col-md-12 col-sm-12 col-xs-12 push-down p-0">
				<div class="col-sm-4 push-s-down">
                    <div class="referral-description">
						<div class="push-down">
							<img src="../../cleanto/assets/images/ICONS-07.svg" alt="" class="wa-vertical-badged-image">
						</div>
						<h3 class="bold color-reset">Share your link</h3>
						<p class="color-reset push-up wa-vertical-badged-description">
							Invite friends to join Fantastic services using your unique link
						</p>
					</div>                                        
				</div>
                 
				<div class="col-sm-4">
                    <div class="referral-description">
						<div class="push-down">
							<img src="../../cleanto/assets/images/callendar.svg" alt="" class="wa-vertical-badged-image">
						</div>
						<h3 class="bold color-reset">Give £10</h3>
						<p class="color-reset push-up wa-vertical-badged-description">
							Friends who sign up via your link get £10 OFF their first booking
						</p>
					</div>                                        
				</div>
				
                <div class="col-sm-4">
                    <div class="referral-description">
						<div class="push-down">
							<img src="../../cleanto/assets/images/ukcash.svg" alt="" class="wa-vertical-badged-image">
						</div>
						<h3 class="bold color-reset">Give £10</h3>
						<p class="color-reset push-up wa-vertical-badged-description">
							As soon as your friends' first appointment is carried out, you also get £10 credited to your account
						</p>
					</div> 
				</div>
				
				<a href="#" target="_blank" id="find-out" class="how-ref-works-link noloader" data-step="1">Terms and conditions</a>
            </div>
			
        </div>          
      </div>

      <div class="col-lg-9 col-md-2 col-sm-2 col-xs-12">
      </div>
    </div>
  </div>
</div>
<?php 
include(dirname(__FILE__).'/footer.php');
?>