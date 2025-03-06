<?php 
date_default_timezone_set('Asia/Calcutta');
session_cache_limiter('nocache');
include("scripts/settings.php");
logvalidate('','');
if(isset($_GET['id'])){
	$sql= 'select * from allotment where sno="'.$_GET['id'].'"';
	$details=mysqli_fetch_array(execute_query($sql));
	
	$sql = "select * from customer where sno=".$details['cust_id'];
	$customer=mysqli_fetch_array(execute_query($sql));
	if($details['exit_date']==''){
		$exit = 'exit_date is null';
	}
	else{
		$exit = 'exit_date="'.$details['exit_date'].'"';
	}
	
	if($details['exit_date']==''){
		$details['exit_date'] = date("Y-m-d H:i");
	}
	
	$days = get_days($details['allotment_date'], $details['exit_date']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/pop.css" TYPE="text/css" REL="stylesheet" media="all">
<style type="text/css">
@media print {
input#btnPrint {
display: none;
	}	
}
/*table, tr, td {font-size:14px; border:none; font-weight:bold;}*/
h3{ font-size:16px;}

</style>
<script language="javascript" type="text/javascript">
//window.print();
</script>
</head>
<body>

	<div class="no-print"><input type="button" id="btnPrint" onclick="window.print();" value="Print Page" />&nbsp;&nbsp;&nbsp;&nbsp;<a href="print_combined.php?id=<?php echo $_GET['id']; ?>">Print Combined Receipt</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="print_grouped.php?id=<?php echo $_GET['id']; ?>">Print Grouped Receipt</a></div>
<div id="wrapper" style="page-break-after:avoid;">

<div id="tablediv">
		<table width="100%" border="0" style="border-bottom: 1px solid;">
		    <img src="images/a2.png" height="170px;" width="250px;" style="margin-left:250px;" />
		<tr>
			<th colspan="3"><h3 style="text-decoration:underline;">
				<?php if($details['invoice_type']=='tax'){ ?>
				TAX INVOICE
				<?php } else{?>
				TAX INVOICE
				<?php } ?></h3></th>
		</tr>
		<tr>
			<th colspan="3"><h2>BEDI'S DREAM LAND HOTEL AND RESORT</h2></th>
		</tr>
		</tr>
		<tr>
			<th colspan="3"><h2>Maheshpur, Near Saryu Bridge </h2></th>
		</tr>
		<tr>
			<th colspan="3"><h3>Ayodhya-224001 (U.P)</h3></th>
		</tr>

		<tr>
			<th colspan="3"><h3>Contact No : +91 8989441919, +91 8400334035/34</h3></th>
		</tr>
		<tr>
			<th colspan="3"><h3>E-Mail : bedisdreamland@gmail.com, Website: www.bedisdreamland.com</h3></th>
		</tr>
		<tr>
			<th><center></center><h3>GSTIN :09AAOFB3645G1ZA</h3></center></th>
		</tr>
	</table>
</div>


<br>
	<table width="100%" style="border-bottom: 1px solid;">
		<tr>
			<td>Customer Name :</td>
			<td><?php echo $details['guest_name']; ?></td>
			<td>Customer GSTIN : </td>
			<td><?php echo $customer['id_2'];?></td>
		</tr>
		<tr>
			<td>Company Name :</td>
			<td><?php echo $customer['company_name']; ?></td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid;">Address :</td>
			<td style="border-bottom: 1px solid;"><?php if($customer['address'] != ''){ echo $customer['address']; }else{ echo $details['guest_address']; } ?></td>
			<td style="border-bottom: 1px solid;">ID Number : </td>
			<td style="border-bottom: 1px solid;"><?php echo $customer['id_1'];?></td>
		</tr>
		<tr>
			<td>Date :</td>
			<td><?php if($details['bill_create_date'] != ''){echo date("d-m-Y",strtotime($details['bill_create_date']));}else{echo date("d-m-Y");} ?></td>
		</tr>
		<tr>
			<td>Check In :</td>
			<td><?php echo date("d-m-Y H:i:s",strtotime($details['allotment_date'])); ?></td>
			<td>Check Out :</td>
			<td><?php echo date("d-m-Y H:i:s",strtotime($details['exit_date'])); ?></td>
		</tr>
	</table>
	<table width="100%" class="td-center">
	<?php if($details['discount'] !=0 && $rooms['other_charges']!=0){ ?>
		<tr>
			<th width="20%">Invoice No. and Days</th>
			<th width="30%">Room Name</th>
			<th width="8%">Room Rent</th>
			<th width="8%">Extra Bed</th>
			<th width="8%">Discount</th>
			<th width="8%">Taxable Rate</th>
			<th width="8%">CGST (6%)</th>
			<th width="8%">SGST (6%)</th>
			<th width="10%">Net Price</th>
		</tr>

		<?php
	}else{
		echo '<tr>
			<th width="20%">Invoice No. and Date</th>
			<th width="30%">Room Name</th>
			<th width="8%">Room Rent</th>
			
			<th width="8%">Taxable Rate</th>
			<th width="8%">CGST (6%)</th>
			<th width="8%">SGST (6%)</th>
			<th width="10%">Net Price</th>
		</tr>';

	}
		$sql = 'SELECT room_name, room_id, room_rent, discount,other_charges, discount_value, original_room_rent, invoice_type, financial_year, invoice_no FROM `allotment` join room_master on room_master.sno = allotment.room_id where cust_id="'.$details['cust_id'].'" and allotment_date="'.$details['allotment_date'].'" and '.$exit.' group by room_id';
		
		$rooms_result = execute_query($sql);
		$grand_total = 0;
		$tot_tax = 0;
		$tot=0;
		$taxable_tot=0;
		$tot_original_rent=0;
		$tot_extra_bed = 0;
		$tot_disc=0;
		
		$full_page = 290;
		$header_size = 90;
		$footer_size = 45;
		$row_height = 11;
		$page_size = $header_size + $footer_size + $row_height;
		$page = 1;
		 if($details['discount'] !=0){ 
			$colspan=9;
}
else{

		$colspan=7;
}								
		while($rooms = mysqli_fetch_array($rooms_result)){
			$sql_type = "select * from room_master where sno='".$rooms['room_id']."'";
			//echo $sql_type;
	        $sql_run=execute_query($sql_type);
	        $row_type=mysqli_fetch_array($sql_run); 
	        $start = date("d-m-Y H:i:s", strtotime($details['allotment_date']));
			$exit = $details['exit_date'];
			
				
				$remaining = $full_page - $page_size;
				if($remaining<0){

					$new = $full_page - $page_size + $footer_size;
					echo '<tr><th>&nbsp;</th></tr>
					<tr><th>Page '.$page.'</th></tr>
					<tr><th width="20%" colspan="2" style="border-top: 1px solid; border-bottom: 1px solid;">Total : </th>';
					echo '
					<th width="10%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_original_rent,2).'</td>';
 if($details['discount'] !=0){ 
					echo '<th width="10%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_disc,2).'</td>';
}
					echo '
						<th width="20%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($taxable_tot,2).'</td>
						<th width="10%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_tax,2).'</td>
						<th width="10%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_tax,2).'</td>
						<th width="20%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot,2).'</td></tr>';
					
					echo '<tr style="height:8mm;"><td colspan="'.$colspan.'">&nbsp;</td></tr>';

					$page_size=$footer_size;
					$remaining = $full_page - $page_size;
					 $page++;
				}


			

				$page_size += $row_height;
				if($page!=1){
						$row_height=11;
						
					}
					
				$tot += $rooms['room_rent']*$days;

		?>
		<tr>
			<td>BDL/<?php echo $rooms['financial_year'].'/'.$rooms['invoice_no']; ?>/Number Of Days : <?php echo $days; ?><br/></td>
			<td><?php echo room_name($rooms['room_id']).' ('.floor_name($row_type['floor_id']).')'; ?><br/><?php echo category_name($row_type['category_id']); ?></td>
			<?php if($rooms['discount']!=0 && $rooms['other_charges']!=0){
				echo '<td>'.floatval($rooms['original_room_rent'])*$days.'</td><td>'.floatval($rooms['other_charges'])*$days.'</td><td>'.floatval($rooms['discount'])*$days.'</td>';
				$base_rent = (floatval($rooms['original_room_rent'])*$days+floatval($rooms['other_charges'])*$days)-floatval($rooms['discount_value'])*$days;
				$taxable_tot+=$base_rent;
			}
			else{
				$base_rent = $rooms['original_room_rent']*$days ;
				echo '<td>'.$rooms['original_room_rent']*$days.'</td>';
				$taxable_tot+=$base_rent;
			}
			?>
			
			<td>
			<?php 
			if($rooms['invoice_type']=='tax'){
				$tax = round($base_rent*0.06,2);
				$temp_rent = $base_rent+$tax+$tax;;
				echo $base_rent;
			}
			else{
				$temp_rent = $rooms['room_rent'];
				echo $rooms['room_rent']*$days;
				$tax=0;
			}
			$tot_tax += $tax;
			$tot_original_rent+=$rooms['original_room_rent']*$days;
			$tot_extra_bed+=floatval($rooms['other_charges'])*$days;
			$tot_disc+=$rooms['discount_value'];
			$grand_total += $base_rent + $tax + $tax;
			?></td>
			<td><?php echo $tax;?></td>
			<td><?php echo $tax;?></td>
			<td><?php echo $base_rent + $tax + $tax ; ?></td>
		</tr>
		<?php 
				$start = date("d-m-Y H:i:s", strtotime($start)+86400);
			
		
	}
		//echo $page; 
		echo '<tr><th colspan="'.$colspan.'">&nbsp;</th></tr>';?>
		<tr>
			<th colspan="<?php echo $colspan; ?>">&nbsp;</th>
		</tr>
		<tr>
			<th colspan="2" width="45%" style="border-top: 1px solid; border-bottom: 1px solid;">Total : </td>
			<?php
			echo '
			<th width="10%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_original_rent,2).'</td>';
if($details['discount'] !=0){ 
			echo '<th width="8%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_extra_bed,2).'</td>';
}
 if($details['discount'] !=0){ 
			echo '<th width="10%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_disc,2).'</td>';
}
			echo '<th width="8%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($taxable_tot,2).'</td>
			<th width="8%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_tax,2).'</td>
			<th width="8%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($tot_tax,2).'</td>
			<th width="6%" style="border-top: 1px solid; border-bottom: 1px solid;">'.round($grand_total,2).'</td>';
			?>
		</tr>
		<tr>
			<th colspan="<?php echo $colspan-1; ?>" align="right">Round Off:</th>
			<th>
				<?php
				$tot = round($tot,2);
				$round_off = round($tot);
				$round_off = round($round_off-$tot,2);
				echo $round_off;
				
				?>
			</th>
		<tr>
			<th colspan="<?php echo $colspan-1; ?>" align="right">Amount Payable :</th>
			<th>
				<?php
				echo round($grand_total);
				
				?>
			</th>
		</tr>
		<tr>
			<td colspan="<?php echo $colspan-3; ?>"><h3 style="text-transform: capitalize;">Amount Payable : <?php echo int_to_words(round($grand_total,0)); ?> Rupees Only</h3></td>
			<td colspan="3" style="text-align: right;">For :BEDIS DREAM LAND<br /><br /></td>
		</tr>
		<tr><th colspan="4"><p>CHECK OUT TIME 11:00 AM. &nbsp; THANK YOU<br> &nbsp; &nbsp; Subject To Lucknow Jurisdiction only</p></th>
		<th><td colspan="4" style="text-align: right;"><br />(Authorised Signatory)</td></th></tr>
		<tr></tr>
	</table>
	</div>
</div>


    <div class="no-print"><input type="button" id="btnPrint" onclick="window.print();" value="Print Page" />&nbsp;&nbsp;<a href="print_combined.php?id=<?php echo $_GET['id']; ?>">Print Combined Receipt</a></div>
</body>
</html>