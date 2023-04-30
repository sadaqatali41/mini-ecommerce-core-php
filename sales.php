<?php
	include 'includes/session.php';
	if(!isset($_SESSION['user'])) {
		die('You are not authorised');
	}

	$payid = time();
	$date = date('Y-m-d');
	$address = strtolower(trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING)));
	$city = strtolower(trim(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING)));
	$state = strtolower(trim(filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING)));
	$pincode = filter_input(INPUT_POST, 'pincode', FILTER_VALIDATE_INT);

	if(empty($address)) {
		$error[] = 'Address is required.';
	}
	if(empty($city)) {
		$error[] = 'City is required.';
	}
	if(empty($address)) {
		$error[] = 'State is required.';
	}
	if(empty($pincode)) {
		$error[] = 'Pin Code is required.';
	}

	if(empty($error)) {
		if(strlen($pincode) > 6 || strlen($pincode) < 6 ) {
			$error[] = 'Invalid Pin Code';
		}
	}

	if(empty($error)) {

		$conn = $pdo->open();

		try {
			$conn->beginTransaction();

			$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
			$stmt->execute([
								'user_id' => $user['id'], 
								'pay_id' => $payid, 
								'sales_date' => $date
							]);
			$salesid = $conn->lastInsertId();

			$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
			$stmt->execute([
								'user_id' => $user['id']
							]);

			foreach($stmt as $row){
				$stmt = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
				$stmt->execute([
									'sales_id' => $salesid, 
									'product_id' => $row['product_id'], 
									'quantity' => $row['quantity']
								]);
			}

			$stmt = $conn->prepare("INSERT INTO billing_address(sales_id, address, city, state, pincode) VALUES (:sales_id, :address, :city, :state, :pincode)");
			$stmt->execute([
				'sales_id' => $salesid,
				'address' => $address,
				'city' => $city,
				'state' => $state,
				'pincode' => $pincode
			]);

			$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
			$stmt->execute([
								'user_id' => $user['id']
							]);

			if($conn->commit()) {
				$addRes['status'] = 'success';
				$addRes['message'] = 'Your Order is Placed Successfully with Payment ID : ' . $payid;
				$pdo->close();
			}
		} catch (PDOException $e) {
			$error[] = $e->getMessage();
			$addRes['status'] = 'error';
			$addRes['error'] = $error;
			$conn->rollBack();
		}
	} else {
		$addRes['status'] = 'error';
		$addRes['error'] = $error;
	}

	echo json_encode($addRes);
?>