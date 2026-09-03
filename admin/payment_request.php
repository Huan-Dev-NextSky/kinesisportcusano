<?php  

include(dirname(__FILE__).'/header.php');
include(dirname(dirname(__FILE__)) ."/objects/class_payments.php");
include(dirname(dirname(__FILE__)) ."/objects/class_staff_commision.php");
include(dirname(__FILE__).'/user_session_check.php');
include(dirname(dirname(__FILE__)) ."/objects/class_adminprofile.php");
include(dirname(dirname(__FILE__)) ."/objects/class_frequently_discount.php");
include(dirname(dirname(__FILE__)) ."/objects/class_order_client_info.php");
include(dirname(dirname(__FILE__)) ."/objects/class_booking.php");

$con = new cleanto_db();
$conn = $con->connect();
$objpayment = new cleanto_payments();
$objpayment->conn = $conn;

$staffpayment=new cleanto_staff_commision();
$staffpayment->conn=$conn;

$admin_profile=new cleanto_adminprofile();
$admin_profile->conn=$conn;

$frequently_discount=new cleanto_frequently_discount();
$frequently_discount->conn=$conn;

$objocinfo=new cleanto_order_client_info();
$objocinfo->conn=$conn;

$objbooking=new cleanto_booking();
$objbooking->conn=$conn;

/* general setting object */
$general=new cleanto_general();
$general->conn=$conn;
$settings = new cleanto_setting();
$settings->conn = $conn;
$symbol_position=$settings->get_option('ct_currency_symbol_position');
$decimal=$settings->get_option('ct_price_format_decimal_places'); 
?>
<div id="cta-payments" class="panel tab-content">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo $label_language_values['payments_history_details'];?></h1>
        </div>
    <ul class="nav nav-tabs ct-segment-tabs">
      <li class="active"><a data-toggle="tab" href="#client-payments"><i class="fa fa-exchange"></i><?php echo "Transfer Requests";?></a></li>
    </ul>
        <div class="tab-content">
        <div id="client-payments" class="tab-pane fade in active">
    
        <div class="mb-5" id="hr"></div>
        <div class="mytabledisplaypayment">
        <table id="payments-details" class="display responsive nowrap table table-striped table-bordered" cellspacing="0" width="100%">
          <thead>
          <tr>
            <th><?php echo "Request Id";  ?></th>
            <th><?php echo "Staff Name";  ?></th>
            <th><?php echo "Staff Email"; ?></th>
            <th><?php echo "Request Amount";  ?></th>
            <th><?php echo "Request Status";  ?></th>
            
          </tr>
          </thead>
          <tbody>

          <?php 
          $r = $objpayment->getallrequest();
          if($r->num_rows > 0){
          while($rs = mysqli_fetch_array($r)){
            ?>
            <tr>
              <td><?php if(isset($rs['request_id'])){
                echo $rs['request_id'];
              }else{

              } ?></td>
              
              <?php 
              
              $admin_profile->id = $rs['staff_id'];
              $staff_detail = $admin_profile->get_previous_staff_wallet();

              ?>
              <td><?php echo $staff_detail[2];  ?></td>
              <td><?php if(isset($rs['email_id'])){ echo $rs['email_id']; }else{ } ?></td>
              <td><?php if(isset($rs['request_amount'])){ echo $rs['request_amount']; }else{ } ?></td>
              <td><?php if(isset($rs['status'])){ echo $rs['status']; }else{ } ?>  
              
              <?php if($rs['status']=="Pending"){ ?>
              <a href="javascript:void(0)" data-email="<?php echo $rs['email_id']; ?>" data-staffid="<?php echo $rs['staff_id']; ?>" data-reqid="<?php echo $rs['request_id']; ?>" data-currentamount="<?php echo $staff_detail[0]; ?>" data-requestamount="<?php if(isset($rs['request_amount'])){ echo $rs['request_amount']; }else{ } ?>" class="btn btn-success ld-btn-width accept_staff_request">Accept</a></td>
              
              <?php }else{ ?>
                <i class="fa fa-check-circle" style="font-size:18px;color:Green"></i>
            <?php } ?>
            </tr>
            <?php 
          } }
          ?>
          </tbody>
        </table>
        </div>
        </form>
        </div>

    </div>
    </div>
</div>

<?php 
include(dirname(__FILE__).'/footer.php');
?>
<script type="text/javascript">
    var ajax_url = '<?php echo AJAX_URL;?>';
    var servObj={'site_url':'<?php echo SITE_URL.'assets/images/business/';?>'};
    var imgObj={'img_url':'<?php echo SITE_URL.'assets/images/';?>'};
</script>