<?php
session_cache_limiter('nocache');
session_start();
include ("scripts/settings.php");
logvalidate($_SESSION['username'], $_SERVER['SCRIPT_FILENAME']);
logvalidate('admin');
$response=1;
$msg='';
$kot='';
$kot_num='';
$username=$_SESSION['username'];
date_default_timezone_set('Asia/Calcutta');
$res_chk='';
if (isset($_GET['edit_id'])) {
	$sql_invoice='select * from `invoice_sale_restaurant` where `sno`="'.$_GET['edit_id'].'"';
	//echo $sql_invoice;
	$result_invoice=execute_query($sql_invoice);
	$row_invoice=mysqli_fetch_array($result_invoice);
	if(isset($_POST['nocharge_kot'])){
    	$kot_type='1';
    }
    else{
    	$kot_type='0';
    }
    $sql_delete = 'DELETE FROM `kitchen_ticket_temp` WHERE `table_id`="'.$row_invoice['storeid'].'"';
    execute_query($sql_delete);
    $sql_delete = 'DELETE FROM `kitchen_ticket_temp_2` WHERE `table_id`="'.$row_invoice['storeid'].'"';
    execute_query($sql_delete);
	$sql_stock='select * from `stock_sale_restaurant` where `invoice_no`="'.$row_invoice['sno'].'"';
	//echo $sql_stock;
	$result_stock=execute_query($sql_stock);
	while($row_stock=mysqli_fetch_array($result_stock)) {
		$sql_stock_available='select * from `stock_available` where `sno`="'.$row_stock['part_id'].'"';
		$result_stock_available=execute_query($sql_stock_available);
		$row_stock_available=mysqli_fetch_array($result_stock_available);
	 	$sql_ktt= 'INSERT INTO `kitchen_ticket_temp`(`unit`,`item_id`, `item_name`, `item_price`, `table_id`,`time_stamp`,`kot_type`,`kot_no`) VALUES("'.$row_stock['qty'].'","'.$row_stock['part_id'].'","'.$row_stock_available['description'].'","'.$row_stock_available['mrp'].'","'.$row_invoice['storeid'].'","'.date("Y-m-d H:i:sa").'","'.$kot_type.'","'.$row_invoice['kot_no'].'")';
	 	execute_query($sql_ktt);

	 	$sql_ktt1= 'INSERT INTO `kitchen_ticket_temp_2`(`unit`,`item_id`, `item_name`, `item_price`, `table_id`,`time_stamp`,`kot_type`,`kot_no`) VALUES("'.$row_stock['qty'].'","'.$row_stock['part_id'].'","'.$row_stock_available['description'].'","'.$row_stock_available['mrp'].'","'.$row_invoice['storeid'].'","'.date("Y-m-d H:i:sa").'","'.$kot_type.'","'.$row_invoice['kot_no'].'")';
	 	execute_query($sql_ktt1);
	 } 

	
}
if(isset($_POST['nocharge_bill'])){

	$custid=$_POST['customer_sno'];
	$end=$_POST['current_id'];
	$timestamp= microtime(true);
	if(isset($_POST['table_sno'])){
		$table=$_POST['table_sno'];	
	}
	elseif(isset($_POST['room_sno'])){
		$table="room_".$_POST['room_sno'];
	}
	$flag=0;
	$grand_total=0;
	$sql = 'select * from invoice_sale_restaurant order by abs(nck_no) desc limit 1';
		//echo $sql;
	$nonkot_result = execute_query($sql);
	if(mysqli_num_rows($nonkot_result)!=0){
		$nonkot_no = mysqli_fetch_array($nonkot_result);
		$nck_no = $nonkot_no['nck_no']+1;
	}
	else{
		$nck_no = 1;
	}
	for($i=1;$i<=$end;$i++){
		$curr_id="data_id_".$i;
        $curr_sno=$_POST[$curr_id];
        $unit_id="unit_data_".$curr_sno;
        $item_id="item_name_".$curr_sno;
        $price_id="unit_price_".$curr_sno;
        $unit=$_POST[$unit_id];
        $item=$_POST[$item_id];
        $price=$_POST[$price_id];
        $date=date("Y-m-d");
        $timestamp=date("Y-m-d H:i:sa");
        $total=$unit*$price;
        $grand_total+=$total;
		$sql = "INSERT INTO `stock_sale_restaurant` ( `supplier_id`, `part_id`, `basicprice`, `discount`, `discount_value`, `vat`, `vat_value`, `excise`, `excise_value`, `taxable_amount`, `effective_price`, `qty`, `part_dateofpurchase`, `amount`, `unit`, `admin_remarks`,`nck_no`) 
		VALUES ('".$custid."', '".$item."', '".$price."', '', '', '', '', '', '', '".$total."', '".$price."', '".$unit."', '".$date."', '".$total."', '".$unit."', '".$timestamp."','".$nck_no."')";
		//echo $sql.'<br>';
		execute_query($sql);
		if(mysqli_error($db)){
			$msg .= '<li>Error # 2 : '.mysqli_error($db).' >> '.$sql.'</li>';
		}

	}

    $sql = 'INSERT INTO `invoice_sale_restaurant` (`invoice_type`, `invoice_no`, `total_amount`, `taxable_amount`, `dateofdispatch`, `user_id`, `timestamp`, `supplier_id`, `remark`, `quantity`, `tot_vat`, `tot_sat`, `round_off`, `tot_disc`, `other_discount`, `grand_total`, `storeid`, `created_by`, `creation_time`, `financial_year`, `mode_of_payment`,`table_no`,`waitor_name`,`nck_no`) 
	VALUES ("","", "'.$grand_total.'", "'.$grand_total.'", "'.$date.'", "'.$username.'", "'.$date.'", "'.$custid.'", "", "", "", "",  "", "", "", "'.$grand_total.'", "'.$table.'", "","'.date("Y-m-d H:i:s").'", "", "nocharge","","","'.$nck_no.'")';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<li>Error # 1  : '.mysqli_error($db).' >> '.$sql.'</li>';
		$inv=0;
	}
	else{
		$inv = insert_id($db);
	}
	$sql = "INSERT INTO `customer_transactions` (`cust_id`, `type`, `number`, `amount`, `timestamp`, `remarks`, `account`,`financial_year`,`invoice_no`,mop) 
	VALUES ('".$custid."', 'sale_restaurant', '', '".$grand_total."', '".$timestamp."', 'nocharge','', '','".$nck_no."','nocharge')";
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<li>Error # 3 : '.mysqli_error($db).' >> '.$sql.'</li>';
	}
}
elseif(isset($_POST['cancel'])){
	$sql_invoice='select * from `invoice_sale_restaurant` where `sno`="'.$_POST['edit_pass'].'"';
	//echo $sql_invoice;
	$result_invoice=execute_query($sql_invoice);
	$row_invoice=mysqli_fetch_array($result_invoice);

    $sql_delete = 'DELETE FROM `kitchen_ticket_temp` WHERE `table_id`="'.$row_invoice['storeid'].'"';
    execute_query($sql_delete);
    $sql_delete = 'DELETE FROM `kitchen_ticket_temp_2` WHERE `table_id`="'.$row_invoice['storeid'].'"';
    execute_query($sql_delete);
    $tableid = $row_invoice['storeid'];
    $pos = strpos($row_invoice['storeid'], "room_");
							//echo '@@>>'.$pos;
	if($pos !== false){
		$tableid=str_replace("room_", "", $_GET['room_id']);
		$sql="UPDATE `room_master` SET `booked_status`='0' WHERE sno='$tableid'";
		$res = execute_query($sql);
		if($res){
			header("location:dine_in_order_room.php");
		}
	}
	else{
		$sql="UPDATE `res_table` SET `booked_status`='0' WHERE sno='$tableid'";
		$res = execute_query($sql);
		if($res){
			header("location:dine_in_order_table.php");
		}
	}
}
else{
	if(isset($_POST['done'])){
		$end=$_POST['current_id'];
		$timestamp= microtime(true);
		if(isset($_POST['table_sno'])){
			$table=$_POST['table_sno'];	
		}
		elseif(isset($_POST['room_sno'])){
			$table="room_".$_POST['room_sno'];
		}
		$flag=0;
		$sql = 'select * from invoice_sale_restaurant  order by abs(kot_no) desc limit 1';
		$kot_res = execute_query($sql);
		$sql="SELECT * FROM `kitchen_ticket_temp` order by abs(kot_no) desc limit 1";
		$k_kot=execute_query($sql);
		$numrowkot=mysqli_num_rows($k_kot);
		if(mysqli_num_rows($kot_res)!=0 || $numrowkot !=0){
			if(mysqli_num_rows($k_kot) != 0){
				$r_kot=mysqli_fetch_array($k_kot);
				$kot_num=$r_kot['kot_no'];
			}
			$kot_no = mysqli_fetch_array($kot_res);
			$kot=$kot_no['kot_no'];
			if($kot_num > $kot){
				$kotno=$kot_num+1;
			}
			else{
				$kotno= $kot_no['kot_no']+1;
			}
			
		}
		else{
			$kotno = 1;
		}
		for($i=1;$i<=$end;$i++){
			$curr_id="data_id_".$i;
	        $curr_sno=$_POST[$curr_id];
	        $unit_id="unit_data_".$curr_sno;
	        $item_id="item_name_".$curr_sno;
	        $price_id="unit_price_".$curr_sno;
	        $unit=$_POST[$unit_id];
	        $item=$_POST[$item_id];
	        $price=$_POST[$price_id];
	        if(isset($_POST['nocharge_kot'])){
	        	$kot_type='1';
	        }
	        else{
	        	$kot_type='0';
	        }
	        $sql="INSERT INTO `kitchen_ticket_temp`(`unit`,`item_id`, `item_name`, `item_price`, `table_id`,`time_stamp`,`kot_type`,`kot_no`) VALUES('$unit','$curr_sno','$item','$price','$table','$timestamp','$kot_type','$kotno')";
	        $res_chk=execute_query($sql);
			if(mysqli_error($db)){
				$msg .= '<li>Error # 0012 >> '.mysqli_error($db).' >> '.$sql;
			}
			else{
				$flag=1;
			}
	    }
		if($flag==1){
			
			$msg .= '<script>window.open("kot.php?tid='.$table.'&ts='.$timestamp.'&kot='.$kotno.'");</script>
			<li>Saved</li>';
			if(isset($_POST['table_sno'])){
				$table=$_POST['table_sno'];	
				$sql="UPDATE `res_table` SET `booked_status`='1' WHERE sno='$table'";
				execute_query($sql);
				$table_edit = $_POST['table_sno'];
				$type = "table";
			}
			elseif(isset($_POST['room_sno'])){
				$table=$_POST['room_sno'];
				$sql="UPDATE `room_master` SET `booked_status`='1' WHERE sno='$table'";
				execute_query($sql);
				$table_edit = 'room_'.$_POST['room_sno'];
				$type = "room";
			}
			if($_POST['edit_pass'] != ''){
				header("Location:bill_invoice.php?table_id=".$table_edit."&edit_id=".$_POST['edit_pass']."&type=".$type."");
			}
		}
	}
}
if(isset($_GET['table_id']) || isset($_GET['room_id']) || isset($_GET['edit_id'])){
	$response=2;
} 
page_header();

?>

<link href="css/dine_in_order1.css" rel="stylesheet">

<?php
switch($response){
	case 1:{
?>
<style type="text/css">
#tables button{
  border: none;
  outline: none;
  background-color:#66AAAA;
  color: white;
  height:70px;
  width:110px;
  cursor: pointer;
  margin: 5px;
  font-size: 30px;
}
</style>
<div id="container">
	
        <h2>Table</h2>	
		<?php echo '<ul><h4>'.$msg.'</h4></ul>'; ?>

		<form action="" class="wufoo leftLabel page1"  name="addnewdesignation" enctype="multipart/form-data" method="post" onSubmit="">
			<div id="tables">
			<?php
				$sql="SELECT * FROM `res_table`";
				$res=execute_query($sql);
				while($row=mysqli_fetch_array($res)){
					$id=$row['sno'];
                    if($row['booked_status'] ==1){
				        echo '<button type="button" onclick="window.open(\'dine_in_order.php?table_id='.$id.'\', \'_self\');" id="table_'.$id.'" style="background-color:red;">'.$row['table_number'].'</button></a>';
				    }
                    else{
                         echo '<button type="button"  onclick="window.open(\'dine_in_order.php?table_id='.$id.'\', \'_self\');" id="table_'.$id.'">'.$row['table_number'].'</button></a>';
                    }
                }

			?>
			</div>
			<h2>Rooms</h2>	
			<div id="tables">
			<?php
				$sql="SELECT * FROM `room_master` order by abs(room_name)";
				$res=execute_query($sql);
				while($row=mysqli_fetch_array($res)){
					$id=$row['sno'];
                    if($row['booked_status'] == 1){
				        echo '<button type="button" onclick="window.open(\'dine_in_order.php?room_id='.$id.'\', \'_self\');" id="table_'.$id.'" style="background-color:red;">'.$row['room_name'].'</button></a>';
				    }
                    else{
                         echo '<button type="button"  onclick="window.open(\'dine_in_order.php?room_id='.$id.'\', \'_self\');" id="table_'.$id.'">'.$row['room_name'].'</button></a>';
                    }
                }

			?>
			</div>
		</form>
</div>

<?php
		break;
	}
	case 2:{
		if (isset($_GET['edit_id'])) {
			$tab_id=$row_invoice['storeid'];
		}
		elseif(isset($_GET['table_id'])){
			$tab_id=$_GET['table_id'];
		}
		else{
			$tab_id="room_".$_GET['room_id'];
			$sql="SELECT * FROM `room_master` where sno=".$_GET['room_id'];
			$room_details=mysqli_fetch_assoc(execute_query($sql));
		}
		$totPrice=0;
		$sql="SELECT * FROM `kitchen_ticket_temp` WHERE table_id='$tab_id'";
		$res=execute_query($sql);
		$rowcount=mysqli_num_rows($res);
		if($rowcount > 0){
			while($row1=mysqli_fetch_array($res)){
				$totPrice+=$row1['item_price'];
			}
			echo '<input type="hidden" name="pre_total" id="pre_total" value="'.$totPrice.'">';
		}
		?>
		
		<script>

		function filter_item(id){
			var item_id='#item_list_'+id;
			$("#display_box").html($(item_id).clone());
			$('.item_box').hide();
			$(item_id).show(); 
		}
		//delete item
		$(document).ready(function() {
		  var tid = "";
		  $('#mytable tr').click(function(event) {
			tid = $(this).attr('id');
		  });
		  $("#del").click(function() {
			if ($('#' + tid).length) {
			  $('#' + tid).remove();
			}
		  });
		});
		</script>
		<script>
		var totalPrice=0;
		function insert_data(id){
			var item_name='item_name_'+id;
			var item_price='item_price_'+id; 
			var item=document.getElementById(item_name).value;
			var price=parseFloat(document.getElementById(item_price).value);
			var totalPrice = parseFloat($("#show_total").html());
			var totalPrice = parseFloat($("#show_total1").html());
			if(!totalPrice){
				totalPrice=0;
			}
			//alert(price);
			var price1=price;
			var unit=1;
			var unit1=1;
			var flag = 0;
			var current_id = parseFloat($("#current_id").val());
			var chk_row = document.getElementById("row_"+id);
			if(!chk_row){
				flag = 0;
			}
			else{
				flag=1;
			}

			if(flag == 1) {
				unit=parseFloat($("#unit_data_"+id).html());
				unit1=parseFloat(document.getElementById("unit1_"+id).value);
				unit++;
				unit1++;
				totalPrice+=price;
				$("#show_total").html(totalPrice);
				$("#show_total1").html(totalPrice);
				price=price*unit;
				price1=price1*unit1;
				$("#unit_data_"+id).html(unit);
				$("#item_price"+id).html(price);
				document.getElementById("unit1_"+id).value=unit1;
				document.getElementById("price1_"+id).value=price1;
			} 
			else {
				current_id++;
				//alert(current_id);
				$("#current_id").val(current_id);
				totalPrice+=price;
				$("#show_total").html(totalPrice);
				$("#show_total1").html(totalPrice);
				//alert(totalPrice);
				$('#mytable > tbody:last').append('<tr id="row_'+id+'"><td id="unit_data_'+id+'">'+ unit + '</td><input type="hidden" name="unit_data_'+id+'" id="unit1_'+id+'" value="'+unit1+'"><td id="item_name_'+id+'">' + item+ '</td><input type="hidden" name="item_name_'+id+'" id="item1_'+id+'" value="'+item+'"><td id="item_price'+id+'">' + price+'</td><input type="hidden" name="item_price'+id+'" id="price1_'+id+'" value="'+price1+'"><input type="hidden" name="unit_price_'+id+'" id="unit_price_'+id+'" value="'+price1+'"><input type="hidden" name="data_id_'+current_id+'" value="'+id+'"><td><button type="button" onclick="remove_data('+id+')">-</button></td><td><button type="button" onclick="insert_data('+id+');">+</button></td></tr>');
			}
		}
		function remove_data(id){
			var current_id = parseFloat($('#current_id').val());
			if(!current_id){
				
			}
			else{
				var qty = parseFloat($('#unit1_'+id).val());
				if(!qty){
					
				}
				else if(qty==1){
					var price = parseFloat($('#unit_price_'+id).val());
				//	alert(price);
					var inv_total = parseFloat($('#show_total').html());
					var inv_total = parseFloat($('#show_total1').html());
					inv_total -= price;
					$('#show_total').html(inv_total);
					$('#show_total1').html(inv_total);
					$('#row_'+id).remove();
					current_id--;
					$('#current_id').val(current_id);
				}
				else{
					var tot_price = parseFloat($('#price1_'+id).val());
					var price = parseFloat($('#unit_price_'+id).val());
					alert(price);
					var inv_total = parseFloat($('#show_total').html());
					var inv_total = parseFloat($('#show_total1').html());

					tot_price -= price;
					inv_total -= price;
					qty--;
					$('#unit1_'+id).val(qty);
					$('#unit_data_'+id).html(qty);
					$('#price1_'+id).val(tot_price);
					$('#item_price'+id).html(tot_price);
					$('#show_total').html(inv_total);
					$('#show_total').html(inv_total);
				}
			}
			
		}
			
		$(function() {
			var options = {
				source: function (request, response){
					$.getJSON("scripts/ajax.php?id=prod",request, response);
				},
				minLength: 1,
				select: function( event, ui ) {
					log( ui.item ?
						"Selected: " + ui.item.value + " aka " + ui.item.label :
						"Nothing selected, input was " + this.value );
				},
				select: function( event, ui ) {
					insert_data(ui.item.id);
					$("#search_product").val('');
					$("#search_product").focus();
					return false;
				}
			};
		$("input#search_product").on("keydown.autocomplete", function() {
			$(this).autocomplete(options);
		});
		});
			
		$(document).ready(function() {
			// action on key up
			$(document).keyup(function(e) {
				if(e.which == 17) {
					isCtrl = false;
				}
			});
			// action on key down
			$(document).keydown(function(e) {
				if(e.which == 17) {
					isCtrl = true; 
				}
				if(e.which == 59 && isCtrl) { 
					$("#search_product").focus();
				} 
			});
			$( function() {
				$('#item_container').click( function() {
					alert('ok');
					$(this).toggleClass("red-cell");
				} );
			});
		});	
		
		function reduce_kot(id){
			//alert(id);
			var total = parseFloat($("#show_total").html());
			//alert(total);
			var total = parseFloat($("#show_total1").html());
			//alert(total);
			$("#show_total").html('<img src="images/loading_transparent.gif">');
			$("#tr_"+id+" #unit").html('<img src="images/loading_transparent.gif">');
			$("#tr_"+id+" #price").html('<img src="images/loading_transparent.gif">');
			$.ajax({
				async: false,
				url: "scripts/ajax.php?id=red_kot&term="+id,
				dataType: "json"
			})
			.done(function(data) {
				data = data[0];
				if(data.qty=='0'){
					$("#tr_"+id).remove();
				}
				else{
					$("#tr_"+id+" #unit").html(data.qty);
					$("#tr_"+id+" #price").html(data.amount);
				}
				total = total-data.rate;
				$("#show_total").html(total);
				$("#show_total1").html(total);
				
			});

		}
		
		function remove_kot(id){
			
		}

		$(function () {
        $("#nocharge_kot").click(function () {
            if ($(this).is(":checked")) {
                $("#show_div").show();
               	$("#total_nocharge").show();
               	$("#total_bill").hide();
            } else {
               $("#show_div").hide();
               	$("#total_nocharge").hide();
               	$("#total_bill").show();
            }
        });
    });
		</script>
		<script>
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
						$("#customer_name").val(ui.item.label);
						$("#customer_sno").val(ui.item.id);
					}
					
				};
			$("input#customer_name").on("keydown.autocomplete", function() {
				$(this).autocomplete(options);
			});
		});
		</script>
		<div id="container">
			<?php echo '<ul><h4>'.$msg.'</h4></ul>'; ?>
			<form action="dine_in_order.php" class="wufoo leftLabel page1"  name="purchase_report" id="purchase_report" enctype="multipart/form-data" method="post" onSubmit="" >
				<input type="hidden" name="edit_pass" value="<?php if(isset($_GET['edit_id'])){echo $_GET['edit_id'];} ?>">
				<div id="left_container">
					<div id="left_div">
						<div id="ajax_loader" style="display: none;"><img src="images/loading_transparent.gif"></div>
						<table id="mytable">
							<tr>
								<th>Unit</th>
								<th>Item</th>
								<th>Price</th>					
								<th colspan="2"></th>
							</tr>
							<?php
							$total=0;
							if (isset($_GET['edit_id'])) {
								$tableid=$row_invoice['storeid'];
							}
							elseif(isset($_GET['table_id'])){
								$tableid=$_GET['table_id'];
							}
							else{
								$tableid="room_".$_GET['room_id'];
							}
							$sql="SELECT * FROM `kitchen_ticket_temp` WHERE table_id='$tableid'";
							//echo $sql;
							$res=execute_query($sql);
							while($item_details=mysqli_fetch_array($res)){
								$total+=($item_details['item_price']*$item_details['unit']);
								echo'<tr style="background-color:yellow;" id="tr_'.$item_details['sno'].'">
									<td id="unit">'.$item_details['unit'].'</td>
									<td id="item">'.$item_details['item_name'].'</td>
									<td id="price">'.$item_details['item_price']*$item_details['unit'].'</td>
									<td id="reduce"><button type="button" onclick="reduce_kot('.$item_details['sno'].')">-</button></td>
								</tr>';
								if($item_details['kot_type']=='1'){
									$kot='1';
								}
								else{
									$kot='';
								}
							}
							?>
						</table>
						
						<input type="hidden" name="current_id" id="current_id" value="0">
					</div>
					<div id="total_show">
						<b>NON CHARGEABLE KOT</b><input type="checkbox" name="nocharge_kot" id="nocharge_kot" value="">
						<div id="show_div" style="display: none">
							<input type="text" name="customer_name" id="customer_name" placeholder="Customer">
							<input type="hidden" name="customer_sno" id="customer_sno">
						</div>
						<div id="total_bill">
							<a href="bill_invoice.php?table_id=<?php echo $tableid; if(isset($_GET['edit_id'])){echo '&edit_id='.$_GET['edit_id'];}?>"><button type="button" id="total_cal"><h3>Total : Rs.<span id="show_total"><?php echo $total; ?></span></h3></button></a>
						</div>
						<div id="total_nocharge" style="display:none;" >
							<button type="submit" name="nocharge_bill"><b>Total : Rs.</b><span id="show_total1"><?php echo $total;?></span></button>
						</div>
						
					</div>
				</div>
				<div id="item_list">
					<div id="inside_item">
						<div id="search_box">
							Search Product : <input type="text" name="search_product" id="search_product" placeholder="Shortcut : Ctrl+; (Control with Semi-Colon)">
						</div>
						<div id="display_box">
							
						</div>

					</div>
					<div id="div_button">
				
						<?php
						if (isset($_GET['edit_id'])) {
							$pos = strpos($row_invoice['storeid'], "room_");
							//echo '@@>>'.$pos;
							if($pos !== false){
								$row_invoice['storeid'] = str_replace("room_", "", $row_invoice['storeid']);
								$sql = 'select * from room_master where sno='.$row_invoice['storeid'];
								//echo $sql;
								$room_details = mysqli_fetch_array(execute_query($sql));
								
								echo '<button type="button" name="Table" >Room: '.$room_details['room_name'].'</button>';
								echo '<input type="hidden" id="room_sno" name="room_sno" value="'.$row_invoice['storeid'].'">';
								echo '<a href="change_table.php?roomid='.$room_details['sno'].'&e_id='.$_GET['edit_id'].'"><button type="button" name="Table" >Change Room: '.$room_details['room_name'].'</button></a>';
							}
							else{
								$sql = 'select * from res_table where sno='.$row_invoice['storeid'];
								//echo $sql;
								$table_name = mysqli_fetch_array(execute_query($sql));
								echo '<button type="button" name="Table" >Table: '.$table_name['table_number'].'</button>';
								echo '<input type="hidden" id="table_sno" name="table_sno" value="'.$row_invoice['storeid'].'">';
								echo '<a href="change_table.php?tableid='.$table_name['sno'].'&e_id='.$_GET['edit_id'].'"><button type="button" name="Table" >Change Table: '.$table_name['table_number'].'</button></a>';
							}
						}
						elseif(isset($_GET['table_id'])){
							$sql = 'select * from res_table where sno='.$tableid;
							$table_name = mysqli_fetch_array(execute_query($sql));
							echo '<button type="button" name="Table" >Table: '.$table_name['table_number'].'</button>';
							echo '<input type="hidden" id="table_sno" name="table_sno" value="'.$_GET['table_id'].'">';
						}
						else{
							echo '<button type="button" name="Table" >Room: '.$room_details['room_name'].'</button>';
							echo '<input type="hidden" id="room_sno" name="room_sno" value="'.$_GET['room_id'].'">';
						}
						?>
						<button type="submit" name="done" id="done" >Done</button>
						<button type="submit" name="cancel" id="cancel">Bill Wash</button>
						<?php
						if(isset($_GET['table_id'])){
							$sql = 'select * from res_table where sno='.$tableid;
							$table_name = mysqli_fetch_array(execute_query($sql));
							echo '<a href="change_table.php?tableid='.$table_name['sno'].'"><button type="button" name="Table" >Change Table: '.$table_name['table_number'].'</button></a>';
							
						}
						else if(!isset($_GET['edit_id'])){
							echo '<a href="change_table.php?roomid='.$room_details['sno'].'"><button type="button" name="Table" >Change Room: '.$room_details['room_name'].'</button></a>';
							
						}
						elseif (isset($_GET['edit_id'])) {
							
						}
						else{
							echo '<button type="button" name="Table" >Change Room: </button>';
						}
						?>
					</div>			
				</div>

				<div id="category_item">
					<div class="mySlides fade" id="category_list">
						<?php
						$sql="SELECT * FROM `new_type`";
						$res=execute_query($sql);
						while($cat_item=mysqli_fetch_array($res)){
							$id=$cat_item['sno'];                    
							echo '<button type="button" id="cat_'.$cat_item['sno'].'" class="cat_'.$cat_item['sno'].'"  onclick="filter_item('.$id.')" >'.$cat_item['description'].'</button>';
						}
						?>
					</div>
				</div>

			  



		   </form>
		   
<?php
$sql="SELECT * FROM `new_type` order by description";
$res1=execute_query($sql);
while($cat_item1=mysqli_fetch_array($res1)){
?>
	<div id="item_list_<?php echo $cat_item1['sno']; ?>" style="display:none;" class="item_box">
	<?php 
	$sql="SELECT * FROM `stock_available` where `type`=".$cat_item1['sno'];
	$res=execute_query($sql);
	while($row=mysqli_fetch_array($res)){
		$id=$row['sno'];
		echo '<div id="item_container"><div id="item_'.$row['sno'].'" type="button" class="item_'.$row['type'].'" onclick="insert_data('.$row['sno'].');">'.$row['description'].'</div>
		<input name="unit_'.$row['sno'].'" id="unit_'.$row['sno'].'" type="hidden" value="1">
		<input name="item_id_'.$row['sno'].'" id="item_id_'.$row['sno'].'" type="hidden" value="'.$row['sno'].'">
		<input name="item_name_'.$row['sno'].'"  id="item_name_'.$row['sno'].'" type="hidden" value="'.$row['description'].'">
		<input name="item_price_'.$row['sno'].'"  id="item_price_'.$row['sno'].'" type="hidden" value="'.$row['mrp'].'"></div>';
	}
	?>
	</div>
<?php } ?>
		</div>

<?php
		break;
	}
}

page_footer(); 
?> 	