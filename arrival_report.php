<?php
session_cache_limiter('nocache');
include("scripts/settings.php");
page_header();
logvalidate($_SESSION['username'], $_SERVER['SCRIPT_FILENAME']);
$response = 1;
$msg = '';
date_default_timezone_set('Asia/Calcutta');
if (isset($_GET['cancel_id'])) {
	$cancel_id = intval($_GET['cancel_id']); // Ensure security
	$sql = "UPDATE customer_transactions SET `type` = 'ADVANCE_AMT_CANCEL' WHERE sno = $cancel_id";
	$result = execute_query($sql);

	$sql = "UPDATE advance_booking 
JOIN customer_transactions ON advance_booking.cust_id = customer_transactions.cust_id
SET advance_booking.status = 1
WHERE customer_transactions.sno=$cancel_id";
	$result = execute_query($sql);
}

if (isset($_GET['uncancel_id'])) {
	$uncancel_id = intval($_GET['uncancel_id']); // Ensure security
	$sql = "UPDATE customer_transactions SET `type` = 'ADVANCE_AMT' WHERE sno = $uncancel_id";
	$result = execute_query($sql);

	$sql = "UPDATE advance_booking 
JOIN customer_transactions ON advance_booking.cust_id = customer_transactions.cust_id
SET advance_booking.status = 0
WHERE customer_transactions.sno=$uncancel_id";
	$result = execute_query($sql);

}

?>
<style>
	.ui-autocomplete-loading {
		background: white url('images/ui-anim_basic_16x16.gif') right center no-repeat;
	}
</style>
<script type="text/javascript" language="javascript">
	$(function () {
		var options = {
			source: function (request, response) {
				$.getJSON("scripts/ajax.php?id=cust_name1", request, response);
			},
			minLength: 1,
			select: function (event, ui) {
				log(ui.item ?
					"Selected: " + ui.item.value + " aka " + ui.item.label :
					"Nothing selected, input was " + this.value);
			},
			select: function (event, ui) {
				$("[name='cust_name']").val(ui.item.label);
				$('#cust_sno').val(ui.item.id);
				$("#ajax_loader").show();
				return false;
			}
		};
		$("input#cust_name").on("keydown.autocomplete", function () {
			$(this).autocomplete(options);
		});
	});
	$(function () {
		var options = {
			source: function (request, response) {
				$.getJSON("scripts/ajax.php?id=company_name", request, response);
			},
			minLength: 1,
			select: function (event, ui) {
				log(ui.item ?
					"Selected: " + ui.item.value + " aka " + ui.item.label :
					"Nothing selected, input was " + this.value);
			},
			select: function (event, ui) {
				$("[name='company_name']").val(ui.item.label);
				$('#cust_sno').val(ui.item.id);
				$("#ajax_loader").show();
				return false;
			}
		};
		$("input#company_name").on("keydown.autocomplete", function () {
			$(this).autocomplete(options);
		});
	});
</script>
<!--<div id="content" class="print-only">
	<div style="border:0px solid; margin:0 auto; text-align:center"><h1>Shane Avadh Hotel</h1>
		<h1>Civil Lines Faizabad, Ayodhya-224001<br/>Registration No. 02/476 J.A./Sarai Act/2003. CIN: U55101UP1980PTC005050</h1>
		<h1>GSTIN : 09AAHCS2262A1ZN</h1>
	</div>
</div>-->
<style>
	.table-like {
		display: flex;
		flex-direction: column;
		border: 1px solid #ddd;
		border-radius: 5px;
		overflow: hidden;
	}

	.table-header {
		display: flex;
		background: #00888d;
		color: white;
		font-weight: bold;
		padding: 10px;
	}

	.table-cell {
		flex: 1;
		text-align: center;
		padding: 10px;
		border-right: 1px solid #ddd;
	}

	.table-cell:last-child {
		border-right: none;
	}

	.table-row {
		display: flex;
		border-top: 1px solid #ddd;
		background: #f9f9f9;
	}

	.table-row:nth-child(even) {
		background: #f1f1f1;
	}
</style>
<div id="container">
	<h2>Today Arrival Report</h2>

	<table width="100%" class="table table-bordered">
		<tr>
			<th style="background:#00888d; color:#FFF;">S.No.</th>
			<th style="background:#00888d; color:#FFF;">Receipt No.</th>
			<th style="background:#00888d; color:#FFF;">Comapny Name</th>
			<th style="background:#00888d; color:#FFF;">Guest Name</th>
			<th style="background:#00888d; color:#FFF;">Mobile</th>
			<th style="background:#00888d; color:#FFF;">Meal Plan</th>
			<th style="background:#00888d; color:#FFF;">Amount</th>
			<!-- <th  style="background:#00888d; color:#FFF;">Date Of Entry</th> -->
			<th style="background:#00888d; color:#FFF;">Type</th>
			<th style="background:#00888d; color:#FFF;">MOP</th>
			<th style="background:#00888d; color:#FFF;">Total Amount</th>
			<th style="background:#00888d; color:#FFF;">Advance Amount</th>
			<th style="background:#00888d; color:#FFF;">Due Amount</th>
			<th style="background:#00888d; color:#FFF;">Booking Date</th>
			<th style="background:#00888d; color:#FFF;">Check In Date</th>
			<th style="background:#00888d; color:#FFF;">Check Out Date</th>
			<th style="background:#00888d; color:#FFF;">Attachment</th>
			<th style="background:#00888d; color:#FFF;">Room Category Details</th>
			<!-- <th  style="background:#00888d; color:#FFF;">No. Of Rooms</th>
					<th  style="background:#00888d; color:#FFF;">Room Number</th> -->
			<!-- <th  style="background:#00888d; color:#FFF;" class="no-print">Status</th> -->
			<th style="background:#00888d; color:#FFF;" class="no-print">Edit</th>
			<th style="background:#00888d; color:#FFF;" class="no-print">View</th>
			<th style="background:#00888d; color:#FFF;" class="no-print">Cancel</th>
		</tr>
		<?php
		$total_room = 0;
		$sql_mop = '';
		$attachments = [];
		$sql = 'SELECT * FROM advance_booking WHERE DATE(check_in) = "' . date("Y-m-d") . '"';

		//echo $sql;
		$result = execute_query($sql);
		while ($row = mysqli_fetch_array($result)) {
			$i = 1;
			$tot_advance = 0;  // Stores total advance amount
			$tot_total = 0;    // Stores total grand amount (total + kitchen)
			$tot_due = 0;      // Stores total due amount
			$total_room = 0;   // Stores total room count
		
			foreach ($result as $row) {
				$sql_mop = 'SELECT * FROM `customer_transactions` WHERE `advance_booking_id`="' . $row['sno'] . '" ';
				if (isset($_POST['submit_form'])) {
					if ($_POST['mop'] != '') {
						$sql_mop .= ' AND `mop`="' . $_POST['mop'] . '" ';
					}
					if ($_POST['cancel_status'] != '') {
						$sql_mop .= ' AND `type`="' . $_POST['cancel_status'] . '" ';
					}
				}
				$result_mop = execute_query($sql_mop);
				$col = ($i % 2 == 0) ? '#CCC' : '#EEE'; // Alternating row colors
		
				if (mysqli_num_rows($result_mop) != 0) {
					while ($row_mop = mysqli_fetch_array($result_mop)) {
						if ($row_mop['type'] == 'ADVANCE_AMT_CANCEL') {
							$col = '#dd4a4a'; // Red color for canceled
						}
					}
				} else {
					$row_mop = ['type' => '', 'sno' => '', 'mop' => ''];
				}

				$sql = 'SELECT * FROM customer WHERE sno=' . $row['cust_id'];
				$result = execute_query($sql);
				$details = mysqli_fetch_assoc($result);

				// **✅ Corrected Total Calculations**
				$tm = floatval($row['total_amount']) + floatval($row['kitchen_amount']);  // Calculate grand total
				$tot_advance += floatval($row['advance_amount']);  // Total advance amount
				$tot_total += $tm;  // Total of all grand amounts
				$tot_due += ($tm - $row['advance_amount']);  // Total due amount

				echo '<tr style="background:' . $col . '">
					<td style="background:' . $col . '">' . $i++ . '</td>
					<td style="background:' . $col . '">' . $row['sno'] . '</td>
					<td style="background:' . $col . '">' . $details['company_name'] . '</td>
					<td style="background:' . $col . '">' . $row['guest_name'] . '</td>
					<td style="background:' . $col . '">' . $details['mobile'] . '</td>
					<td style="background:' . $col . '">' . $row['kitchen_dining'] . '</td>
					<td style="background:' . $col . '">' . $row['kitchen_amount'] . '</td>';

				if ($row['purpose'] == "room_rent") {
					echo '<td style="background:' . $col . '">Room Booking</td>';
				} elseif ($row['purpose'] == "banquet_rent") {
					echo '<td style="background:' . $col . '">Banquet Booking</td>';
				} elseif ($row['purpose'] == "advance_for") {
					$sql_advance_for = 'SELECT * FROM `advance_booking` WHERE `sno`="' . $row['advance_for_id'] . '"';
					$result_advance_for = execute_query($sql_advance_for);
					$row_advance_for = mysqli_fetch_array($result_advance_for);
					if ($row_advance_for['purpose'] == "room_rent") {
						echo '<td style="background:' . $col . '">Room Booking(Plus Amount)</td>';
					} elseif ($row_advance_for['purpose'] == "banquet_rent") {
						echo '<td style="background:' . $col . '">Banquet Booking(Plus Amount)</td>';
					}
				} elseif ($row['purpose'] == "advance_for_checkin") {
					echo '<td style="background:' . $col . '">Room Booking(In House Guest)</td>';
				} else {
					echo '<td style="background:' . $col . '"></td>';
				}
				?>
				<?php
				$catIds = explode(',', $row['cat_id']); // Convert cat_id string to an array
				$roomTypes = [];

				foreach ($catIds as $catId) {
					$catQuery = 'SELECT room_type FROM category WHERE sno="' . trim($catId) . '"';
					$catRes = execute_query($catQuery);

					if ($catRow = mysqli_fetch_assoc($catRes)) {
						$roomTypes[] = $catRow['room_type']; // Store room type in array
					}
				}
				$roomTypeList = implode(', ', $roomTypes);
				$sql_mop = 'SELECT * FROM `customer_transactions` WHERE `advance_booking_id`="' . $row['sno'] . '" ';
				$result_mop = execute_query($sql_mop);
				$row_mop = mysqli_fetch_assoc($result_mop);

				$e_id = mysqli_real_escape_string($db, $row['sno']);
				$sql1 = "SELECT * FROM attachment WHERE advance_id = '$e_id'";
				$result1 = execute_query($sql1);
				while ($row1 = mysqli_fetch_assoc($result1)) {
					$attachments[] = $row1; // Store attachments in an array
				}
				$attachmentsJson = json_encode($attachments);
				?>
				<td style="background:<?php echo $col ?>" class="editable" id="row_<?php echo $row_mop['sno']; ?>">
					<?php if ($row_mop['mop'] == "bank_transfer") {
						echo 'BANK TRANSFER';
					} elseif ($row_mop['mop'] == "card_sbi") {
						echo 'CARD S.B.I.';
					} elseif ($row_mop['mop'] == "card_pnb") {
						echo 'CARD P.N.B.';
					} else {
						echo strtoupper($row_mop['mop']);
					} ?>
				</td>
				<td style="background:<?php echo $col ?>"><?php $tm=floatval($row['total_amount']) + floatval($row['kitchen_amount']); echo number_format($tm,2,'.',''); ?></td>
				<?php
				//if($row['status'] == 0 AND $row_mop['type'] == 'ADVANCE_AMT'){
				echo '<td style="background:' . $col . '" class="editable_amount" id="row_amount_' . $row['sno'] . '">' . $row['advance_amount'] . '</td>';
				/**}
										   else{
											   echo '<td>'.$row['advance_amount'].'</td>';
										   }**/
				
				$advanceAmount = isset($row['advance_amount']) ? floatval($row['advance_amount']) : 0;
				$tm = isset($tm) ? floatval($tm) : 0;

				echo '<td style="background:' . $col . '">' . ($tm - $advanceAmount) . '</td>';
				echo '<td style="background:' . $col . '">' . date('d-m-Y h:i:s', strtotime($row['allotment_date'])) . '</td>';
				echo '<td style="background:' . $col . '">' . date('d-m-Y h:i:s', strtotime($row['check_in'])) . '</td>';
				echo '<td style="background:' . $col . '">' . date('d-m-Y h:i:s', strtotime($row['check_out'])) . '</td>';
				echo '<td style="background: ' . $col . '">
    <button class="btn btn-link" style="color: #0D6EFD;" onclick="showAttachments(' . htmlspecialchars($attachmentsJson, ENT_QUOTES, "UTF-8") . ')">View</button>
</td>';


				echo '<td style="background:' . $col . '">
        <button class="btn btn-link" style="color: #0D6EFD;" onclick="showPopup(\'' .
					htmlspecialchars($roomTypeList) . '\', \'' .
					htmlspecialchars($row['number_of_room']) . '\', \'' .
					htmlspecialchars($row['room_number']) . '\')">
            View
        </button>
      </td>';
				$room_numbers = explode(',', $row['number_of_room']); // Convert "1,2,3" → ['1', '2', '3']
				$total_room += array_sum($room_numbers);
				?>
				<!-- Hidden Modal for Viewing Attachments -->
				<div id="attachmentModal" class="modal">
					<div class="modal-content">
						<span class="close" onclick="closeModal()">&times;</span>
						<h3 style="background:#00888D">Attachment Details</h3>
						<p id="attachmentDescription"></p>
						<iframe id="attachmentFrame" style="width:100%; height:400px;" frameborder="0"></iframe>
					</div>
				</div>
				<!-- Bootstrap Modal -->
				<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Category Room Details</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="table-like">
									<div class="table-header">
										<div class="table-cell">Room Category</div>
										<div class="table-cell">No. of Rooms</div>
										<div class="table-cell">Room Number</div>
									</div>
									<div id="popupRoomDetails"></div> <!-- Dynamic Data Will Be Inserted Here -->
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>



				<?php



				echo '<td style="background:' . $col . '" class="no-print"><a href="advance_booking.php?e_id=' . $row['sno'] . '" onclick="return confirm(\'Are you sure?\');">Edit</a></td>';

				echo '<td style="background:' . $col . '" class="no-print"><a href="advance_print.php?print_id=' . $row['sno'] . '"  target="_blank">View</a></td>';
				if ($row['status'] == 0 && $row_mop['type'] == 'ADVANCE_AMT') {
					echo '<td style="background:' . $col . '" class="no-print">
							<a href="report_advance.php?cancel_id=' . $row_mop['sno'] . '" 
							   onclick="return confirm(\'Are you sure you want to cancel this transaction?\');">
							   Cancel
							</a>
						  </td>';
				} elseif ($row_mop['type'] == 'ADVANCE_AMT_CANCEL') {
					echo '<td style="background:' . $col . '" class="no-print">
							<a href="report_advance.php?uncancel_id=' . $row_mop['sno'] . '" 
							   onclick="return confirm(\'Are you sure you want to revert the cancellation?\');">
							   Canceled
							</a>
						  </td>';
				}

				echo '</tr>';
			}
			$sql = "SELECT SUM(CAST(remarks AS UNSIGNED)) AS total_remarks FROM category";

			$result = $db->query($sql);
			$row = $result->fetch_assoc();
			$totalRoom = $row['total_remarks'] ?? 0;
			echo '<tr style="background:#00888d; color:#FFF;">
				    <th style="background:#00888d; color:#FFF;" colspan="9">Total :</th>
				    <th style="background:#00888d; color:#FFF;">' . ($tot_total ?? 0) . '</th>
				    <th style="background:#00888d; color:#FFF;">' . ($tot_advance ?? 0) . '</th>
				    <th style="background:#00888d; color:#FFF;">' . ($tot_due ?? 0). '</th>
				    <th style="background:#00888d; color:#FFF;"></th>
				    <th style="background:#00888d; color:#FFF;"></th>
				    <th style="background:#00888d; color:#FFF;"></th>
				    <th style="background:#00888d; color:#FFF;">Rooms: ' . ($total_room ?? 0) . '</th>
				    <th style="background:#00888d; color:#FFF;" colspan="4">Available Rooms: ' . ($totalRoom - ($total_room ?? 0)) . '</th>
				</tr>';
		}
		?>
	</table>
</div>
<script>
	$(function () {
		$("td.editable").dblclick(function (e) {
			var currentEle = $(this);
			var id = $(this).attr('id');
			var value = $(this).html();
			id = id.replace("row_", "");
			var txt = '<select name="mode_of_payment" id="mode_of_payment_' + id + '" class="small"><option value="cash" ';
			if (value == 'cash') {
				txt += ' selected="selected" ';
			}
			txt += '>CASH</option><option value="card" ';
			if (value == 'card') {
				txt += ' selected="selected" ';
			}
			txt += '>CARD</option><option value="other" ';
			if (value == 'other') {
				txt += ' selected="selected" ';
			} txt += '>Other</option><option value="paytm" ';
			if (value == 'PAYTM') {
				txt += ' selected="selected" ';
			} txt += '>PAYTM</option><option value="bank_transfer" ';
			if (value == 'bank_transfer') {
				txt += ' selected="selected" ';
			} txt += '>BANK TRANSFER</option><option value="cheque" ';
			if (value == 'cheque') {
				txt += ' selected="selected" ';
			} txt += '>CHEQUE</option><option value="card_sbi" ';
			if (value == 'card_sbi') {
				txt += ' selected="selected" ';
			} txt += '>Card S.B.I</option><option value="card_pnb" ';
			if (value == 'card_pnb') {
				txt += ' selected="selected" ';
			} txt += '>Card P.N.B.</option></select><br /><input type="button" value="Save" name="save_button" class="small" onClick="edit_mode_of_payment(' + id + ');">';
			$(this).html(txt);
		});
	});
	function edit_mode_of_payment(id) {
		//alert("#mode_of_payment_"+id);
		var mop = $("#mode_of_payment_" + id).val();
		$("#row_" + id).html('<img src="images/loading_transparent.gif">');
		$.ajax({
			async: false,
			url: "scripts/ajax.php?id=mop_room&term=" + id + "&mop=" + mop,
			dataType: "json"
		})
			.done(function (data) {
				data = data[0];
				if (data.result == 'true') {
					alert("Updated");
					if (mop == "bank_transfer") {
						mop = "BANK TRANSFER";
					}
					$("#row_" + id).html(mop);
				}
				else {
					alert("Failed. Retry.");
					var txt = '<select name="mode_of_payment" id="mode_of_payment_' + id + '" class="small"><option value="CASH" ';
					txt += '>CASH</option><option value="CARD" ';
					txt += '>CARD</option><option value="CREDIT" ';
					txt += '>CREDIT</option></option><option value="PAYTM" ';
					txt += '>PAYTM</option></option><option value="bank_transfer" ';
					txt += '>BANK TRANSFER</option><option value="cheque" ';
					txt += '>CHEQUE</option></select><br/><input type="button" value="Save" name="save_button" class="small" onClick="edit_mode_of_payment(' + id + ');">';
					$("#row_" + id).html(txt);
				}
			});

	}	
</script>
<script type="text/javascript">
	$(function () {
		$("td.editable_amount").dblclick(function (e) {
			var currentEle = $(this);
			var id = $(this).attr('id');
			var value = $(this).html();
			id = id.replace("row_amount_", "");
			var txt = '<input type="text" name="edit_amount" id="edit_amount_' + id + '" value="' + value + '" class="small"><br /><input type="button" value="Save" name="save_button" class="small" onClick="edit_amount(' + id + ');">';
			$(this).html(txt);
		});
	});
	function edit_amount(id) {
		//alert("#mode_of_payment_"+id);
		var amount = $("#edit_amount_" + id).val();
		$("#row_amount_" + id).html('<img src="images/loading_transparent.gif">');
		$.ajax({
			async: false,
			url: "scripts/ajax.php?id=advance_amount_edit&term=" + id + "&amount=" + amount,
			dataType: "json"
		})
			.done(function (data) {
				data = data[0];
				if (data.result == 'true') {
					alert("Updated");
					$("#row_amount_" + id).html(amount);
				}
				else {
					alert("Failed. Retry.");
					var txt = '<input type="text" name="edit_amount" id="edit_amount_' + id + '" value="' + amount + '" class="small"><br /><input type="button" value="Save" name="save_button" class="small" onClick="edit_amount(' + id + ');">';
					$("#row_amount_" + id).html(txt);
				}
			});

	}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
	function showPopup(roomType, numberOfRooms, roomNumber) {
		let detailsContainer = document.getElementById("popupRoomDetails");

		// Clear previous content
		detailsContainer.innerHTML = "";

		// Convert comma-separated values into arrays
		let roomTypes = roomType.split(",");
		let numRooms = numberOfRooms.split(",");
		let roomNumbers = roomNumber.split(",");

		// Loop through data and create rows dynamically
		for (let i = 0; i < roomTypes.length; i++) {
			let row = `<div class="table-row">
				<div class="table-cell">${roomTypes[i]}</div>
				<div class="table-cell">${numRooms[i] || '-'}</div>
				<div class="table-cell">${roomNumbers[i] || '-'}</div>
			</div>`;
			detailsContainer.innerHTML += row;
		}

		// Show Bootstrap Modal
		var modal = new bootstrap.Modal(document.getElementById("infoModal"));
		modal.show();
	}
</script>
<script>
	function showAttachments(attachments) {
		let content = '<h3 style="background:#00888D; color:white; padding:10px; border-radius:5px;">Attachments</h3><ul style="list-style:none; padding:0;">';

		if (attachments.length > 0) {
			attachments.forEach(att => {
				content += `<li style="margin:5px 0; padding:5px; border-bottom:1px solid #ccc;">
				<a href="${att.file_path}" target="_blank" style="color:#00888D; text-decoration:none; font-weight:bold;">${att.file_path}</a> - ${att.description}
			</li>`;
			});
		} else {
			content += "<li style='padding:10px;'>No attachments available</li>";
		}
		content += "</ul>";

		// Create overlay
		let overlay = document.createElement("div");
		overlay.id = "modalOverlay";
		overlay.style.position = "fixed";
		overlay.style.top = "0";
		overlay.style.left = "0";
		overlay.style.width = "100%";
		overlay.style.height = "100%";
		overlay.style.background = "rgba(0, 0, 0, 0.5)"; // Semi-transparent black
		overlay.style.zIndex = "999"; // Behind the modal

		// Create modal
		let modal = document.createElement("div");
		modal.style.position = "fixed";
		modal.style.top = "20%";
		modal.style.left = "35%";
		modal.style.width = "30%";
		modal.style.borderRadius = "10px";
		modal.style.background = "#eddfdf";
		modal.style.padding = "20px";
		modal.style.border = "1px solid #ccc";
		modal.style.zIndex = "1000"; // Above the overlay
		modal.style.boxShadow = "0px 4px 8px rgba(0, 0, 0, 0.2)";

		modal.innerHTML = `
		${content}
		<button class="btn btn-danger" onclick="closeModal()" style="display:block; width:100%; margin-top:10px; padding:10px; border:none; background:#d9534f; color:white; cursor:pointer; border-radius:5px;">Close</button>
	`;

		// Append elements to the body
		document.body.appendChild(overlay);
		document.body.appendChild(modal);

		// Function to close modal and remove overlay
		window.closeModal = function () {
			modal.remove();
			overlay.remove();
		};
	}

</script>
<?php
navigation('');
page_footer();
?>