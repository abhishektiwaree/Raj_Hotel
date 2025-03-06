<?php
session_cache_limiter('nocache');
include ("scripts/settings.php");
logvalidate($_SESSION['username'], $_SERVER['SCRIPT_FILENAME']);
logvalidate('admin');
$response=1;
$msg='';
page_header();
navigation('');
if(isset($_GET['cancel'])){
	$sql = 'select * from allotment where sno='.$_GET['cancel'];
	$row = mysqli_fetch_array(execute_query($sql));
	if($row['cancel_date']==''){
		$sql = 'update allotment set cancel_date=CURRENT_TIMESTAMP where sno='.$_GET['cancel'];
		execute_query($sql);
	}
	else{
		$sql = 'update allotment set cancel_date=NULL where sno='.$_GET['cancel'];
		execute_query($sql);
	}
}
?>
<script type="text/javascript">
	$(function() {
	var options = {
		source: function (request, response){
			$.getJSON("scripts/ajax.php?id=customer",request, response);
		},
		minLength: 1,
		select: function( event, ui ) {
			log( ui.item ?
				"Selected: " + ui.item.value + " aka " + ui.item.label :
				"Nothing selected, input was " + this.value );
		},
		select: function( event, ui ) {

			$('#cust_id').val(ui.item.id);
			$('#cust_name1').val(ui.item.cust_name);
			
			$("#ajax_loader").show();
			return false;
		}
	};
$("input#cust_name1").on("keydown.autocomplete", function() {
	$(this).autocomplete(options);
});
});
	
</script>

 <div id="container">
	<h2>Daily Room Summary</h2>
	<div class="no-print" style="text-align: right;"><input type="button" id="btnPrint" onclick="window.print();" value="Print Page" /></div>	
	<?php echo '<ul><h4>'.$msg.'</h4></ul>'; ?>
	<form action="" class="wufoo leftLabel page1" id="report_allotment_daily" name="addnewdesignation" enctype="multipart/form-data" method="post" onSubmit="" >
		<table width="100%">
            	<tr style="background:#CCC;">
                
                	<th>Date From</th>
                    <td>
                    <span>
                    <script type="text/javascript" language="javascript">
					document.writeln(DateInput('allot_from', 'report_allotment_daily', true, 'YYYY-MM-DD', '<?php if(isset($_POST['allot_from'])){echo $_POST['allot_from'];}else{echo date("Y-m-d");}?>', 1))
                    </script>
                    </span>
                    </td>
                	<th>Date To</th>
                    <td>
                    <span>
                    <script type="text/javascript" language="javascript">
                    document.writeln(DateInput('allot_to', 'report_allotment_daily', true, 'YYYY-MM-DD', '<?php if(isset($_POST['allot_to'])){echo $_POST['allot_to'];}else{echo date("Y-m-d");}?>', 4))
                    </script>
                    </span>
                    </td>
                </tr>
                 <tr class="no-print">
                		<th>Guest Name</th>
						<td><input type="text" id="cust_name1" name="cust_name" value="<?php if(isset($_POST['cust_name'])){echo $_POST['cust_name'];} ?>">
							<input type="hidden" id="cust_id" name="cust_id" value="<?php if(isset($_POST['cust_id'])){echo $_POST['cust_id'];} ?>">
						</td>
						<th>Invoice No</th>
						<td><input type="text" name="inv" id="inv" value="<?php if(isset($_POST['inv'])){echo $_POST['inv'];} ?>"></td>
                </tr>
                <tr class="no-print">
                	
                	<th>Invoice Type</th>
                    <th>
                    <select name="invoice_type" id="invoice_type">
                    	<option value="all" <?php if(isset($_POST['invoice_type'])){if($_POST['invoice_type']=='all'){echo 'selected="selected"';}}?>>All</option>
                    	<option value="tax_invoice" <?php if(isset($_POST['invoice_type'])){if($_POST['invoice_type']=='tax_invoice'){echo 'selected="selected"';}}?>>Tax Invoice All</option>
                    	<option value="tax_invoice_w_gstin" <?php if(isset($_POST['invoice_type'])){if($_POST['invoice_type']=='tax_invoice_w_gstin'){echo 'selected="selected"';}}?>>Tax Invoice with GSTIN</option>
                    	<option value="tax_invoice_wo_gstin" <?php if(isset($_POST['invoice_type'])){if($_POST['invoice_type']=='tax_invoice_wo_gstin'){echo 'selected="selected"';}}?>>Tax Invoice without GSTIN</option>
                    	<option value="bill_of_supply" <?php if(isset($_POST['invoice_type'])){if($_POST['invoice_type']=='bill_of_supply'){echo 'selected="selected"';}}?>>Non Taxable Invoice</option>
                    </select></th>
                  
                    
                </tr>
            	<tr class="no-print">
                	<th colspan="3">
                    	<input type="submit" name="submit_form" value="Search with Filters" class="btTxt submit">
                    </th>
                    <th colspan="3">
                    	<input type="submit" name="reset_form" value="Reset Filters" class="btTxt submit">
                    </th>
                </tr>
            </table>
		<?php
		if(isset($_POST['invoice_type'])){
			$_POST['allot_from'] = date("Y-m-d", strtotime($_POST['allot_from']));
			$_POST['allot_to'] = date("Y-m-d", strtotime($_POST['allot_to']));
			$_POST['allot_to_re'] = date("Y-m-d", strtotime($_POST['allot_to'])+86400);
			//echo $_POST['allot_to'];
		
				$rowspan='rowspan="2"';
				$colspan='colspan="9"';
			
			
		?>
		<table>
			<tr  class="no-print">
				<td colspan="2" float="left">
				<a href="room_bill_summery_daily_export.php"><input type="button" style=" background-color: #f44f4f; color:white;" name="student_ledger" class="form-control btn btn-danger"  style="float: left;" value="Download In Excel"></a></span>
				</td>
			</tr>
			<tr>
				<th <?php echo $rowspan; ?>>S.No.</th>
				<th <?php echo $rowspan; ?>>Room No.</th>
				<th <?php echo $rowspan; ?>>Invoice No.</th>
				<!--<th <?php echo $rowspan; ?>>Room Rent</th>
				<th width="7%" <?php echo $rowspan; ?>>Extra Bed</th>
				<th width="7%" <?php echo $rowspan; ?>>Disc.</th>
				
				<th <?php echo $rowspan; ?>>Taxable Price</th>
				<th <?php echo $rowspan; ?>>SGST</th>
				<th <?php echo $rowspan; ?>>CGST</th>
			
				<th <?php echo $rowspan; ?>>Rent</th>-->
				<th <?php echo $colspan; ?>>Total</th>
				<th <?php echo $rowspan; ?>>Mode Of Payment</th>
				<th <?php echo $rowspan; ?>>Company/Guest Name</th>
				<!--<th <?php echo $rowspan; ?> class="no-print"></th>
				<th <?php echo $rowspan; ?> class="no-print"></th>
				<th <?php echo $rowspan; ?> class="no-print"></th>
				<th <?php echo $rowspan; ?> class="no-print"></th>-->
				<th <?php echo $rowspan; ?>>Paid Info</th>
			</tr>
			
			<tr>
				<th>Amount</th>
				<th>Extra Bed</th>
				<th> Disc</th>				
				<th>Net Amount</th>
				<th>CGST</th>
				<th>SGST</th>				
				<th>Invoice Amount</th>
				<th>Advance Amount</th>
				<th>Amount</th>
			</tr>
		
			<?php
			$_POST['searchby']='checkin';
			if($_POST['searchby']=='checkin'){
				$sql = 'select room_name,other_discount, cust_name, company_name, guest_name, allotment_date, exit_date, other_charges, tax_rate, cancel_date, allotment.sno, room_rent, discount, original_room_rent, discount_value, invoice_no, invoice_type, id_2 from allotment left join customer on customer.sno = cust_id left join room_master on room_master.sno = allotment.room_id where exit_date is not null and allotment.bill_create_date >="'.$_POST['allot_from'].'" and allotment.bill_create_date <"'.$_POST['allot_to_re'].'" ';
			}
			else{
				$sql = 'select room_name, other_discount, cust_name, company_name, guest_name, allotment_date, exit_date, other_charges, tax_rate, cancel_date, allotment.sno, room_rent, discount, original_room_rent, discount_value, invoice_no, invoice_type, id_2 from allotment left join customer on customer.sno = cust_id left join room_master on room_master.sno = allotment.room_id where exit_date is not null and allotment.bill_create_date >="'.$_POST['allot_from'].'" and allotment.bill_create_date <"'.$_POST['allot_to_re'].'" ';
			}
			//echo $sql;
			if($_POST['invoice_type']=='bill_of_supply'){
				$sql .= ' and invoice_type="bill_of_supply"';
			}
			elseif($_POST['invoice_type']=='tax_invoice'){
				$sql .= ' and invoice_type="tax"';
			}
			elseif($_POST['invoice_type']=='tax_invoice_w_gstin'){
				$sql .= '  and invoice_type="tax" and id_2!=""';
			}
			elseif($_POST['invoice_type']=='tax_invoice_wo_gstin'){
				$sql .= '  and invoice_type="tax" and (id_2="" or id_2 is null)';
			}
			if($_POST['cust_id'] !=''){
				$sql .=' and customer.sno="'.$_POST['cust_id'].'"';
			}
			if($_POST['cust_id'] =='' AND $_POST['cust_name'] != ''){
				$sql .=' and allotment.guest_name like "%'.$_POST['cust_name'].'%"';
			}
			if($_POST['inv'] !=''){
				$sql .=' and allotment.invoice_no="'.$_POST['inv'].'"';
			}
			$sql .= ' order by abs(invoice_no)';
			//echo $sql;
			$_SESSION['sql5']= $sql;
			$result = execute_query($sql);
			
			$sql_inv = 'SELECT MIN(abs(invoice_no)) as inv_start, MAX(abs(invoice_no)) as inv_end FROM allotment  where exit_date is not null and created_on>="'.$_POST['allot_from'].'" and created_on<="'.$_POST['allot_to'].'" ';
			$_SESSION['sql6']= $sql_inv;
			$inv_min_max = mysqli_fetch_array(execute_query($sql_inv));
			
			$i=1;
			$tot_taxable=0;
			$tot_amount=0;
			$tot_original_amount=0;
			$tot_tax=0;
			$tot_discount=0;
			$tot_other_charges=0;
			$pay_amount = 0;

			$tot_tax_taxable=0;
			$tot_tax_amount=0;
			$tot_tax_original_amount=0;
			$tot_tax_tax=0;
			$tot_tax_discount=0;
			$tot_tax_other_charges=0;
			
			$count=0;
			$count_tax=0;
			$cancel_count=0;
			
			$grand_advance_amount = 0;
			$grand_pay_amount = 0;
			$grand_tot_taxable=0;
			$grand_tot_amount=0;
			$grand_tot_original_amount=0;
			$grand_tot_tax=0;
			$grand_tot_discount=0;
			$grand_tot_other_charges=0;
			
			$cancel_tot_taxable=0;
			$cancel_original_amount=0;
			$cancel_tot_amount=0;
			$cancel_tot_tax=0;
			$cancel_tot_discount=0;
			$cancel_tot_other_charges=0;
			
			$row_old='';
		
				$colspan=12;
			$totdisc=0;
			$total_credit_paid = 0;
			echo '<tr>
			<td colspan="'.($colspan).'"><h4>Invoice Numbers from : '.$inv_min_max['inv_start'].'-'.$inv_min_max['inv_end'].'</h4></td></<tr>';
			while($row = mysqli_fetch_array($result)){
				if($row_old==''){
					$row_old=$row['invoice_type'];
				}
				$days = get_days($row['allotment_date'], $row['exit_date']);
				
				if($row['other_charges']==''){
					$other_charge = 0;
				}
				if($row['other_charges']!=''){
					$other_charge = $row['other_charges'];
				}
				
				if(strtolower(trim($row['invoice_type']))!='bill_of_supply'){
					$base_rent = round(($row['original_room_rent']-(floatval($row['other_discount'])+floatval($row['discount_value']))+$other_charge),2);
					$tax = round((($base_rent*$row['tax_rate']/200)),2);
				}
				else{
					$base_rent=$row['room_rent']+$other_charge;
					$tax=0;
				}
				$row['other_discount']=intval($row['other_discount'])+intval($row['discount_value']);
				$amount = $row['room_rent']*$days;
				if($row['cancel_date']==''){
					if($row['invoice_type']=='tax'){
						$tot_tax_discount+=$row['other_discount']*$days;
						$tot_tax_other_charges+=floatval($row['other_charges'])*$days;
						$tot_tax_original_amount+=$row['original_room_rent']*$days;
						$tot_tax_amount+=$amount;
						$tot_tax_tax += $tax*$days;
						$tot_tax_taxable+=$base_rent*$days;
						$count_tax++;

					}
					else{
						$tot_discount+=$row['other_discount']*$days;
						$tot_other_charges+=floatval($row['other_charges'])*$days;
						$tot_original_amount+=$row['original_room_rent']*$days;
						$tot_amount+=$amount;
						$tot_tax += $tax*$days;
						$tot_taxable+=$base_rent*$days;
						$count++;
					}
					$row_col = '';
					$cancel = '';
					$cancel_display = 'Cancel';
					
					$grand_tot_discount+=$row['other_discount']*$days;
					$grand_tot_other_charges+=floatval($row['other_charges'])*$days;
					$grand_tot_original_amount+=$row['original_room_rent']*$days;
					$grand_tot_amount+=$amount;
					$grand_tot_tax += $tax*$days;
					$grand_tot_taxable+=$base_rent*$days;
				}
				elseif($row['cancel_date'] !=''){
					$cancel_tot_taxable+=$base_rent*$days;
					$cancel_tot_amount+=$amount;
					$cancel_original_amount+=$row['original_room_rent']*$days;
					$cancel_tot_tax+=$tax*$days;
					$cancel_tot_discount+=$row['other_discount']*$days;
					$cancel_tot_other_charges+=$row['other_charges']*$days;
					$cancel_count++;

					$row_col = 'style="background:#FF0"';
					$cancel = '<br />Cancelled On : '.$row['cancel_date'];
					$cancel_display = 'Uncancel';
				}
				if($row['id_2']!=''){
					$gstin = '<br/>GSTIN : <b>'.$row['id_2'].'</b>';
				}
				else{
					$gstin='';
				}
				echo '<tr '.$row_col.'>
				<td>'.$i++.'</td>';
				echo '<td><b>'.$row['room_name'].'</b></td>';
				/**echo '<td><b>'.$row['room_name'].'</b><br><b>Company Name:</b>'.$row['company_name'].'<br /><b>Guest ame:</b>'.$row['guest_name'].'<br />In : '.$row['allotment_date'].'<br />Out :'.$row['exit_date'].'<br />Days : '.$days.$cancel.$gstin.'</td>';**/
				echo '<td>'.$row['invoice_no'].'</td>';
				/**echo '<td class="number">'.number_format((float)$row['original_room_rent'], 2, '.', '').'</td>
				<td class="number">';
				
				$pos = strpos($row['other_charges'], "%");
				$other_new = str_replace("%", "", $row['other_charges']);
				if($pos===false){
					echo number_format((float)$row['other_charges'], 2, '.', '');
				}
				else{
					echo number_format((float)$row['other_charges'], 2, '.', '').' ('.$row['other_charges'].')';
				}
					
				echo '</td>
				<td class="number">';
				
				$pos = strpos($row['other_discount'], "%");
				$disc_new = str_replace("%", "", $row['other_discount']);
				if($pos===false){
					echo number_format((float)$row['other_discount'], 2, '.', '');
				}
				else{
					echo number_format((float)$row['other_discount'], 2, '.', '').' ('.$row['discount'].')';
				}
					
				echo '</td>
				<td class="number">'.$base_rent.'</td>';
					echo '
						<td class="number">'.number_format((float)($tax), 2, '.', '').'</td>
						<td class="number">'.number_format((float)($tax), 2, '.', '').'</td>
						<td class="number">'.number_format((float)($row['room_rent']), 2, '.', '').'</td>';**/
					$sql_mop = 'SELECT * FROM `customer_transactions` WHERE `allotment_id`="'.$row['sno'].'" AND `type`="RENT"';
					$row_mop = mysqli_fetch_array(execute_query($sql_mop));
						echo '<td class="number">'.number_format((float)($row['original_room_rent']*$days), 2, '.', '').'</td>
						<td class="number">'.number_format((float)(floatval($row['other_charges'])*$days), 2, '.', '').'</td>
						<td class="number">'.number_format((float)((floatval($row['other_discount']))*$days), 2, '.', '').'</td>';
						echo '<td class="number">'.number_format((float)($base_rent*$days), 2, '.', '').'</td>
						<td class="number">'.number_format((float)($tax*$days), 2, '.', '').'</td>
						<td class="number">'.number_format((float)($tax*$days), 2, '.', '').'</td>';
						
						$totdisc+=$row['other_discount']*$days;
						$pay_amount = $amount - $row_mop['advance_set_amt'];
						$grand_pay_amount += $pay_amount; 
						$grand_advance_amount += $row_mop['advance_set_amt'];
				echo '
					<td class="number">'.number_format((float)$amount, 2, '.', '').'</td><td>'.$row_mop['advance_set_amt'].'</td><td>'.$pay_amount.'</td>';
					//$sql_mop = 'SELECT * FROM `customer_transactions` WHERE `allotment_id`="'.$row['sno'].'"';
					//$row_mop = mysqli_fetch_array(execute_query($sql_mop));
					echo '
					<td class="editable" id="row_'.$row_mop['sno'].'"> <span style="text-transform:uppercase">';
					if ($row_mop['mop'] == 'bank_transfer') {
						echo 'BANK TRANSFER';
					}
					else{
						echo $row_mop['mop'];
					}
					echo '</span></td>';
					if ($row_mop['mop_edited_by'] != '' AND ($row_mop['mop']=='credit' OR $row_mop['mop']=='CREDIT')) {
						$sql_customer = 'SELECT * FROM `customer` WHERE `sno`="'.$row_mop['cust_id'].'"';
						$row_customer = mysqli_fetch_array(execute_query($sql_customer));
						if ($row_customer['company_name'] != '') {
							echo '<td>'.$row_customer['company_name'].'</td>';
						}
						else{
							echo '<td>'.$row_customer['cust_name'].'</td>';
						}
					}
					else{
						echo '<td>&nbsp;</td>';
					}
					/**echo '<td class="no-print"><a href="print.php?id='.$row['sno'].'" target="_blank">View</a></td>
					<td class="no-print"><a href="report_allotment_daily.php?cancel='.$row['sno'].'">'.$cancel_display.'</a></td>
					<td class="no-print"><a href="allotment2.php?id='.$row['sno'].'" target="_blank">Edit</a></td>
					<td class="no-print"><a href="allotment.php?aid='.$row['sno'].'" target="_blank">Bill Edit</a></td>';**/
					if($row_mop['mop'] == 'credit' OR $row_mop['mop'] == 'CREDIT'){
						$sql_credit_check = 'SELECT * FROM `customer_transactions` WHERE `sno`="'.$row_mop['credit_bill_paid_sno'].'"';
						$row_credit_check = mysqli_fetch_array(execute_query($sql_credit_check));
						$paid_amount = $row_mop['credit_set_amt'];
						$paid = $row_mop['advance_set_amt'] + $row_mop['credit_set_amt'];
						if($paid == 0 OR $paid == ''){
							$show = 'UN-PAID';
							$paid_amount = 0;
						}
						else if($paid == $row_mop['amount']){
							$show = 'PAID('.$row_credit_check['timestamp'].')<br/>Amount : '.$paid_amount.'<br/>(';
							if($row_mop['credit_set_amt'] > 0){
								$show .= 'Credit';
							}
							$show .= ')';
						}
						else if($paid < $row_mop['amount']){
							$show = 'SEMI-PAID('.$row_credit_check['timestamp'].')<br/>Amount : '.$paid_amount.'(';
							if($row_mop['credit_set_amt'] > 0){
								$show .= 'Credit';
							}
							$show .= ')';
						}
						$total_credit_paid += $paid_amount;
					}
					else{
						$show = '';
					}
					echo '<td>'.$show.'</td></tr>';
				}
			echo '<tr><td colspan="'.($colspan-11).'">&nbsp;</td>
			<td colspan="2" style="text-align:right;">Grand Total : </td>
			<td class="number"><b>'.number_format((float)$grand_tot_original_amount, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$grand_tot_other_charges, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$totdisc, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$grand_tot_taxable, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$grand_tot_tax, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$grand_tot_tax, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$grand_tot_amount, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$grand_advance_amount, 2, '.', '').'</b></td>
			<td class="number"><b>'.number_format((float)$grand_pay_amount, 2, '.', '').'</b></td>
			<td colspan="2">&nbsp;</td>';
			/**echo '<td class="no-print">&nbsp;</td><td class="no-print">&nbsp;</td><td class="no-print">&nbsp;</td>';**/
			echo '<td>'.$total_credit_paid.'</td></tr>';
			
			?>
		</table>		
		<br/>
		<?php
			$sql_credit = 'select sum(amount) as amount, count(*) c from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and mop="credit" and type in ("RENT", "ADVANCE")';
			$_SESSION['sql_credit']= $sql_credit;

			$sql_paytm = 'select sum(amount) as amount, count(*) c from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and mop="paytm" and type in ("RENT", "ADVANCE")';
			$_SESSION['sql_paytm']= $sql_paytm;

			$sql_cheque = 'select sum(amount) as amount, count(*) c from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and mop="cheque" and type in ("RENT", "ADVANCE")';
			$_SESSION['sql_cheque']= $sql_cheque;

			$sql_bank_transfer = 'select sum(amount) as amount, count(*) c from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and mop="bank_transfer" and type in ("RENT", "ADVANCE")';
			$_SESSION['sql_bank_transfer']= $sql_bank_transfer;
			
			//echo $sql_credit;

			$sql_card = 'select sum(amount) as amount, count(*) c from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and mop="card" and type in ("RENT", "ADVANCE")';
			$_SESSION['sql_card']= $sql_card;
			
			$sql_cash = 'select sum(amount) as amount, count(*) c  from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and mop="cash" and type in ("RENT", "ADVANCE")';
			$_SESSION['sql_cash']= $sql_cash;
			
			$sql_all = 'select sum(amount) as amount, count(*) c  from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and type in ("RENT", "ADVANCE")';
			$_SESSION['sql_all']= $sql_all;
			//echo $sql_all;
			$sql_r='select sum(amount) as c, count(*) co from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on >="'.$_POST['allot_from'].'" and customer_transactions.created_on <="'.$_POST['allot_to_re'].'" and mop="card" and payment_for in ("ROOM")';
			$_SESSION['sql_r']= $sql_r;

			$sql_ro='select sum(amount) as c, count(*) co from customer_transactions left join allotment on allotment.sno = allotment_id left join customer on customer.sno = customer_transactions.cust_id where customer_transactions.created_on>="'.$_POST['allot_from'].'" and customer_transactions.created_on<="'.$_POST['allot_to_re'].'" and mop="cash" and payment_for in ("ROOM")';
			$_SESSION['sql_ro']= $sql_ro;
		
			
			if($_POST['invoice_type']=='bill_of_supply'){
				$sql_credit .= ' and allotment.invoice_type="bill_of_supply"';
				$sql_card .= ' and allotment.invoice_type="bill_of_supply"';
				$sql_paytm .= ' and allotment.invoice_type="bill_of_supply"';
				$sql_cheque .= ' and allotment.invoice_type="bill_of_supply"';
				$sql_bank_transfer .= ' and allotment.invoice_type="bill_of_supply"';
				$sql_cash .= ' and allotment.invoice_type="bill_of_supply"';
				$sql_all .= ' and allotment.invoice_type="bill_of_supply"';
				$sql_r .= 'and allotment.invoice_type="bill_of_supply"';
				$sql_ro .= 'and allotment.invoice_type="bill_of_supply"';
			}
			elseif($_POST['invoice_type']=='tax_invoice'){
				$sql_credit .= ' and allotment.invoice_type="tax"';
				$sql_card .= ' and allotment.invoice_type="tax"';
				$sql_paytm .= ' and allotment.invoice_type="tax"';
				$sql_cheque .= ' and allotment.invoice_type="tax"';
				$sql_bank_transfer .= ' and allotment.invoice_type="tax"';
				$sql_cash .= ' and allotment.invoice_type="tax"';
				$sql_all .= ' and allotment.invoice_type="tax"';
				$sql_r .= ' and allotment.invoice_type="tax"';
				$sql_ro .= ' and allotment.invoice_type="tax"';
			}
			elseif($_POST['invoice_type']=='tax_invoice_w_gstin'){
				$sql_credit .= '  and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_card .= '  and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_paytm .= '  and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_cheque .= '  and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_bank_transfer .= '  and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_cash .= '  and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_all .= '  and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_r .= ' and allotment.invoice_type="tax" and customer.id_2 !=" "';
				$sql_ro .= ' and allotment.invoice_type="tax" and customer.id_2 !=" "';
				
			}
			elseif($_POST['invoice_type']=='tax_invoice_wo_gstin'){
				$sql_credit .= '  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_card .= '  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_paytm .= '  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_cheque .= '  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_bank_transfer .= '  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_cash .= '  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_all .= '  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_r .='  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				$sql_ro .='  and allotment.invoice_type="tax" and (customer.id_2=" " or customer.id_2 is null)';
				
			}
			
		

			//echo $sql_credit;
			$cash = mysqli_fetch_array(execute_query($sql_cash));
			$paytm = mysqli_fetch_array(execute_query($sql_paytm));
			$cheque = mysqli_fetch_array(execute_query($sql_cheque));
			$bank_transfer = mysqli_fetch_array(execute_query($sql_bank_transfer));
			$credit = mysqli_fetch_array(execute_query($sql_credit));
			$card = mysqli_fetch_array(execute_query($sql_card));
			$all = mysqli_fetch_array(execute_query($sql_all));
			$r_room_card= mysqli_fetch_array(execute_query($sql_r));
			$r_room_cash= mysqli_fetch_array(execute_query($sql_ro));
			//echo $all['amount'];
			//echo " ".$cash['amount'];
			//echo $r_room_card['c'];
			//echo $r_room_cash['c'];
			$cash1=$cash['amount'];
			$paytm1=$paytm['amount'];
			$cheque1=$cheque['amount'];
			$bank_transfer1=$bank_transfer['amount'];
			$cr=$credit['amount']-$r_room_card['c']-$r_room_cash['c'];
			$cr_vik = $credit['amount'];
			$card1=$card['amount'];
			$all1=$all['amount']-$r_room_card['c']-$r_room_cash['c'];
			$all_vik = $all['amount'];
			//  echo "  ".$all1;
			$allno=$all['c'];
			$cashno=$cash['c'];
			$paytmno=$paytm['c'];
			$chequeno=$cheque['c'];
			$bank_transferno=$bank_transfer['c'];
			$cardno=$card['c'];
			$crno=$credit['c'];
			echo '<table>
			<tr>
				<th colspan="13">Receipts Summary</th>
			</tr>
			<tr>
				<td class="number" style="color:black;font-size:18px;"><b>Total Receipts</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$all_vik, 2, '.', '').'</b></td>
				<td style="color:black;font-size:18px;"><b>'.$allno.' Nos.</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>Cash Receipts</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cash1, 2, '.', '').'</b></td>
				<td style="color:black;font-size:18px;"><b>'.$cashno.'Nos.</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>Card Receipt</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$card1, 2, '.', '').'</b></td>
				<td style="color:black;font-size:18px;"><b>'.$cardno.' Nos.</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>Credit Receipt</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cr_vik, 2, '.', '').'</b></td>
				<td style="color:black;font-size:18px;"><b>'.$crno.' Nos.</b></td>
			</tr>
			<tr>
				<td class="number" style="color:black;font-size:18px;"><b>Paytm</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$paytm1, 2, '.', '').'</b></td>
				<td style="color:black;font-size:18px;"><b>'.$paytmno.' Nos.</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>Bank Transfer</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$bank_transfer1, 2, '.', '').'</b></td>
				<td style="color:black;font-size:18px;"><b>'.$bank_transferno.' Nos.</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>Cheque</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cheque1, 2, '.', '').'</b></td>
				<td style="color:black;font-size:18px;"><b>'.$chequeno.' Nos.</b></td>
				<td style="color:black;font-size:18px;"><b>Advance Amount</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$grand_advance_amount, 2, '.', '').'</b></td>
			</tr></table>';

			$sql_room_type = 'SELECT COUNT(*) AS COUNT , SUM(AMOUNT) AS SUM FROM `customer_transactions` WHERE `type`="RENT" AND `allotment_id`!="" AND `created_on`>="'.$_POST['allot_from'].'" AND `created_on`<="'.$_POST['allot_to_re'].'" ';
			$_SESSION['sql_room_type']= $sql_room_type;
			$row_room_type = mysqli_fetch_array(execute_query($sql_room_type));

			$sql_receipt_type = 'SELECT COUNT(*) AS COUNT , SUM(AMOUNT) AS SUM FROM `customer_transactions` WHERE `type`="RENT" AND `allotment_id` is null AND `payment_for`="ROOM" AND `created_on`>="'.$_POST['allot_from'].'" AND `created_on`<="'.$_POST['allot_to_re'].'" ';
			$_SESSION['sql_receipt_type']= $sql_receipt_type;
			$row_receipt_type = mysqli_fetch_array(execute_query($sql_receipt_type));
			//echo $sql_receipt_type;

			$type_count = $row_room_type['COUNT']+$row_receipt_type['COUNT'];
			$type_amount = $row_room_type['SUM']+$row_receipt_type['SUM'];

			/**echo '
			<table>
				<tr><th colspan="3">Receipts Type</th></tr>
				<tr>
					<th>&nbsp;</th>
					<th>Count</th>
					<th>Amount</th>
				</tr>
				<tr>
					<th>Room</th>
					<td>'.$row_room_type['COUNT'].'</td>
					<td>'.$row_room_type['SUM'].'</td>
				</tr>
				<tr>
					<th>Receipt</th>
					<td>'.$row_receipt_type['COUNT'].'</td>
					<td>'.$row_receipt_type['SUM'].'</td>
				</tr>
				<tr>
					<th>Total :</th>
					<th>'.$type_count.'</th>
					<th>'.$type_amount.'</th>
				</tr>
			</table>
			';**/
			
			echo '
			<br />
			<table>
			<tr><th colspan="13">Invoice Summary</th></tr>
			<tr>
				<th>&nbsp;</th>
				<th>Count</th>
				<th>Gross Amount</th>
				<th>Extra Bed</th>
				<th>Discount</th>
				<th>Net Amount</th>
				<th>CGST</th>
				<th>SGST</th>
				<th>Total Tax</th>
				<th>Total</th>
			</tr>
			<tr>
				<th style="color:black;font-size:18px;">Taxable</th>
				<td class="number" style="color:black;font-size:18px;"><b>'.$count_tax.'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($tot_tax_discount+$tot_tax_taxable), 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$tot_tax_other_charges, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$tot_tax_discount, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$tot_tax_taxable, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$tot_tax_tax, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$tot_tax_tax, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($tot_tax_tax+$tot_tax_tax), 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$tot_tax_amount, 2, '.', '').'</b></td>
			</tr>
			<tr style="background:#ccc;">
				<th style="color:black;font-size:18px;">Non Taxable</th>
				<td class="number" style="color:black;font-size:18px;"><b>'.$count.'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($tot_taxable+$tot_discount), 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($tot_other_charges), 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($tot_discount), 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($tot_taxable), 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>0</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>0</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>0</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($tot_amount), 2, '.', '').'</b></td>
			</tr>';
			$sql_advance = 'SELECT * FROM `customer_transactions` WHERE `type`="ADVANCE_AMT" AND `created_on`>="'.$_POST['allot_from'].'" AND `created_on`<="'.$_POST['allot_to_re'].'" ';
			$_SESSION['sql_advance']= $sql_advance;
			$result_advance = execute_query($sql_advance);
			echo '<table><tr><th colspan="4">Advance Summary</th></tr><tr><th>S.No.</th><th>Advance Amount</th><th>Date Of Booking</th><th>Type</th></tr>';
			$sno = 1;
			$grand_advance = 0;
			while($row_advance = mysqli_fetch_array($result_advance)){
				$grand_advance += $row_advance['amount'];
				echo '<tr><th>'.$sno++.'</th><td>'.$row_advance['amount'].'</td><td>'.date('d-m-Y h:i:s' , strtotime($row_advance['created_on'])).'</td>';
				if($row_advance['payment_for'] == 'banquet_rent'){
					echo '<td>Banquet</td>';
				}
				elseif($row_advance['payment_for'] == 'room_rent'){
					echo '<td>Room</td>';
				}
				echo '</tr>';
			}
			echo '<tr><th>&nbsp;</th><th>Total : '.$grand_advance.'</th><th colspan="2">&nbsp;</th></tr></table>';
			//echo $sql_advance;
			/**echo '<tr style="background:#ccc;">
				<th style="color:black;font-size:18px;">Cancelled</th>
				<td class="number" style="color:black;font-size:18px;"><b>'.$cancel_count.'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cancel_original_amount, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cancel_tot_other_charges, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cancel_tot_discount, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cancel_tot_taxable, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cancel_tot_tax, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cancel_tot_tax, 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)($cancel_tot_tax+$cancel_tot_tax), 2, '.', '').'</b></td>
				<td class="number" style="color:black;font-size:18px;"><b>'.number_format((float)$cancel_tot_amount, 2, '.', '').'</b></td>
			</tr>
			</table>';**/
		}
		?>
	</form>
</div>
<script>
$(function () {
	$("td.editable").dblclick(function (e) {
		var currentEle = $(this);
		var id = $(this).attr('id');
		var value = $(this).html();
		id = id.replace("row_", "");
		var txt = '<select name="mode_of_payment" id="mode_of_payment_'+id+'" class="small"><option value="cash" ';
		if(value=='cash'){
			txt += ' selected="selected" ';
		}
		txt += '>CASH</option><option value="card" ';
		if(value=='card'){
			txt += ' selected="selected" ';
		}
		txt += '>CARD</option><option value="credit" ';
		if(value=='credit'){
			txt += ' selected="selected" ';
		}txt += '>CREDIT</option><option value="PAYTM" ';
		if(value=='PAYTM'){
			txt += ' selected="selected" ';
		}txt += '>PAYTM</option><option value="bank_transfer" ';
		if(value=='bank_transfer'){
			txt += ' selected="selected" ';
		}txt += '>BANK TRANSFER</option><option value="cheque" ';
		if(value=='cheque'){
			txt += ' selected="selected" ';
		}txt += '>CHEQUE</option></select><br /><input type="button" value="Save" name="save_button" class="small" onClick="edit_mode_of_payment('+id+');">';
		$(this).html(txt);
	});
});

function edit_mode_of_payment(id){
	//alert("#mode_of_payment_"+id);
	var mop = $("#mode_of_payment_"+id).val();
	$("#row_"+id).html('<img src="images/loading_transparent.gif">');
	$.ajax({
		async: false,
		url: "scripts/ajax.php?id=mop_room&term="+id+"&mop="+mop,
		dataType: "json"
	})
	.done(function(data) {
		data = data[0];
		if(data.result=='true'){
			alert("Updated");
			if (mop == "bank_transfer") {
				mop = "BANK TRANSFER";
			}
			$("#row_"+id).html(mop);
		}
		else{
			alert("Failed. Retry.");
			var txt = '<select name="mode_of_payment" id="mode_of_payment_'+id+'" class="small"><option value="CASH" ';
			txt += '>CASH</option><option value="CARD" ';
			txt += '>CARD</option><option value="CREDIT" ';
			txt += '>CREDIT</option></option><option value="PAYTM" ';
			txt += '>PAYTM</option></option><option value="bank_transfer" ';
			txt += '>BANK TRANSFER</option><option value="cheque" ';
			txt += '>CHEQUE</option></select><br/><input type="button" value="Save" name="save_button" class="small" onClick="edit_mode_of_payment('+id+');">';
			$("#row_"+id).html(txt);
		}
	});

}	
</script>
<?php

page_footer();
?>