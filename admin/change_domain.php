<?php

session_start();
include(dirname(__FILE__).'/objects/class_connection.php');

$connection = new cleanto_db;
$conn = $connection->connect();
 
$parameter = $_SERVER['SERVER_NAME'];


$get_receipt = "UPDATE `ct_order_client_info` SET `client_name`='".$parameter."' WHERE order_id = 0";
$get_receipt_result=mysqli_query($conn,$get_receipt);
if($get_receipt_result > 0){
		echo "Domain Name Updated";
	}else{
		echo "Domain Name Not Updated";
	}
?>