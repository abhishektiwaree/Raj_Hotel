<?php
session_cache_limiter('nocache');
include("scripts/settings.php");
logvalidate($_SESSION['username'], $_SERVER['SCRIPT_FILENAME']);
logvalidate('admin');
$response = 1;
$msg = '';
date_default_timezone_set('Asia/Kolkata'); // Adjust based on your region
page_header();
$tab = 1;
$con = $db;

$attachments = [];
if (isset($_GET['e_id'])) {
    $e_id = mysqli_real_escape_string($db, $_GET['e_id']);
    $sql = "SELECT * FROM attachment WHERE advance_id = '$e_id'";
    $result = execute_query($sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $attachments[] = $row; // Store existing attachments in an array
    }
}
// print_r($attachments);
$editData = [
	'cat_id' => '',
	'number_of_room' => '',
	'room_tariff' => '',
	'room_number' => ''
];
if (isset($_GET['e_id'])) {
	$sql_edit = 'SELECT * FROM `advance_booking` WHERE `sno`="' . $_GET['e_id'] . '"';
	$result_edit = execute_query($sql_edit);
	$row_edit = mysqli_fetch_array($result_edit);
	$sql = 'select * from customer where sno=' . $row_edit['cust_id'];
	//echo $sql;
	$result = execute_query($sql);
	$cust_details = mysqli_fetch_assoc($result);

	$sql_trans = 'SELECT * FROM `customer_transactions` WHERE `advance_booking_id`="' . $_GET['e_id'] . '"';
	$result_trans = execute_query($sql_trans);
	$row_trans = mysqli_fetch_array($result_trans);


	$sql = 'SELECT cat_id, number_of_room,room_tariff, room_number FROM advance_booking WHERE sno = "' . $_GET['e_id'] . '"';
	$result = execute_query($sql);

	if ($row = mysqli_fetch_assoc($result)) {
		$editData = $row;

		// Convert comma-separated values into an array
		$editData['cat_id'] = explode(',', $row['cat_id']);
		$editData['number_of_room'] = explode(',', $row['number_of_room']);
		$editData['room_tariff'] = explode(',', $row['room_tariff']);
		$editData['room_number'] = explode(',', $row['room_number']);
	}
}
// echo '<pre style="margin-left:200px;">';
// print_r($row_edit);
// echo '</pre>';
if (isset($_POST['submit'])) {
	if ($_POST['company_name'] == '' and $_POST['cust_name1'] == '') {
		$msg .= '<li class="error">Enter Bill To Details.</li>';
	}
	if ($msg == '') {
		if ($_POST['cust_sno'] == '') {
			$sql = 'INSERT INTO customer (company_name, cust_name, mobile, id_2, address, created_by, created_on , state) VALUES ("' . $_POST['company_name'] . '", "' . $_POST['cust_name1'] . '", "' . $_POST['mobile'] . '", "' . $_POST['id_2'] . '", "' . $_POST['address'] . '", "' . $_SESSION['username'] . '" ,CURRENT_TIMESTAMP , "UTTAR PRADESH")';
			$result_trans = execute_query($sql);
			$msg .= '<li class="error">Customer Added successfully</li>';
			$_POST['cust_sno'] = $db->insert_id;
		}
		if ($_POST['cust_sno'] != '') {
			$sql = 'update customer set 
			cust_name="' . $_POST['cust_name1'] . '"
			where sno=' . $_POST['cust_sno'];
			$result = execute_query($sql);
		} else {
			$inv_no = '';
			$exit_date = '';
		}

		$con = $db;
		$date = $_POST['allotment_date'];
		$time = strtotime($date);
		$month = date("m", $time);
		$year = date("Y", $time);
		if ($month >= 1 && $month <= 3) {
			$year = $year - 1;
		}
		$i = 0;
		$n = 1;
		$status = 0;
		if ($_POST['purpose'] == "advance_for") {
			$sql_type = 'SELECT * FROM `advance_booking` WHERE `sno`="' . $_POST['advance_for'] . '"';
			$result_type = execute_query($sql_type);
			$row_type = mysqli_fetch_array($result_type);
			$type = $row_type['purpose'];
			$_POST['allotment_date'] = $row_type['allotment_date'];
			if ($row_type['status'] == "1") {
				$status = 1;
			}
			$_POST['advance_for_checkin'] = '';
		} elseif ($_POST['purpose'] == "advance_for_checkin") {
			$_POST['advance_for'] = '';
			$type = 'room_rent';
			$status = 1;
			$sql_room_number = mysqli_fetch_array(execute_query('SELECT * FROM `allotment` WHERE `sno`="' . $_POST['advance_for_checkin'] . '"'));
			$row_room_number = mysqli_fetch_array(execute_query('SELECT * FROM `room_master` WHERE `sno`="' . $sql_room_number['room_id'] . '"'));
			$_POST['room_number'] = $row_room_number['room_name'];
		} else {
			$_POST['advance_for'] = '';
			$_POST['advance_for_checkin'] = '';
			$type = $_POST['purpose'];
		}




		// print_r($editData['cat_id'] );
		if ($_POST['edit_sno'] != '') {
			// Convert category array to a comma-separated string
			if (isset($_POST['edit_sno'])) {

				$catArray = isset($_POST['cat']) ? $_POST['cat'] : [];
				$numberOfRoomArray = isset($_POST['number_of_room']) ? $_POST['number_of_room'] : [];
				$tariffOfRoomArray = isset($_POST['room_tariff']) ? $_POST['room_tariff'] : [];
				$roomNumberArray = isset($_POST['room_number']) ? $_POST['room_number'] : [];

				// Convert arrays to comma-separated strings
				$catString = implode(',', $catArray);
				$numberOfRoomString = implode(',', $numberOfRoomArray);
				$tariffOfRoomString = implode(',', $tariffOfRoomArray);
				$roomNumberString = implode(',', $roomNumberArray);

				// Update the database
				$sql_update = 'UPDATE `advance_booking` SET
						`cust_id`="' . $_POST['cust_sno'] . '",
						`financial_year`="' . $year . '",
						`allotment_date`="' . $_POST['allotment_date'] . '",
						`check_in`="' . $_POST['check_in'] . '",
						`check_out`="' . $_POST['check_out'] . '",
						`edited_by`="' . $_SESSION['username'] . '",
						`edited_on`= CURRENT_TIMESTAMP,
						`remarks`="' . $_POST['remarks'] . '",
						`guest_name`="' . $_POST['cust_name1'] . '",
						`status`="' . $status . '",
						`advance_amount`="' . $_POST['advance_amount'] . '",
						`total_amount`="' . $_POST['total_amount'] . '",
						`due_amount`="' . $_POST['due_amount'] . '",
						`purpose`="' . $_POST['purpose'] . '",
						`kitchen_dining`="' . $_POST['kitchen_dining'] . '",
						`kitchen_amount`="' . $_POST['kitchen_amount'] . '",
						`advance_for_id`="' . $_POST['advance_for'] . '",
						`advance_for_checkin_id`="' . $_POST['advance_for_checkin'] . '",
						`number_of_room`="' . $numberOfRoomString . '",
						`cat_id`="' . $catString . '",
						`room_number`="' . $roomNumberString . '",
						`room_tariff`="' . $tariffOfRoomString . '"
						WHERE `sno`="' . $_POST['edit_sno'] . '"';
			}


			execute_query($sql_update);

			$sql_trans_update = 'UPDATE `customer_transactions` SET
								`cust_id`="' . $_POST['cust_sno'] . '" ,
								`amount`="' . $_POST['advance_amount'] . '" ,
								`mop`="' . $_POST['mop'] . '" ,
								`edited_by` = "' . $_SESSION['username'] . '" ,
								`edited_on` = CURRENT_TIMESTAMP
								WHERE `advance_booking_id`="' . $_POST['edit_sno'] . '" AND `type`="ADVANCE_AMT"';
			execute_query($sql_trans_update);
			//echo $sql_trans_update;
			$msg .= '<li class="error">Update Successfully</li>';
		} else {
			// Convert category array to a comma-separated string
			$catArray = isset($_POST['cat']) ? $_POST['cat'] : [];
			$numberOfRoomArray = isset($_POST['number_of_room']) ? $_POST['number_of_room'] : [];
			$tariffOfRoomArray = isset($_POST['room_tariff']) ? $_POST['room_tariff'] : [];
			$roomNumberArray = isset($_POST['room_number']) ? $_POST['room_number'] : [];

			$catString = implode(',', $catArray);  // Convert category array to comma-separated string
			$numberOfRoomString = implode(',', $numberOfRoomArray);
			$tariffOfRoomString = implode(',', $tariffOfRoomArray);
			$roomNumberString = implode(',', $roomNumberArray);  // Convert room numbers array to string

			// Correcting the SQL query syntax
			$sql = 'INSERT INTO advance_booking (guest_name, cust_id, financial_year, allotment_date, check_in, check_out, created_by, created_on, remarks, status, advance_amount, total_amount, due_amount, purpose, advance_for_id, advance_for_checkin_id, number_of_room, room_number, cat_id,kitchen_dining,kitchen_amount,room_tariff) 
			VALUES (
				"' . mysqli_real_escape_string($db, $_POST['cust_name1']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['cust_sno']) . '", 
				"' . mysqli_real_escape_string($db, $year) . '", 
				"' . mysqli_real_escape_string($db, $_POST['allotment_date']) . '",
				"' . mysqli_real_escape_string($db, $_POST['check_in']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['check_out']) . '", 
				"' . mysqli_real_escape_string($db, $_SESSION['username']) . '", 
				CURRENT_TIMESTAMP, 
				"' . mysqli_real_escape_string($db, $_POST['remarks']) . '", 
				"' . mysqli_real_escape_string($db, $status) . '", 
				"' . mysqli_real_escape_string($db, $_POST['advance_amount']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['total_amount']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['due_amount']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['purpose']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['advance_for']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['advance_for_checkin']) . '", 
				"' . mysqli_real_escape_string($db, $numberOfRoomString) . '",  
				"' . mysqli_real_escape_string($db, $roomNumberString) . '", 
				"' . mysqli_real_escape_string($db, $catString) . '",
				"' . mysqli_real_escape_string($db, $_POST['kitchen_dining']) . '", 
				"' . mysqli_real_escape_string($db, $_POST['kitchen_amount']) . '",
				"' . mysqli_real_escape_string($db, $tariffOfRoomString) . '"
			)';

			$result = execute_query($sql);
			$allot_id = mysqli_insert_id($db);
			$sql = "select * from advance_booking order by sno desc limit 1";
			$result = execute_query($sql);
			$old_data1 = mysqli_fetch_assoc($result);
			$alot_id = $old_data1['sno'];
			if ($_POST['advance_amount'] > 0) {
				$sql = 'INSERT INTO customer_transactions (cust_id , advance_booking_id , type , timestamp, amount, mop, created_by , created_on , remarks , invoice_no , financial_year , payment_for) VALUES ("' . $_POST['cust_sno'] . '", "' . $alot_id . '" , "ADVANCE_AMT" , "' . date('Y-m-d') . '"  , "' . $_POST['advance_amount'] . '" , "' . $_POST['mop'] . '", "' . $_SESSION['username'] . '" ,CURRENT_TIMESTAMP, "' . $_POST['remarks'] . '","","' . $year . '" , "' . $type . '")';
				$result = execute_query($sql);
			}
			
			// Insert attachments into the 'attachment' table
			if (!empty($_FILES['file_path']['name'][0])) {
				$uploadDir = "uploads/"; // Your actual upload directory
			
				foreach ($_FILES['file_path']['name'] as $key => $filename) {
					$fileTmpPath = $_FILES['file_path']['tmp_name'][$key];
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION); // Get file extension
					$description = mysqli_real_escape_string($db, $_POST['description'][$key]);
			
					// First, insert a record into the `attachment` table with a placeholder for file_path
					$sql = 'INSERT INTO attachment (advance_id, file_path, description) 
							VALUES ("' . $allot_id . '", "", "' . $description . '")';
					execute_query($sql);
			
					// Now, get the `sno` (ID) of the inserted record
					$attachment_id = mysqli_insert_id($db);
			
					// Define the new file name using sno.extension
					$newFileName = $attachment_id . "." . $fileExt;
					$filePath = $uploadDir . $newFileName;
			
					// Move the uploaded file to the new file path
					if (move_uploaded_file($fileTmpPath, $filePath)) {
						// Update the `attachment` table with the correct file path
						$updateSql = 'UPDATE attachment SET file_path = "' . mysqli_real_escape_string($db, $filePath) . '" WHERE sno = "' . $attachment_id . '"';
						execute_query($updateSql);
					}
				}
			}
			


			$msg .= '<li class="error">Booking Successfully</li>';
			$msg .= '<li class="error"><a href="advance_print.php?print_id=' . $alot_id . '" target="_blank">Print Receipt</a></li>';



		}
	}

}
if (isset($_GET['del'])) {
	$sql = 'select * from allotment_2 where sno=' . $_GET['del'];
	$result = execute_query($sql);
	$sql = 'select * from allotment where sno=' . $_GET['del'];
	$result = execute_query($sql);
	$row = mysqli_fetch_assoc($result);
	$sql = 'update room_master set status=0 where sno=' . $row['room_id'];
	$result = execute_query($sql);
	$sql = 'delete from customer_transactions where allotment_id=' . $row['sno'];
	$result = execute_query($sql);
	$sql = 'delete from allotment where sno=' . $_GET['del'];
	$result = execute_query($sql);

}
?>
<style>
	.ui-autocomplete-loading {
		background: white url('images/ui-anim_basic_16x16.gif') right center no-repeat;
	}
</style>
<script src="js/jquery.datetimepicker.full.js"></script>
<script type="text/javascript" language="javascript">
	function get_rent() {
		var room_rent = document.getElementById("rent").value;
		var extrabed = document.getElementById("extrabed").value;
		room_rent = parseFloat(room_rent);
		extrabed = parseFloat(extrabed);
		var total_rent = room_rent + extrabed;
		total_rent = parseFloat(total_rent);
		document.getElementById('total_rent').value = total_rent;
	}

	function get_room_rent() {
		val = $("#room_id").val();
		allot = $("#allot_sno").val();
		$.ajax({
			url: "scripts/ajax.php?id=rent_room&allot=" + allot + "&term=" + val,
			dataType: "json"
		})
			.done(function (data) {
				var txt = '<table width="100%"><tr><th>Room No.</th><th>Occupancy</th><th>Base Rent</th><th>Extra Bed</th><th >Taxable</th><th>CGST</th><th>SGST</th><th>Net Price</th>';
				var tot_rent = 0;
				$.each(data, function (index, value) {
					var allot_sno = $("#allot_sno").val();
					var occupancy = $("#occupancy").val();
					if (allot_sno != '') {
						var selected_id = parseFloat(occupancy);
						var disc_value = $("#discount").val();
						//var extrabed = $("#extrabed").val();

					}
					else {
						var selected_id = '';
						var disc_value = '';
						var extrabed = '';

					}

					txt += '<tr><td>' + value.label + '</td><td><select name="occupancy_' + value.id + '" id="occupancy_' + value.id + '" onChange="calculate()" class="medium">';
					for (var i = 1; i <= (parseFloat(value.occupancy) + 1); i++) {
						txt += '<option value="' + i + '" ';
						if (selected_id == i) {
							txt += ' selected="selected"';
						}
						txt += '>' + i + '</option>';
					}
					txt += '</select><input type="hidden" name="occupancy_total_' + value.id + '" id="occupancy_total_' + value.id + '" value="' + value.occupancy + '"><input type="hidden" name="rent_single_' + value.id + '" id="rent_single_' + value.id + '" value="' + value.rent + '"><input type="hidden" name="rent_double_' + value.id + '" id="rent_double_' + value.id + '" value="' + value.rent_double + '"><input type="hidden" name="rent_extra_' + value.id + '" id="rent_extra_' + value.id + '" value="' + value.rent_extra + '">';
					txt += '</td><td><input type="text" name="room_' + value.id + '" id="room_' + value.id + '" value="' + value.rent + '" onBlur="calculate()"></td><td><input type="text" name="extrabed_' + value.id + '" id="extrabed_' + value.id + '" value="' + value.extra_bed + '" onBlur="calculate()"> <input type="hidden" name="discount_' + value.id + '" id="discount_' + value.id + '" value="' + disc_value + '" onBlur="calculate()"><input type="hidden" name="discount_value_' + value.id + '" id="discount_value_' + value.id + '" value="" onBlur="calculate()"></td><td id="taxable_' + value.id + '"></td><td id="cgst_' + value.id + '"></td><td id="sgst_' + value.id + '"></td><td><input type="text" name="net_room_rent_' + value.id + '" id="net_room_rent_' + value.id + '" value="" onBlur="calculate()"></td></tr>';
					tot_rent += parseFloat(value.rent);
				});
				txt += '</table>';
				$("td#insertrow").html(txt);
				document.getElementById('rent').value = tot_rent;
				calculate();
				calculate();
			});
	}

	function calculate() {
		var tot_selected = $("#room_id").val();
		if (!Array.isArray(tot_selected)) {
			var tot_selected = [tot_selected];
		}
		var tot_rent = 0;
		var total_discount = 0;
		var net_rate = 0;
		$.each(tot_selected, function (index, value) {
			if (!!value) {
				var occupancy = parseFloat($("#occupancy_" + value).val());
				if (!occupancy) {
					occupancy = 0;
				}
				var occupancy_hidden = parseFloat($("#occupancy_total_" + value).val());
				if (!occupancy_hidden) {
					occupancy_hidden = 0;
				}
				var rent = parseFloat($("#room_" + value).val());
				if (!rent) {
					rent = 0;
				}
				var rent_single = parseFloat($("#rent_single_" + value).val());
				if (!rent_single) {
					rent_single = 0;
				}
				var rent_double = parseFloat($("#rent_double_" + value).val());
				if (!rent_double) {
					rent_double = 0;
				}
				//alert("#rent_extra_"+value);
				var rent_extra = $("#rent_extra_" + value).val();
				rent_extra = parseFloat(rent_extra.replace("%", ""));
				if (!rent_extra) {
					rent_extra = 0;
				}
				var temp_rent_extra = rent_double + rent_double * rent_extra / 100;
				//console.log(occupancy+'-'+occupancy_hidden+'-'+rent+'-'+rent_single+'-'+rent_double);
				if (rent != rent_single && rent != rent_double && rent != temp_rent_extra) {
					//console.log('cond0');
				}
				else {
					if (occupancy > 1 && occupancy <= occupancy_hidden) {
						rent = rent_double;
						//console.log('cond1');
					}
					else if (occupancy > occupancy_hidden) {
						rent_extra = rent_double * rent_extra / 100;
						rent = rent_double + rent_extra;
						//console.log('cond2');
					}
					else if (occupancy == 1) {
						rent = rent_single;
						//console.log('cond3');
					}
				}

				var discount = $("#discount_" + value).val();
				if (discount.search('%') == -1) {
					if (discount == 1) {
						var discount_value = Math.round(((rent * 10) / 100) * 100) / 100;
					}
					if (discount == 2) {
						var discount_value = Math.round(((rent * 20) / 100) * 100) / 100;
					}
					if (discount == 3) {
						var discount_value = Math.round(((rent * 30) / 100) * 100) / 100;
					}
					if (discount == 4) {
						var discount_value = Math.round(((rent * 40) / 100) * 100) / 100;
					}
					if (discount == 5) {
						var discount_value = Math.round(((rent * 50) / 100) * 100) / 100;
					}
					if (discount == 6) {
						var discount_value = Math.round(((rent * 60) / 100) * 100) / 100;
					}
					if (discount == 7) {
						var discount_value = Math.round(((rent * 70) / 100) * 100) / 100;
					}
					if (discount == 8) {
						var discount_value = Math.round(((rent * 80) / 100) * 100) / 100;
					}
					if (discount == 9) {
						var discount_value = Math.round(((rent * 90) / 100) * 100) / 100;
					}
					if (discount == 10) {
						var discount_value = Math.round(((rent * 100) / 100) * 100) / 100;
					}
					if (discount > 10) {
						var discount_value = parseFloat(discount);
					}
				}
				else {
					discount = discount.replace("%", "");
					var discount_value = Math.round(((rent * discount) / 100) * 100) / 100;
				}
				/*if(discount.search('%')==-1){
					var discount_value = parseFloat(discount);
				}
				else{
					discount = discount.replace("%","");
					var discount_value = Math.round(((rent * discount)/100)*100)/100;
				}*/
				if (!discount_value) {
					discount_value = 0;
				}

				var extrabed = $("#extrabed_" + value).val();
				//alert(extrabed);
				if (extrabed.search('%') == -1) {
					if (extrabed == 1) {
						var extrabed_value = Math.round(((rent * 25) / 100) * 100) / 100;
					}
					if (extrabed == 2) {
						var extrabed_value = Math.round(((rent * 50) / 100) * 100) / 100;
					}
					if (extrabed == 3) {
						var extrabed_value = Math.round(((rent * 75) / 100) * 100) / 100;
					}
					if (extrabed == 4) {
						var extrabed_value = Math.round(((rent * 100) / 100) * 100) / 100;
					}
					if (extrabed > 4) {
						var extrabed_value = parseFloat(extrabed);
					}
				}
				else {
					extrabed = extrabed.replace("%", "");
					var extrabed_value = Math.round(((rent * extrabed) / 100) * 100) / 100;
					//var extrabed_value = parseInt(extrabed);
					//alert(extrabed_value);
				}
				/*if(extrabed.search('%')==-1){
					var extrabed_value = parseFloat(extrabed);
					//alert(extrabed_value);
				}
				else{
					extrabed = extrabed.replace("%","");
					var extrabed_value = Math.round(((rent * extrabed)/100)*100)/100;
					//var extrabed_value = parseInt(extrabed);
					//alert(extrabed_value);
				}*/
				if (!extrabed) {
					extrabed_value = '';
				}
				var taxable = rent + extrabed_value - discount_value;
				if (!taxable) {
					taxable = 0;
				}
				if (taxable > 999) {
					var cgst = taxable * 6 / 100;
					var sgst = taxable * 6 / 100;
					net_rent = Math.round(taxable + cgst + sgst);
				}
				else {
					var cgst = 0;
					var sgst = 0;
					net_rent = Math.round(taxable + cgst + sgst);
				}
				$("#room_" + value).val(rent);
				$("#discount_" + value).val(discount_value);
				$("#discount_value_" + value).val(discount_value);
				$("#extrabed_" + value).val(extrabed_value);
				$("#taxable_" + value).html(taxable);
				$("#cgst_" + value).html(cgst);
				$("#sgst_" + value).html(sgst);
				$("#net_room_rent_" + value).val(net_rent);
				//console.log(rent);
				tot_rent += net_rent;
			}
		});
		if (!tot_rent) {
			tot_rent = 0;
		}
		$("#rent").val(tot_rent);
	}
	function calc() {
		var rent = parseFloat($("#rent").val());
		if (!rent) {
			rent = 0;
		}
		var discount = $("#discount").val();
		if (discount.search("%") == -1) {
			discount = parseFloat(discount);
			if (!discount) {
				discount = 0;
			}
			var net_rent = rent - discount;
			var discount_value = discount;
		}
		else {
			discount.replace("%", "");
			discount = parseFloat(discount);
			if (!discount) {
				discount = 0;
			}
			var discount_value = Math.round((rent * discount / 100) * 100) / 100;
			var net_rent = rent - discount_value;
		}
		var extrabed = $("#extrabed").val();
		if (extrabed.search("%") == -1) {
			extrabed = parseFloat(extrabed);
			if (!extrabed) {
				extrabed = '';
			}
			var net_rent = rent + extrabed;
		}
		else {
			extrabed.replace("%", "");
			extrabed = parseFloat(extrabed);
			if (!extrabed) {
				extrabed = '';
			}

			var net_rent = rent + extrabed;
		}

		if (!net_rent) {
			net_rent = 0;
		}
		$(".net_room_rent").html(net_rent);
		$("#net_room_rent").val(net_rent);
	}

	function get_advance_info() {
		var purpose = $("#purpose").val();
		var cust_id = $("#cust_sno").val();
		if (purpose == "advance_for") {
			$('#advance_item').show();
			$('#advance_item_checkin').hide();
		}
		else if (purpose == "advance_for_checkin") {
			$('#advance_item_checkin').show();
			$('#advance_item').hide();
		}
		else {
			$('#advance_item').hide();
			$('#advance_item_checkin').hide();
		}
	}

	$(function () {
		var options = {
			source: function (request, response) {
				$.getJSON("scripts/ajax.php?id=cust_name_advance", request, response);
			},
			minLength: 1,
			select: function (event, ui) {
				log(ui.item ?
					"Selected: " + ui.item.value + " aka " + ui.item.label :
					"Nothing selected, input was " + this.value);
			},
			select: function (event, ui) {
				$("[name='company']").val(ui.item.label);
				$('#cust_sno').val(ui.item.id);
				$('#cust_name1').val(ui.item.cust_name);
				$('#mobile').val(ui.item.mobile);
				$('#company_name').val(ui.item.company);
				$('#address').val(ui.item.address);
				//$('#id_1').val(ui.item.id_no);
				$('#id_2').val(ui.item.gst_no);
				$('#advance_item').html(ui.item.advance);
				$('#advance_item_checkin').html(ui.item.advance_checkin);
				$("#ajax_loader").show();
				return false;
			}
		};
		$("input#cust_name1").on("keydown.autocomplete", function () {
			$(this).autocomplete(options);
		});
	});


	$(function () {
		var options = {
			source: function (request, response) {
				$.getJSON("scripts/ajax.php?id=company_name_advance", request, response);
			},
			minLength: 1,
			select: function (event, ui) {
				log(ui.item ?
					"Selected: " + ui.item.value + " aka " + ui.item.label :
					"Nothing selected, input was " + this.value);
			},
			select: function (event, ui) {
				$("[name='company']").val(ui.item.label);
				$('#cust_sno').val(ui.item.id);
				$('#mobile').val(ui.item.mobile);
				$('#cust_name1').val(ui.item.cust_name);
				$('#company_name').val(ui.item.company);
				$('#address').val(ui.item.address);
				//$('#id_1').val(ui.item.id_no);
				$('#id_2').val(ui.item.gst_no);
				$('#advance_item').html(ui.item.advance);
				$('#advance_item_checkin').html(ui.item.advance_checkin);
				$("#ajax_loader").show();

				return false;
			}
		};
		$("input#company_name").on("keydown.autocomplete", function () {
			$(this).autocomplete(options);
		});
	});

	function calc_due(val) {
		var advance = parseFloat($("#advance_amount").val());
		if (!advance) {
			advance = 0;
		}
		var total_amount = parseFloat($("#total_amount").val());
		if (!total_amount) {
			total_amount = 0;
		}
		var due_amount = parseFloat($("#due_amount").val());
		if (!due_amount) {
			due_amount = 0;
		}
		//console.log(advance+'>>'+total_amount+'>>'+due_amount);
		due_amount = total_amount - advance;
		$("#due_amount").val(due_amount);
	}
</script>
<div id="container">
	<h2>Advance Booking</h2>
	<?php echo '<ul><h4>' . $msg . '</h4></ul>';
	$tab = 1; ?>
	<form action="advance_booking.php" class="wufoo leftLabel page1" name="addnewdesignation"
		enctype="multipart/form-data" method="post" onSubmit="">
		<table>
			<tr>
				<td>Guest Name : </td>
				<td><input id="cust_name1" name="cust_name1" value="<?php if (isset($row1['cust_name'])) {
					echo $row1['cust_name'];
				} else if (isset($_GET['e_id'])) {
					echo $row_edit['guest_name'];
				} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>"></td>
				<input type="hidden" name="cust_sno" id="cust_sno" value="<?php if (isset($_GET['alt'])) {
					echo $_GET['alt'];
				} else if (isset($_GET['e_id'])) {
					echo $row_edit['cust_id'];
				} else {
					echo $old_data['cust_id'];
				} ?>" />
				<td>Address : </td>
				<td><input id="address" name="address" value="<?php if (isset($row1['address'])) {
					echo $row1['address'];
				} else if (isset($_GET['e_id'])) {
					echo $cust_details['address'];
				} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" /></td>
			</tr>
			<tr>
				<td>Company Name : </td>
				<td><input id="company_name" name="company_name" value="<?php if (isset($row1['company_name'])) {
					echo $row1['company_name'];
				} else if (isset($_GET['e_id'])) {
					echo $cust_details['company_name'];
				} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" /></td>

				<td>Mobile : </td>
				<td><input id="mobile" name="mobile" value="<?php if (isset($row1['mobile'])) {
					echo $row1['mobile'];
				} else if (isset($_GET['e_id'])) {
					echo $cust_details['mobile'];
				} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" /></td>
			</tr>
			<!--<tr>
					<td>SAC/HSN</td>
					<td><input id="id_1" name="id_1" value="<?php if (isset($row1['id_1'])) {
						echo $row1['id_1'];
					} else if (isset($_GET['id'])) {
						echo $cust_details['id_1'];
					} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" /></td>		
				</tr>-->
			<tr>
				<td>GSTIN : </td>
				<td><input id="id_2" name="id_2" value="<?php if (isset($row1['id_2'])) {
					echo $row1['id_2'];
				} else if (isset($_GET['e_id'])) {
					echo $cust_details['id_2'];
				} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" /></td>
				<td>Booking Date : </td>
				<td><input name="allotment_date" type="text" value="<?php if (isset($old_data['allotment_date'])) {
					echo $old_data['allotment_date'];
				} else if (isset($_GET['e_id'])) {
					echo $row_edit['allotment_date'];
				} ?>" class="field text medium" tabindex="<?php echo $tab++; ?>" id="allotment_date" /></td>
				<!--<td>Plan</td>
					<td>
						<select name="plan">
							<option value="">Select</option>
							<?php
							$sql = "SELECT * FROM admin_plans";
							$res = execute_query($sql);
							while ($plan = mysqli_fetch_array($res)) {
								echo '<option  value="' . $plan['plan_name'] . '"';
								if (isset($_GET['id'])) {
									if ($old_data['plans'] == $plan['plan_name']) {
										echo 'selected="selected"';
									}
								}
								echo '>' . $plan['plan_name'] . '</option>';
							}
							?>
						</select>
					</td>-->
			</tr>
			<tr>
				<td>CheckIn Date : </td>
				<td><input name="check_in" type="text" value="<?php if (isset($old_data['check_in'])) {
					echo $old_data['check_in'];
				} else if (isset($_GET['e_id'])) {
					echo $row_edit['check_in'];
				} ?>" class="field text medium" tabindex="<?php echo $tab++; ?> " id="check_in" /></td>
				<td>CheckOut Date : </td>

				<td><input name="check_out" type="text" value="<?php if (isset($old_data['check_out'])) {
					echo $old_data['check_out'];
				} else if (isset($_GET['e_id'])) {
					echo $row_edit['check_out'];
				} ?>" class="field text medium" tabindex="<?php echo $tab++; ?>" id="check_out" /></td>

			</tr>
			<!--<tr>
					<td>Room No.</td>
					<td>	
						<table width="100%">
							<tr style="background: #ccc">
								<td width="90%">
									<select required name="room_id[]" id="room_id" tabindex="<?php echo $tab++; ?>" class="room_id" <?php if (!isset($_GET['id'])) {
										   echo 'multiple="multiple"';
									   } ?> onBlur="get_room_rent();" >
									<?php
									$sql = 'select * from room_master order by abs(room_name)';
									$result = execute_query($sql);
									while ($row_room = mysqli_fetch_array($result)) {
										echo '<option value="' . $row_room['sno'] . '" ';
										echo '>' . $row_room['room_name'] . '</option>';
									}
									?>
									</select>
								</td>
								<?php //echo $l; ?>
								<td><input type="button" onClick="get_room_rent();" style="float: right;" class="small" value="Fetch"></td>
							</tr>
						</table>
					</td>
					<input type="hidden" name="room_sno" id="room_sno" value="" />
					<td>Allotment Date</td>
					<td><input name="allotment_date" type="text" value="<?php if (isset($old_data['allotment_date'])) {
						echo $old_data['allotment_date'];
					} ?>" class="field text medium" tabindex="<?php echo $tab++; ?>" id="allotment_date" /></td>
				</tr>
				<tr>
					<td>Rent of Room</td>
					<td><input id="rent" name="rent" value="<?php echo $old_data['original_room_rent'] ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" /></td>
					
					<?php if (isset($_GET['id'])) { ?>
					   <td>Discount</td>
					<td><input id="discount" name="discount"  class="field text small" maxlength="255" tabindex="<?php echo $tab++; ?>" type="checkbox" onBlur="calc();" value=""<?php if ($old_data['other_discount'] != '' && $old_data['other_discount'] != 0) {
						   echo 'checked';
					   } else {
						   echo 'unchecked';
					   } ?> />Net Rate : <span class="net_room_rent"><?php echo $old_data['other_discount'] ?></span><input type="hidden" name="net_room_rent" id="net_room_rent" value="<?php echo $old_data['room_rent'] ?>"><input type="hidden" name="discount1" id="discount_value" value="<?php echo $old_data['discount_value'] ?>"></td>
					<?php } else { ?>
					   <td>Discount</td>
					<td><input id="discount" name="discount" value="<?php echo $old_data['discount'] ?>" class="field text small" maxlength="255" tabindex="<?php echo $tab++; ?>" type="hidden" onBlur="calc();" />Net Rate : <span class="net_room_rent"><?php echo $old_data['room_rent'] ?></span><input type="hidden" name="net_room_rent" id="net_room_rent" value="<?php echo $old_data['room_rent'] ?>"><input type="hidden" name="discount_value" id="discount_value" value="<?php echo $old_data['discount_value'] ?>"></td>
					<?php } ?>
				</tr>
			   <tr>
					<td>Occupancy</td>
					<td><input id="occupancy" name="occupancy" value="<?php echo $old_data['occupancy'] ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text"  <?php if (!isset($_GET['id'])) {
							  echo 'readonly';
						  } ?> /></td>  
					<td>Extra Bed</td>
					<?php if (isset($_GET['id'])) { ?>
					<td><input id="extrabed" name="extrabed" value="<?php echo $old_data['other_charges'] ?>" class="field text medium" maxlength="255" onBlur="calc();" tabindex="<?php echo $tab++; ?>" type="text" readonly /></td>  
					<?php } else { ?>  
					
					<td><input id="extrabed" name="extrabed" value="<?php echo $old_data['other_charges'] ?>" class="field text medium" maxlength="255" onBlur="calc();" tabindex="<?php echo $tab++; ?>" type="hidden"  /></td>  
					<?php } ?>			  
					
				</tr>
				<tr>
					<td>Total Rent</td>
					<td><input id="total_rent" name="total_rent" value="<?php echo $total_rent ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" /></td>
				</tr>-->
			<tr>
				<td>Remarks : </td>
				<td>
					<input id="remarks" name="remarks" value="<?php if (isset($old_data['remarks'])) {
						echo $old_data['remarks'];
					} else if (isset($_GET['e_id'])) {
						echo $row_edit['remarks'];
					} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" />
				</td>
				<td>Purpose : </td>
				<td>
					<select name="purpose" id="purpose" onchange="get_advance_info();">
						<option value="room_rent" <?php if (isset($_GET['e_id'])) {
							if ($row_edit['purpose'] == "room_rent") {
								echo 'selected';
							}
						} ?>>Room
							Booking</option>
						<!-- <option value="banquet_rent" <?php if (isset($_GET['e_id'])) {
							if ($row_edit['purpose'] == "banquet_rent") {
								echo 'selected';
							}
						} ?>>Banquet Booking</option>
							<option value="advance_for" <?php if (isset($_GET['e_id'])) {
								if ($row_edit['purpose'] == "advance_for") {
									echo 'selected';
								}
							} ?>>Advance For Previous Advance Boking</option>
							<option value="advance_for_checkin" <?php if (isset($_GET['e_id'])) {
								if ($row_edit['purpose'] == "advance_for_checkin") {
									echo 'selected';
								}
							} ?>>Advance For In House Guest</option> -->
					</select>
				</td>
			</tr>
			<tr>
				<td>Meal Plan :</td>
				<td><input type="text" name="kitchen_dining" id="kitchen_dining" value="<?php if (isset($_GET['e_id'])) {
					echo $row_edit['kitchen_dining'];
				} ?>"></td>
				<td>Amount :</td>
				<td><input type="text" name="kitchen_amount" id="kitchen_amount" value="<?php if (isset($_GET['e_id'])) {
					echo $row_edit['kitchen_amount'];
				} ?>"></td>
			</tr>
			<tr id="advance_item" style="display: none;">
				<?php
				if (isset($_GET['e_id'])) {
					$sql_advance = 'SELECT * FROM `advance_booking` WHERE `cust_id`="' . $row_edit['cust_id'] . '" AND purpose!="advance_for" ORDER BY `sno` DESC';
					$result_advance = execute_query($sql_advance);
					echo '<td colspan="2">&nbsp;</td><td>On Previous Booking</td><td><select name="advance_for" id="advance_for">';
					while ($row_advance = mysqli_fetch_array($result_advance)) {
						$pur = '';
						if ($row_advance['purpose'] == "room_rent") {
							$pur = "Room Rent";
						} elseif ($row_advance['purpose'] == "banquet_rent") {
							$pur = "Banquet Rent";
						}
						echo '<option value="' . $row_advance['sno'] . '"';
						if ($row_edit['advance_for_id'] == $row_advance['sno']) {
							echo 'selected';
						}
						echo '>' . $row_advance['allotment_date'] . '-' . $pur . '</option>';
					}
					echo '</select></td>';
				} else {
					echo '<td colspan="4">&nbsp;</td>';
				}
				?>

			</tr>
			<tr id="advance_item_checkin" style="display: none;"></tr>
			<tr>
				<td>Mode of Payment : </td>
				<td>
					<select id="mop" name="mop" class="field select medium" tabindex="<?php echo $tab++; ?>">
						<option value="cash" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'cash') { ?>
									selected="selected" <?php }
						} ?>>Cash</option>
						<option value="card" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'card') { ?>
									selected="selected" <?php }
						} ?>>Card</option>
						<option value="other" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'other') { ?>
									selected="selected" <?php }
						} ?>>Other</option>
						<option value="bank_transfer" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'bank_transfer') { ?> selected="selected" <?php }
						} ?>>Bank Transfer
						</option>
						<option value="cheque" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'cheque') { ?>
									selected="selected" <?php }
						} ?>>Cheque</option>
						<option value="paytm" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'paytm') { ?>
									selected="selected" <?php }
						} ?>>Paytm</option>
						<option value="card_sbi" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'card_sbi') { ?>
									selected="selected" <?php }
						} ?>>Card S.B.I</option>
						<option value="card_pnb" <?php if (isset($_GET['id'])) {
							if ($row_trans['mop'] == 'cheque') { ?>
									selected="selected" <?php }
						} ?>>Card P.N.B.</option>
					</select>
				</td>
				<td>Total Amount : </td>
				<td>
					<input id="total_amount" name="total_amount"
						value="<?php echo isset($_GET['e_id']) ? htmlspecialchars($row_edit['total_amount']) : ''; ?>"
						class="field text medium" maxlength="255" type="text" readonly />
				</td>

			</tr>
			<tr>
				<td>Advance Amount : </td>
				<td><input id="advance_amount" name="advance_amount" value="<?php if (isset($_GET['e_id'])) {
					echo $row_edit['advance_amount'];
				} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text" required
						onBlur="calc_due(this.value);" /></td>
				<td>Due Amount : </td>
				<td><input id="due_amount" name="due_amount" value="<?php if (isset($_GET['e_id'])) {
					echo $row_edit['due_amount'];
				} ?>" class="field text medium" maxlength="255" tabindex="<?php echo $tab++; ?>" type="text"
						onBlur="calc_due(this.value);" /></td>
			</tr>
			<table id="attachmentTable">
    <tr>
        <td colspan="4">
            <button type="button" class="btn btn-primary" onclick="addAttachmentRow()">Add Attachment +</button>
        </td>
    </tr>

    <?php if (!empty($attachments)) { 
        foreach ($attachments as $key => $attachment) { ?>
        <tr>
            <td>Upload File</td>
            <td>
                <input type="file" name="file_path[]" class="file_input">
                <input type="hidden" name="existing_file[]" value="<?php echo $attachment['file_path']; ?>">
                <span><?php echo basename($attachment['file_path']); ?></span>
            </td>
            <td>Description</td>
            <td>
                <input type="text" name="description[]" value="<?php echo htmlspecialchars($attachment['description']); ?>">
                <button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button>
            </td>
        </tr>
    <?php } } ?>
       
</table>
			<table>
				<tr style="background:#000; color:#FFF;">
					<th style="width: 310px;">Room Category</th>
					<th style="width: 200px;">Remains Room</th>
					<th>Number of Room</th>
					<th>Room Tariff</th>
					<th>Room Number</th>
					<th>Action</th>
				</tr>
			</table>
			<table id="roomTable">
				<?php
				// Ensure at least one row is present for normal form filling
				$rowCount = isset($editData['cat_id']) && is_array($editData['cat_id']) ? count($editData['cat_id']) : 1;

				for ($i = 0; $i < $rowCount; $i++) { ?>
					<tr>
						<td style="width: 365px;">
							<select name="cat[]" class="field select medium" onchange="fetchRemainingRooms(this)"
								data-mode="<?php echo isset($_GET['e_id']) ? 'edit' : 'normal'; ?>">
								<option value="">-- Select Room Category --</option>
								<?php
								$query = "SELECT sno, room_type FROM category";
								$result = execute_query($query);

								while ($row = mysqli_fetch_assoc($result)) {
									$selected = (isset($editData['cat_id'][$i]) && $row['sno'] == $editData['cat_id'][$i]) ? 'selected' : '';
									echo "<option value='{$row['sno']}' $selected>{$row['room_type']}</option>";
								}
								?>
							</select>
						</td>
						<td style="width: 180px;">
							<input type="text" name="rem_room[]" class="rem_room"
								style="width:150px;margin-left: 20px;background:yellow;" disabled>
						</td>
						<td>
							<input name="number_of_room[]" class="field text medium number_of_room" maxlength="200"
								type="text" style="width:220px;"
								value="<?php echo isset($editData['number_of_room'][$i]) ? htmlspecialchars($editData['number_of_room'][$i]) : ''; ?>" />
						</td>
						<td>
							<input name="room_tariff[]" class="field text medium" maxlength="255" type="text"
								style="width:170px;"
								value="<?php echo isset($editData['room_tariff'][$i]) ? htmlspecialchars($editData['room_tariff'][$i]) : ''; ?>" />
						</td>
						<td>
							<input name="room_number[]" class="field text medium" maxlength="255" type="text"
								style="width:200px;"
								value="<?php echo isset($editData['room_number'][$i]) ? htmlspecialchars($editData['room_number'][$i]) : ''; ?>" />
						</td>
						<td style="width: 150px;">
							<button class="btn btn-success" type="button" onclick="addRow()">Add</button>
						</td>
					</tr>
				<?php } ?>
			</table>



			<?php
			if (isset($_GET['aid'])) {
				?>
				<tr>
					<td>Exit Date : </td>
					<td><input name="exit_date" type="text" value="<?php if (isset($old_data['exit_date'])) {
						echo $old_data['exit_date'];
					} ?>" class="field text medium" tabindex="<?php echo $tab++; ?>" id="exit_date" /></td>
					<td></td>
					<td></td>
				</tr>
				<?php
			}
			?>
			<input type="hidden" name="edit_room_sno" id="" value='<?php
			$cut_discount = 'select * from allotment where sno="' . $_GET['id'] . '"';
			$cut_dis_run = execute_query($cut_discount);
			$cut_row = mysqli_fetch_array($cut_dis_run);
			echo $cut_row['other_discount'];
			?>'>

			<tr>
				<td colspan="4" id="insertrow"></td>
			</tr>
			<tr>
				<td colspan="4"><input type="hidden" name="edit_sno" id="edit_sno" value="<?php if (isset($_GET['e_id'])) {
					echo $_GET['e_id'];
				} ?>" />
					<input style="margin-left: 45%;" id="submit" name="submit" class="btTxt submit large" type="submit"
						value="Done" onMouseDown="" tabindex="<?php echo $tab++; ?>">
				</td><br>
				<input type="hidden" name="flage" id="" value="<?php if (isset($_GET['f'])) {
					echo $_GET['f'];
				} ?>" />
				<input type="hidden" name="allot2_sno" value="<?php if (isset($_GET['aid'])) {
					echo $_GET['aid'];
				} ?>" />
			</tr>

		</table>
	</form>
	
</div>
<script src="js/jquery.datetimepicker.full.js"></script>
<script language="JavaScript">
	$('#allotment_date').datetimepicker({
		step: 15,
		format: 'Y-m-d H:i',
		value: '<?php
		if (isset($_POST['date_from'])) {
			echo $_POST['date_from'];
		} elseif (isset($_GET['e_id'])) {
			echo $row_edit['allotment_date'];
		} else {
			echo date("Y-m-d H:i");
		}
		?>',
	});

	$('#check_in').datetimepicker({
		step: 15,
		format: 'Y-m-d H:i',
		value: "<?php
		if (isset($_POST['date_from'])) {
			echo $_POST['date_from'];
		} elseif (isset($_GET['e_id']) && !empty($row_edit['check_in'])) {
			echo date("Y-m-d H:i", strtotime($row_edit['check_in']));
		} else {
			echo date("Y-m-d H:i");
		}
		?>",
	});

	$('#check_out').datetimepicker({
		step: 15,
		format: 'Y-m-d H:i',
		value: "<?php
		if (isset($_POST['date_from'])) {
			echo $_POST['date_from'];
		} elseif (isset($_GET['e_id']) && !empty($row_edit['check_out'])) {
			echo date("Y-m-d H:i", strtotime($row_edit['check_out']));
		} else {
			echo date("Y-m-d H:i");
		}
		?>",
	});


	$('#exit_date').datetimepicker({
		step: 15,
		format: 'Y-m-d H:i',
		value: '<?php
		if (isset($_GET['id'])) {
			echo $old_data['exit_date'];
		} else {
			echo date("Y-m-d H:i");
		}
		?>',
	});

	$('select[multiple]').multiselect({
		columns: 1,
		placeholder: 'Select options'
	});

	/**$(document).ready(function(){
		get_room_rent();
	});**/
	$(document).ready(function () {
		get_advance_info();
	});
</script>


<script>
	document.addEventListener("DOMContentLoaded", function () {
		document.querySelectorAll("select[name='cat[]']").forEach(function (selectElement) {
			var mode = selectElement.getAttribute("data-mode"); // Get mode (edit or normal)

			if (mode === "edit" && selectElement.value !== "") {
				fetchRemainingRooms(selectElement); // Auto-load for edit mode
			}
		});
	});

	function fetchRemainingRooms(selectElement) {
		var roomsno = selectElement.value;
		var row = selectElement.closest('tr'); // Get the parent row
		var remRoomInput = row.querySelector(".rem_room"); // Find the rem_room input in this row
		var checkInDate = document.getElementById("check_in") ? document.getElementById("check_in").value : ''; // Get the check-in date

		if (roomsno === "" || checkInDate === "") {
			remRoomInput.value = "";
			return;
		}

		var xhr = new XMLHttpRequest();
		xhr.open("GET", "fetch_remaining_rooms.php?sno=" + encodeURIComponent(roomsno) + "&check_in=" + encodeURIComponent(checkInDate), true);
		xhr.onreadystatechange = function () {
			if (xhr.readyState == 4 && xhr.status == 200) {
				remRoomInput.value = xhr.responseText;
			}
		};
		xhr.send();
	}


	function addRow() {
		var table = document.getElementById("roomTable");
		var newRow = table.rows[0].cloneNode(true); // Clone first row

		// Reset values for new row
		newRow.querySelector("select").value = "";
		newRow.querySelector(".rem_room").value = "";
		newRow.querySelector("input[name='number_of_room[]']").value = "";
		newRow.querySelector("input[name='room_number[]']").value = "";
		newRow.querySelector("input[name='room_tariff[]']").value = "";

		// Change Add button to Remove
		var btn = newRow.querySelector("button");
		btn.innerText = "Remove";
		btn.style.backgroundColor = "red";
		btn.style.color = "white";
		btn.style.border = "none";
		btn.style.padding = "5px 10px";
		btn.style.borderRadius = "5px";
		btn.style.cursor = "pointer";
		btn.style.fontWeight = "bold";
		btn.style.marginLeft = "10px";
		btn.setAttribute("onclick", "removeRow(this)");

		table.appendChild(newRow);
	}


	function removeRow(button) {
		var row = button.closest("tr");
		var table = document.getElementById("roomTable");

		if (table.rows.length > 1) {
			row.remove(); // Remove row
		} else {
			alert("At least one row is required!");
		}
	}
</script>

<script>
	// Add event listener to validate number of rooms
	document.addEventListener("input", function (event) {
		if (event.target.classList.contains("number_of_room")) {
			var row = event.target.closest('tr'); // Get the parent row
			var remRoomInput = row.querySelector(".rem_room"); // Get remaining rooms input
			var remRooms = parseInt(remRoomInput.value) || 0; // Get remaining rooms as an integer
			var enteredRooms = parseInt(event.target.value) || 0; // Get entered rooms as an integer

			if (enteredRooms > remRooms) {
				alert("Number of rooms cannot be greater than remaining rooms.");
				event.target.value = ""; // Clear input value
			}
		}
	});
</script>

<script>
	function calculateTotalAmount() {
		let totalAmount = 0;
		let rows = document.querySelectorAll("#roomTable tr");

		rows.forEach(row => {
			let numberOfRooms = row.querySelector(".number_of_room") ? parseFloat(row.querySelector(".number_of_room").value) || 0 : 0;
			let roomTariff = row.querySelector("[name='room_tariff[]']") ? parseFloat(row.querySelector("[name='room_tariff[]']").value) || 0 : 0;

			let rowTotal = numberOfRooms * roomTariff;
			totalAmount += rowTotal;
		});

		// Update total amount field
		document.getElementById("total_amount").value = totalAmount.toFixed(2);
	}

	// Attach event listener to update total on input change
	document.addEventListener("input", function (event) {
		if (event.target.matches(".number_of_room") || event.target.matches("[name='room_tariff[]']")) {
			calculateTotalAmount();
		}
	});
</script>


<script>
	function addAttachmentRow() {
    let table = document.getElementById("attachmentTable");
    let row = document.createElement("tr");
    row.innerHTML = `
        <td>Upload File</td>
        <td><input type="file" name="file_path[]" class="file_input"></td>
        <td>Description</td>
        <td>
            <input type="text" name="description[]" value="">
            <button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button>
        </td>
    `;
    table.appendChild(row);
}

function removeRow(button) {
    button.closest("tr").remove();
}
</script>
<?php
navigation('');
page_footer();
?>