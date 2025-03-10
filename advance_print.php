<?php 
date_default_timezone_set('Asia/Calcutta');
include ("scripts/settings.php"); 
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


$sql_invoice = 'SELECT * FROM advance_booking 
JOIN category ON FIND_IN_SET(category.sno, advance_booking.cat_id) 
WHERE advance_booking.sno = "'.$_GET['print_id'].'"';
$invoice=mysqli_fetch_assoc(execute_query($sql_invoice));
$sql_cust = 'SELECT * FROM `customer` WHERE `sno`="'.$invoice['cust_id'].'"';
$cust = mysqli_fetch_array(execute_query($sql_cust));
$sql_mop = 'SELECT * FROM `customer_transactions` WHERE `advance_booking_id`="'.$invoice['sno'].'" ';
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
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .receipt-container {
            width: 750px;
            background: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }
        .header-text {
            text-align: center;
            margin-bottom: 10px;
        }
        .header-text h5 {
            font-weight: bold;
            text-decoration: underline;
            font-size: 18px;
            color: #333;
        }
        .header-text h6 {
            font-size: 16px;
            color: #555;
        }
        .header-text p {
            font-size: 12px;
            margin-bottom: 3px;
            color: #666;
        }
        .company-logo {
            display: block;
            margin: 0 auto 10px;
        }
        .info-table th {
            background-color: #f8f9fa;
            text-align: left;
            font-size: 13px;
            color: #444;
        }
        .info-table td {
            font-size: 13px;
        }
        .amount-table th, .amount-table td {
            border: 1px solid #333;
            text-align: center;
            font-size: 13px;
            padding: 6px;
        }
        .terms {
            font-size: 12px;
            margin-top: 10px;
            color: #555;
        }
        .terms ul {
            padding-left: 18px;
        }
        .signature {
            text-align: right;
            font-weight: bold;
            margin-top: 15px;
        }
        /* Print Optimization */
        @media print {
            body {
                background: none;
            }
            .receipt-container {
                width: 100%;
                box-shadow: none;
                padding: 10px;
            }
            .info-table th, .info-table td, .amount-table th, .amount-table td {
                font-size: 14px;
                padding: 5px;
            }
            .header-text h5 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <img src="images/a2.png" class="company-logo" height="70px" width="70px">
        <div class="header-text">
            <h5>ADVANCE RECEIPT</h5>
            <h6>HOTEL RAJ PALACE</h6>
            <p>Deokali-Fatehganj Road, Wazirganj Japti, Ayodhya-224001 (U.P)</p>
            <p>Contact: 05278-316015 | +91 9335452112 | +91 7755004900</p>
            <p>Email: hotelrajpalace.biz@gmail.com | Website: www.hotelrajpalace.biz</p>
            <p><strong>GSTIN:</strong> 09CUYPS5983A2ZP</p>
        </div>

        <table class="table table-bordered info-table">
            <tr>
                <th>Guest Name:</th>
                <td><?php echo $cust['cust_name']; ?></td>
                <th>Receipt No.:</th>
                <td><?php echo $invoice['sno']; ?></td>
            </tr>
            <tr>
                <th>Company Name:</th>
                <td><?php echo $cust['company_name']; ?></td>
                <th>Date:</th>
                <td><?php echo date("d-m-Y", strtotime($invoice['allotment_date'])); ?></td>
            </tr>
            <tr>
                <th>GSTIN No:</th>
                <td><?php echo $cust['id_2']; ?></td>
                <th>Check In:</th>
                <td><?php echo date('d-m-Y h:i A', strtotime($invoice['check_in'])); ?></td>
            </tr>
            <tr>
                <th>Check Out:</th>
                <td><?php echo date('d-m-Y h:i A', strtotime($invoice['check_out'])); ?></td>
                <th>Mobile:</th>
                <td><?php echo $cust['mobile']; ?></td>
            </tr>
            <tr>
                <th>Kitchen Dining:</th>
                <td><?php echo $invoice['kitchen_dining']; ?></td>
                <th>Amount:</th>
                <td><?php echo $invoice['kitchen_amount']; ?></td>
            </tr>
            <tr>
                <th>Payment Mode:</th>
                <td><?php echo strtoupper($row_mop['mop']); ?></td>
                <th>Total Amount:</th>
                <td><?php echo $invoice['total_amount']; ?></td>
            </tr>
            <tr>
                <th>Advance Amount:</th>
                <td><?php echo $invoice['advance_amount']; ?></td>
                <th>Due Amount:</th>
                <td><?php echo $invoice['due_amount']; ?></td>
            </tr>
        </table>

        <table class="table table-bordered mt-3">
            <tr>
                <th>Room Category</th>
                <th>No. Of Rooms</th>
                <th>Room Number</th>
            </tr>
            <tr>
            <td>
                    <?php 
                    $result = mysqli_query($db, $sql_invoice);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo $row['room_type'] . "<br>"; 
                    } 
                    ?>
                </td>
                <?php if($invoice['number_of_room'] != ''){ ?>
                
                <td> <?php echo str_replace(',', '<br>', $invoice['number_of_room']); ?></td>
                <?php } ?>
                <?php if($invoice['room_number'] != ''){ ?>
                
                <td><?php echo str_replace(',', '<br>', $invoice['room_number']); ?></td>
                <?php } ?>
            </tr>
        </table>

        <div class="terms">
            <strong>Terms & Conditions:</strong>
            <ul>
                <li>Check-in Time: 13:00 HRS</li>
                <li>Check-out Time: 12:00 NOON</li>
                <li>Advance payments are non-refundable</li>
            </ul>
        </div>

        <div class="signature">
            Authorized Signature
        </div>
    </div>
</body>
</html>



