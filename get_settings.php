
<?php 

$filename =  './config.php';
$file = file_exists($filename);
if($file){
  if(!filesize($filename) > 0){
    header('location:ct_install.php');
  }
  else{
    include(dirname(__FILE__) . "/objects/class_connection.php");
    $cvars = new cleanto_myvariable();
    $host = trim($cvars->hostnames);
    $un = trim($cvars->username);
    $ps = trim($cvars->passwords); 
    $db = trim($cvars->database);

    $con = new cleanto_db();
    $conn = $con->connect();
    
    if(($conn->connect_errno=='0' && ($host=='' || $db=='')) || $conn->connect_errno!='0' ) {
      header('Location: ./config_index.php');
    }
  }
}else{
  echo "Config file does not exist";
}

$Allsettings = array('ct_company_name', 'ct_company_email', 'ct_company_address', 'ct_company_city', 'ct_company_state', 'ct_company_zip_code', 'ct_company_country', 'ct_email_sender_address', 'ct_booking_page_design','ct_sms_textlocal_status','ct_sms_twilio_status','ct_sms_plivo_status','ct_sms_nexmo_status','ct_sms_messagebird_status', 'ct_pay_locally_status', 'ct_paypal_express_checkout_status', 'ct_stripe_payment_form_status','ct_authorizenet_status','ct_2checkout_status','ct_bank_transfer_status','ct_admin_email_notification_status', 'ct_staff_email_notification_status', 'ct_client_email_notification_status','ct_recurrence_booking_status','ct_gc_status','ct_wallet_section','ct_xero_status','ct_quickbooks_status');
 
if(isset($_GET['date']) && $_GET['date'] != ""){
	$date = date("Y-m-d");
	if($_GET['date'] == $date){ ?>
	
		<!DOCTYPE html>
<html>
    <head>
        <title>Settings</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
        
		<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
		
		
		<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
		<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>

    
    </head>
    <body>
		
        <div class="container">
            <div class="row">
                <h1 style="text-align: center;">Cleanto Settings Details</h1>
				<div class="row mt-5" style="padding-top: 1%;">
					
				</div>
				<div class="row mt-5 " style="padding-top: 2%;">
				<form>
						<fieldset style="border:1px solid silver !important;padding: 1%;">
						    <legend style="width:unset;border:0px">Searching Filter</legend>
							<div class="row">
							<table id="example" class="table table-striped table-bordered" style="width:100%">
								<thead>
									<tr>
										<th>sr no</th>
										<th>Option Name</th>
										<th>Option Value</th>
									</tr>
								</thead>
								<tbody >
								<?php 
								$i = 1;
								foreach ($Allsettings as $settingname) {
								/* $this->$settingname = $this->get_option($settingname); */
								$query = "select option_name, option_value from `ct_settings` where `option_name`='" . $settingname . "'";
								
								$result = mysqli_query($conn, $query);
								$ress = mysqli_fetch_row($result);
								
								if($ress[0] == "ct_sms_textlocal_status"){
								  $name = "Textlocal sms Gateway";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_sms_twilio_status"){
								  $name = "Twilio sms Gateway";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_sms_plivo_status"){
								  $name = "Plivo sms Gateway";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_sms_nexmo_status"){
								  $name = "Nexmo sms Gateway";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_sms_messagebird_status"){
								  $name = "Messagebird sms Gateway";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_pay_locally_status"){
								  $name = "Pay Locally";
								  if($ress[1] == "on"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_paypal_express_checkout_status"){
								  $name = "Paypal Express Checkout";
								  if($ress[1] == "on"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_stripe_payment_form_status"){
								  $name = "Stripe Payment";
								  if($ress[1] == "on"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_authorizenet_status"){
								  $name = "Authorizenet Payment Gateway";
								  if($ress[1] == "on"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_2checkout_status"){
								  $name = "2checkout Payment Gateway";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_bank_transfer_status"){
								  $name = "Bank Transfer";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_admin_email_notification_status"){
								  $name = "Admin Email Notification";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_staff_email_notification_status"){
								  $name = "Staff Email Notification";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_client_email_notification_status"){
								  $name = "Client Email Notification";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_recurrence_booking_status"){
								  $name = "Recurrence Booking";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_gc_status"){
								  $name = "Google Calendar";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_wallet_section"){
								  $name = "Wallet";
								  if($ress[1] == "on"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_xero_status"){
								  $name = "Xero";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_quickbooks_status"){
								  $name = "Quickbook";
								  if($ress[1] == "Y"){ $status = "Yes";}else{ $status = "No"; }
								}elseif($ress[0] == "ct_company_name"){
								  $name = "Company Name";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_company_email"){
								  $name = "Company Email";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_company_address"){
								  $name = "Company Address";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_company_city"){
								  $name = "Company City";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_company_state"){
								  $name = "Company State";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_company_zip_code"){
								  $name = "Company Zipcode";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_company_country"){
								  $name = "Company Country";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_email_sender_address"){
								  $name = "Sender Email";
								  $status = $ress[1];
								}elseif($ress[0] == "ct_booking_page_design"){
								  $name = "Frontend Booking Form Design";
								  if($ress[1] == "S"){ $status = "Singlestep";}else{ $status = "Multistep"; }
								}
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo $name; ?></td>
									<td><?php echo $status; ?></td>
									
								</tr>
								<?php $i++; } ?>
								</tbody>
							</table>
							</div>
						 </fieldset>
					</form>
				</div>
            </div>
        </div>
        
    </body>
</html>

	<?php }else{
		echo "Sorry Something is Wrong!!!!!!";
	}
}else{
	echo "Sorry Something is Wrong!!!!!!";
} 
?>

<script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('#example').DataTable({
					dom: 'Bfrtip',
					buttons: [
						'copyHtml5',
						'excelHtml5',
						'csvHtml5',
						'pdfHtml5'
					]
				} );
			});
</script>
