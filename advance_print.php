<?php
date_default_timezone_set('Asia/Calcutta');
include("scripts/settings.php");
$sql = 'select * from general_settings where `desc`="company"';
$company = mysqli_fetch_assoc(execute_query($sql));
$company = $company['rate'];

$sql = 'select * from general_settings where `desc`="slogan"';
$slogan = mysqli_fetch_assoc(execute_query($sql));
$slogan = $slogan['rate'];

$sql = 'select * from general_settings where `desc`="dealer"';
$dealer = mysqli_fetch_assoc(execute_query($sql));
$dealer = $dealer['rate'];

$sql = 'select * from general_settings where `desc`="address"';
$address = mysqli_fetch_assoc(execute_query($sql));
$address = $address['rate'];

$sql = 'select * from general_settings where `desc`="contact"';
$contact = mysqli_fetch_assoc(execute_query($sql));
$contact = $contact['rate'];

$sql = 'select * from general_settings where `desc`="gstin"';
$gstin = mysqli_fetch_assoc(execute_query($sql));
$gstin = $gstin['rate'];

$sql = 'select * from general_settings where `desc`="pan"';
$pan = mysqli_fetch_assoc(execute_query($sql));
$pan = $pan['rate'];

$sql = 'select * from general_settings where `desc`="invoice_prefix"';
$invoice_prefix = mysqli_fetch_assoc(execute_query($sql));
$invoice_prefix = $invoice_prefix['rate'];

$sql = 'select * from general_settings where `desc`="firm_type"';
$firm_type = mysqli_fetch_assoc(execute_query($sql));
$firm_type = $firm_type['rate'];

$sql = 'select * from general_settings where `desc`="bill_style"';
$bill_style = mysqli_fetch_assoc(execute_query($sql));
$bill_style = $bill_style['rate'];

$sql = 'select * from general_settings where `desc`="terms"';
$terms = mysqli_fetch_assoc(execute_query($sql));
$terms = $terms['rate'];

$sql = 'select * from general_settings where `desc`="bank"';
$bank = mysqli_fetch_assoc(execute_query($sql));
$bank = $bank['rate'];

$sql = 'select * from general_settings where `desc`="jurisdiction"';
$jurisdiction = mysqli_fetch_assoc(execute_query($sql));
$jurisdiction = $jurisdiction['rate'];

$sql = 'select * from general_settings where `desc`="software_type"';
$software_type = mysqli_fetch_assoc(execute_query($sql));
$software_type = $software_type['rate'];

$sql = 'select * from general_settings where `desc`="Print Table No On Bill"';
$tabl = mysqli_fetch_assoc(execute_query($sql));
$tableno = $tabl['rate'];

$sql_invoice = "SELECT 
    ab.sno AS id,
    ab.remarks AS remark,
    ab.*, 
    c.*
FROM 
    advance_booking ab
JOIN 
    (
        SELECT sno, cat_id, SUBSTRING_INDEX(SUBSTRING_INDEX(cat_id, ',', n.n), ',', -1) AS category_id
        FROM advance_booking
        JOIN (
            SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
        ) n 
        ON CHAR_LENGTH(cat_id) - CHAR_LENGTH(REPLACE(cat_id, ',', '')) >= n.n - 1
    ) expanded_cat
ON ab.sno = expanded_cat.sno
JOIN category c 
ON c.sno = expanded_cat.category_id
WHERE ab.sno = '" . $_GET['print_id'] . "';


";
$invoice = mysqli_fetch_assoc(execute_query($sql_invoice));

$sql_cust = 'SELECT * FROM `customer` WHERE `sno`="' . $invoice['cust_id'] . '"';
$cust = mysqli_fetch_array(execute_query($sql_cust));
$sql_mop = 'SELECT * FROM `customer_transactions` WHERE `advance_booking_id`="' . $_GET['print_id'] . '" ';
$row_mop = mysqli_fetch_array(execute_query($sql_mop));
$style = 'thermal';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Advance Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
           
            .receipt-container {
                width: 100%;
                max-width: 100%;
                padding: 10px;
                margin: 0;
                box-shadow: none;
                page-break-after: avoid;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #fff;
            padding: 10px;
            margin: 0;
        }

        .receipt-container {
            width: 100%;
            max-width: 950px;
            background: #fff;
            margin: auto;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }

        .header-text {
            text-align: center;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .header-text h3, .header-text h6 {
            margin: 3px 0;
        }

        .info-table th, .info-table td {
            font-size: 11px;
            padding: 4px;
        }

        .terms {
            font-size: 12px;
            margin-top: 5px;
        }

        .terms ul {
            padding-left: 15px;
            margin-bottom: 5px;
        }

        .signature {
            text-align: right;
            font-weight: bold;
            margin-top: 32px;
        }

        .bank-details, .amount-table {
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="updiv d-flex align-items-center">
            <img src="images/a2.png" class="company-logo me-3" height="120px" width="120px">
            <div class="header-text">
                <h6>ADVANCE RECEIPT</h6>
                <h3>HOTEL RAJ PALACE</h3>
                <h6>Deokali-Fatehganj Road, Wazirganj Japti, Ayodhya-224001 (U.P)</h6>
                <h6>Receptation: 05278-316015 | +91 9335452112</h6>
                <h6>Reservation: +91 7755004900</h6>
                <h6>Email: hotelrajpalace.biz@gmail.com | Website: www.hotelrajpalace.biz</h6>
                <h6><strong>GSTIN:</strong> 09CUYPS5983A2ZP</h6>
            </div>
        </div>

        <table class="table table-bordered info-table">
            <tr>
                <th>Booking Id:</th>
                <td><?php echo $invoice['booking_id']; ?></td>
                <!-- <th>Receipt No:</th>
                <td><?php echo $invoice['id']; ?></td> -->
                <th>Guest Name:</th>
                <td><?php echo $cust['cust_name']; ?></td>
            </tr>
            <tr>
                <th>Company Name:</th>
                <td><?php echo $cust['company_name']; ?></td>
                <th>Mobile:</th>
                <td><?php echo $cust['mobile']; ?></td>
                
                
            </tr>
            
            <tr>
                <th>GSTIN No:</th>
                <td><?php echo $cust['id_2']; ?></td>
                <th>Date:</th>
                <td><?php echo date("d-m-Y", strtotime($invoice['allotment_date'])); ?></td>
            </tr>
            <tr>
                <th>Check In:</th>
                <td><?php echo date('d-m-Y h:i A', strtotime($invoice['check_in'])); ?></td>
                <th>Check Out:</th>
                <td><?php echo date('d-m-Y h:i A', strtotime($invoice['check_out'])); ?></td>
               
            </tr>
            <tr>
                <th>Meal Plan:</th>
                <td><?php echo $invoice['kitchen_dining']; ?></td>
                <th>Amount:</th>
                <td><?php echo number_format($invoice['kitchen_amount'], 2, '.', ''); ?></td>
            </tr>
            <tr>
                <th>Payment Mode:</th>
                <td><?php echo strtoupper($row_mop['mop']); ?></td>
                <th>Total Amount:</th>
                <td><?php
                $tm=number_format($invoice['total_amount'], 2, '.', '')+number_format($invoice['kitchen_amount'], 2, '.', '');
                echo number_format($tm,2,'.',''); ?></td>
            </tr>
            <tr>
                <th>Advance Amount:</th>
                <td><?php echo number_format($invoice['advance_amount'], 2, '.', ''); ?></td>
                <th>Due Amount:</th>
                <td><?php echo number_format($tm-$invoice['advance_amount'], 2, '.', ''); ?></td>
            </tr>
            <tr>
            <th>Remarks</th>
            <td colspan="3"><?php echo $invoice['remark']; ?></td>
           
            </tr>

        </table>
        <table class="table table-bordered mt-3">
    <tr>
        <th>Room Category</th>
        <th>Rooms</th>
        <th>Days</th>
        <th>Tariff</th>
        <th>Total Amount</th>
        <th>Occupency</th>
    </tr>
    <?php
    $result = mysqli_query($db, $sql_invoice);
    
    // Splitting the comma-separated values into arrays
    $roomNumbers = explode(',', $invoice['number_of_room']);
    $roomDays = explode(',', $invoice['number_of_days']);
    $roomTariffs = explode(',', $invoice['room_tariff']);
    $roomNumberes = explode(',', $invoice['room_number']);
    $roomTypes = []; // Array to store room types

    while ($row = mysqli_fetch_assoc($result)) {
        $roomTypes[] = $row['room_type'];
    }

    $totalRooms = 0;
    $totalDays = 0;
    $totalRoomsTariff = 0;
    $grandTotalAmount = 0;

    // Loop through each room category
    for ($i = 0; $i < count($roomNumbers); $i++) {
        $roomCount = intval($roomNumbers[$i]);
        $roomsCount = $roomNumberes[$i];
        $daysCount = intval($roomDays[$i]);
        $tariff = intval($roomTariffs[$i]);
        
        $totalAmount = ($roomCount * $tariff) * $daysCount;

        // Accumulate totals
        $totalRooms += $roomCount;
        $totalDays += $daysCount;
        $totalRoomsTariff += $tariff;
        $grandTotalAmount += $totalAmount;
        ?>
        <tr>
            <td><?php echo isset($roomTypes[$i]) ? $roomTypes[$i] : ''; ?></td>
            <td><?php echo $roomCount; ?></td>
            <td><?php echo $daysCount; ?></td>
            <td><?php echo $tariff; ?></td>
            <td><?php echo $totalAmount; ?></td>
            <td><?php echo $roomsCount; ?></td>
        </tr>
    <?php } ?>

    <tr>
        <th>Total Rooms:</th>
        <th><?php echo $totalRooms; ?></th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th><?php echo $grandTotalAmount; ?></th>
        <th>&nbsp;</th>
    </tr>
</table>
<div class="terms">
            <strong>Terms & Conditions:</strong>
            <ul>
                <li>No Gathering & Party in the room.</li>
                <li>Room can be shifted to another hotel in case of emergency.</li>
                <li>All Standard rooms will be booked, and Triple with extra mattress will be applicable.</li>
                <li>Remaining payment should be made as per the minimum number of room bookings before guest check-in.
                </li>
                <li>This tariff is valid for Bulk Room Booking (Minimum 8 Rooms) and not valid for FIT Rooms.</li>
                <li>Only one room tariff is free in a day for all groups, and food charges will be applicable. One room
                    will be free after a minimum of 15 rooms are booked.</li>
                <li>Depends on Room Tariff: Free Breakfast (07:30 AM to 10:30 AM), Dinner (08:00 PM to 10:00 PM) as per
                    room tariff. No alternate timing is applicable.</li>
                <li>Check-in Time: 02:00 PM | Check-out Time: 11:00 AM (Late check-out charges will be applicable).</li>
                <li>Confirmed room bookings will be considered; otherwise, cancellation charges will be applicable.</li>
                <li>If advance payment has not been made, then the booking can be canceled without making a call.</li>

            </ul>
        </div>

        <div class="mt-3 bank-details" style="width:70%">
            <h6><strong>Bank Details</strong></h6>
            <table class="table table-bordered">
                <tr>
                    <td><strong>Bank Name:</strong> PUNJAB NATIONAL BANK</td>
                    <td><strong>A/C No.:</strong> 0166002101033809</td>
                </tr>
                <tr>
                    <td><strong>IFSC Code:</strong> PUNB0016600</td>
                    <td><strong>Swift Code:</strong> PUNBINBBISB</td>
                </tr>
            </table>
        </div>

        <div class="signature">Authorized Signature</div>
    </div>
</body>

</html>


