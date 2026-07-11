<?php
 if(isset($_REQUEST['p']) && $_REQUEST['p']!=''){
		$purchase_code_string =$_REQUEST['p'];
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		
		$connectionstring = mysqli_connect('localhost','bonnt2an_license','OE(7ZM+D?_Fa','bonnt2an_license');

		
		$q="select * from licenses where product='ct' and verify_code='".$purchase_code_string."'";
		$res= $connectionstring->query($q);
		$rowcount=mysqli_num_rows($res);
		
		if($rowcount>0){
			
			foreach($res as $vale){
				echo "This Purchase code working on ".$vale['user_domain']." Domain <br>";
			}
		}else{
			echo "This Purchase code is not in working";
		}
 }

?>
		
		