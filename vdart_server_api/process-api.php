<?php
	session_start();
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
	header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
	header("Content-Type: application/json; charset=utf-8");

	include "model/db.php";
	
	$postjson = json_decode(file_get_contents('php://input'), true);
	$today	  = date('Y-m-d');

	// query for Register
	if($postjson['aksi'] == 'register') {
		$query = mysqli_query($conn, "INSERT INTO `vdart_login_tbl` SET 
		user_name = '$postjson[user_name]',
		user_email_id = '$postjson[user_mail_id]',
		user_job_position = '$postjson[user_job_position]',
		user_password = '$postjson[user_password]',
		status = '1',
		created_at = '$today' ");
		$login_id = mysqli_insert_id($conn);

		if($query){
			$queryUser = mysqli_query($conn, "INSERT INTO `vdart_user_tbl` SET 
									emp_name = '$postjson[user_name]',
									emp_job_position = '$postjson[user_job_position]',
									login_id = '$login_id',
									status = '1' ");
			$result = json_encode(array('success' => true));
		}else{
			$result = json_encode(array('success' => false, 'msg'=>'error, something went to wrong'));
		}		
		echo $result;	
	}

	// query for Login
	elseif($postjson['aksi'] == 'login') {
		$query = mysqli_query($conn, "SELECT * FROM `vdart_login_tbl` WHERE user_email_id='$postjson[userName]' AND user_password='$postjson[userPassword]'");
		$count = mysqli_num_rows($query);
		$user_data = mysqli_fetch_array($query, MYSQLI_ASSOC);
		if($count>0){
			$data = $user_data;
			// $dataUser = array(
			// 	$_SESSION['data'] = $data;
			// );
			$_SESSION['data'] = $data;
			$dataUser = $_SESSION['data'];
			if($data['status']=='1'){
				$result = json_encode(array('success'=>true, 'result'=>$dataUser));
			}else{
				$result = json_encode(array('success'=>false, 'msg'=>'Account Inactive!'));
			}
		}else{
			$result = json_encode(array('success'=>false, 'msg'=>'Unregistered Account!'));
		}	
		echo $result;	
	}

	// query for upload
	elseif($postjson['aksi'] == 'upload'){
		// $query = mysqli_query($conn, "INSERT INTO `vdart_user_tbl` SET 
		// emp_name = '$postjson[empName]',
		// emp_job_position = '$postjson[empPosition]',
		// emp_description = '$postjson[empDescription]',
		// emp_post_image = '',
		// status = '1' ");
		$query = mysqli_query($conn, "SELECT MAX(login_id) AS LastID FROM vdart_user_tbl ");
		
		$result = mysqli_fetch_array($query, MYSQLI_ASSOC);
		$last_id = $result['LastID'];

		$query = mysqli_query($conn, "UPDATE `vdart_user_tbl` SET `emp_description` = '$postjson[empDescription]' WHERE `user_id` = '$last_id' ");
		if($query){
			$result = json_encode(array('success' => true, 'msg'=>'Your Post Successfully Updated!'));
		}
	
		else $result = json_encode(array('success' => false, 'msg'=>'error, something went to wrong'));
		
		echo $result;
	}

	elseif($postjson['aksi'] == 'timeline'){
		$query = mysqli_query($conn, "SELECT `emp_name`, `emp_job_position`, `emp_description`, `created_at`,  `login_id` FROM `vdart_user_tbl` WHERE status = '1' ORDER BY user_id DESC ");
		$response = array();

		while($row = mysqli_fetch_array($query))
		{
		array_push($response, array("emp_name"=>$row[0],
									"emp_job_position"=>$row[1],
									"emp_description"=>$row[2],
									"created_at"=>$row[3],
									"login_id"=>$row[4]));

		}

		if($query) $result = json_encode(array('success' => $response));
		else $result = json_encode(array('success' => false, 'msg'=>'error, something went to wrong'));
		
		echo $result;
	}

?>