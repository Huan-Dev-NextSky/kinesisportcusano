<?php  

include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/admin_session_check.php');
include(dirname(dirname(__FILE__))  . "/objects/class_userdetails.php");

if(!isset($_SESSION['ct_login_user_id'])){
  header('Location:'.SITE_URL."admin/");
}
$con = new cleanto_db();
$conn = $con->connect();
$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;

$setting = new cleanto_setting();
$setting->conn = $conn;
?>

<div id="cta-user-appointments">
  <div class="panel-body">
    <div class="tab-content">
      <h4 class="header4">My referral coupon</h4>
        <form>
          <div class="table-responsive">
            <table id="user-profile-booking-table" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
              <thead>
                <tr>
									<th>Friend Name</th>
									<th>Coupon</th>
									<th>Discount</th>
									<th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $objuserdetails->id = $_SESSION['ct_login_user_id'];
                  $details = $objuserdetails->get_user_referral_code();
                  if($details->num_rows > 0){
                  while($dd = mysqli_fetch_array($details)){
                ?>
                  <tr>
                    <td><?php if(isset($rs['first_name'])){ echo $rs['first_name']; }else{ } ?></td>
                    <td><?php if(isset($rs['referral_coupon'])){ echo $rs['referral_coupon']; }else{ } ?></td>
                    <td><?php if($setting->get_option('ct_special_type') == "P"){ echo $setting->get_option('ct_special_offer_text')."%"; } else { echo $setting->get_option('ct_special_offer_text')."/-"; } ?></td>
                    <td><?php     if($dd['coupon_used'] == "0"){ echo "Pending"; } else { echo "Expire"; } ?></td>
                  </tr>
                <?php 
                }
              }
                ?>
              </tbody>
            </table>
          </div>   
    </div>
        </form>
  </div>
</div>
<?php 
if($gc_hook->gc_purchase_status() == 'exist'){
	if($setting->get_option('ct_gc_status_configure') == 'Y' && $setting->get_option('ct_gc_status') == 'Y') {
		?>
		<input type="hidden" id="extension_js" value="true" />
		<?php 
	} else {
		?>
		<input type="hidden" id="extension_js" value="false" />
        <?php 
	}
}
include(dirname(__FILE__).'/footer.php');
?>		
	