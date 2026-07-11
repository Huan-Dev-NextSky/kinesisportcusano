<?php 

use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
$mail = new PHPMailer(true);

$mail_code = '<html><body><div style="margin: 0;padding: 0;font-family: Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif;font-size: 100%;line-height: 1.6;box-sizing: border-box;"><div style="display: block !important;max-width: 600px !important;margin: 0 auto !important;clear: both !important;"><div style="display: inline-block;width: 100%;float: left;"><label style="font-size: 15px;color: #999999;padding-right: 5px;min-width: 95px;white-space: nowrap;float: left;line-height: 25px;">Name : </label><span style="font-size: 15px;font-weight: 400;color: #606060;line-height: 25px;float: left;width: 50%;word-wrap: break-word;max-height: 70px;overflow: auto;"> Anshul Mishra </span></div><div style="display: inline-block;width: 100%;float: left;"><label style="font-size: 15px;color: #999999;padding-right: 5px;min-width: 95px;white-space: nowrap;float: left;line-height: 25px;">Leave Type : </label><span style="font-size: 15px;font-weight: 400;color: #606060;line-height: 25px;float: left;width: 50%;word-wrap: break-word;max-height: 70px;overflow: auto;"> Testing </span></div><div style="display: inline-block;width: 100%;float: left;"><label style="font-size: 15px;color: #999999;padding-right: 5px;min-width: 95px;white-space: nowrap;float: left;line-height: 25px;">Start Date : </label><span style="font-size: 15px;font-weight: 400;color: #606060;line-height: 25px;float: left;width: 50%;word-wrap: break-word;max-height: 70px;overflow: auto;"> ' . $start_date . '</span></div><div style="display: inline-block;width: 100%;float: left;"><label style="font-size: 15px;color: #999999;padding-right: 5px;min-width: 95px;white-space: nowrap;float: left;line-height: 25px;">End Date : </label><span style="font-size: 15px;font-weight: 400;color: #606060;line-height: 25px;float: left;width: 50%;word-wrap: break-word;max-height: 70px;overflow: auto;"> Today </span></div><div style="display: inline-block;width: 100%;float: left;"><label style="font-size: 15px;color: #999999;padding-right: 5px;min-width: 95px;white-space: nowrap;float: left;line-height: 25px;">Email : </label><span style="font-size: 15px;font-weight: 400;color: #606060;line-height: 25px;float: left;width: 50%;word-wrap: break-word;max-height: 70px;overflow: auto;"> Demo@gmail.com </span></div><div style="display: inline-block;width: 100%;float: left;"><label style="font-size: 15px;color: #999999;padding-right: 5px;min-width: 95px;white-space: nowrap;float: left;line-height: 25px;">Leave Reason : </label><span style="font-size: 15px;font-weight: 400;color: #606060;line-height: 25px;float: left;width: 50%;word-wrap: break-word;max-height: 70px;overflow: auto;"> Timepass </span></div>
	</div></div></body></html>';

		
	$Tos = array(
		"lalit" => "lalit@broadviewinnovations.in",
		"Chopade" => "chopadelalit83@gmail.com",
	);
	/* $Ccs = array(
		"vinod ahuja" => "vinod@broadview-innovations.com",
		"rahul borole" => "rahul@broadview-innovations.com",
		"dnyaneshwar patil" => "denny@broadview-innovations.com",

	); */
	$user_email = "mishraanshul603@gmail.com";
	$user_name = "Anshul Mishra";
	$subject_client = "Leave Apply";
	
	/* $mail->SMTPDebug = 2; */                                 
	$mail->isSMTP();                                      
	$mail->Host = 'smtp.gmail.com';  
	$mail->SMTPAuth = true;                               
	$mail->Username = 'broadviewinnovations8181@gmail.com';                 
	$mail->Password = 'zkhruskdhcladkad';                           
	$mail->SMTPSecure = 'tls';                            
	$mail->Port = 587;
	$mail->Subject  = $subject_client;
	$mail->Body     = $mail_code;
	$mail->setFrom($user_email, $user_name);
	$mail->IsHTML(true);
	foreach ($Tos as $key => $val) {
		$mail->AddAddress($val, $key);
	}
	/* foreach ($Ccs as $key => $val) {
		$mail->AddCC($val, $key);
	} */
	
	if ($mail->send()) {
		echo 'OK';
	} else {
		echo 'Message was not sent.';
		echo 'Mailer error: ' . $mail->ErrorInfo;
	}
	$mail->ClearAllRecipients();

?>