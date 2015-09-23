<?php
	include_once(".\module.php");
	session_start();
	
	if(!isset($_SESSION['userid'])){
			redirect_url('login.php?p=index.php');
	}

	$mongodb = getMongoDbConnection();
	$database = $mongodb->test;
	$collection = $database->response;

	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
	$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

	if(isset($_GET['from'])){
		$from = $_GET['from'];
	}
	if(isset($_GET['to'])){
		$to =$_GET['to'];
	}

	$skip = ($page - 1) * $limit;
	$next = ($page + 1);
	$prev = ($page - 1);

	if(isset($from) && isset($to)){
		$from = $from." 00:00:00";
		$to = $to." 00:00:00";
		$search_query = array('timestamp' => array('$gte'=>$from,'$lte'=>$to));
		$cursor = $collection->find($search_query)->skip($skip)->limit($limit);
	}

	else if(isset($_GET['search'])){
		$search_string = $_GET['search'];
		$cursor = $collection->find(
		    array('$text' => array('$search' => $search_string)),
		    array('score' => array('$meta' => 'textScore'))
		);

		$cursor = $cursor->sort(
		    array('score' => array('$meta' => 'textScore'))
		)->limit($limit)->skip($skip);
	}
	else{
		
		$sort = array('timestamp' => -1);
		$cursor = $collection->find()->skip($skip)->limit($limit)->sort($sort);
	}
	

	$display_index = array();
	$headers = $collection->find();

	foreach ($headers as $report) {
		foreach ($report as $key => $value) {
			$display_index[$key] = '1';
		}
	}

	foreach ($display_index as $key => $value) {

		if($key == '_id' || $key == 'smtp-id' || $key == 'response' || $key == 'sg_event_id'){
			$display_index[$key]='0';
		}
	}
	
	$count_rec = 0;
	foreach ($cursor as $key => $value) {
		$count_rec++;
	}

	
?>

<head>
	<title>Email Reports</title>
	<meta http-equiv="content-type" content="text/plain; charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="style.css">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

	<div class="row" style="width: 100%;">
		<div class="col-sm-1"></div>
		<div class="col-sm-1"></div>
		<div class="col-sm-8">
			<center><h2>Email Reports - Send Grid</h2></center>
		</div>
		<div class="col-sm-2" style="padding-top:20px; padding-left:160px;">
			<form action="login.php" method="post">
				<input hidden type="text" name="logout" id="logout" value="1">
				<button class="btn btn-sm" type="submit">logout</button>
			</form>
		</div>
		
	</div>
</head>

<body >

	<!-- <div class="page-header">
		<center><h2>Email Reports - Send Grid</h2></center>
	</div> -->
	<div class="input-group">
	  <input type="text" id="searchBar" class="form-control" placeholder="Search by email,reason,event,invoice,PO number,...">
	  <span class="input-group-btn">
        <div class="btn-group">
            <a href="#" id="searchBtn" onclick="setSearchURL()" class="btn btn-primary" data-loading-text="Loading...">Search!</a>
        </div>
	  </span>
	</div><!-- /input-group -->

	
   
	<center>
		<div style="width:1200px;" id='grid' name='grid' class='table-responsive'>
			<div class="form-group" align="right">
			<?php 				
				if($count_rec>0){
			?>
				<ul class="pager">
					<form  role='form' method="get" action="index.php">
			        	<div class="form-group row">
			        		<li><label for='from'>From</label></li>
							<li><input type="text" name="from" id="from"></li>
							<li><label for="to">To</label></li>
							<li><input type="text" name="to" id="to"></li>
			        		<li><button type="submit" id="btnDateSearch" class="btn btn-sm" hidden>Submit</button></li>
			        	</div>
			        </form>
					&nbsp;&nbsp;&nbsp;
					<label>Records per Page</label>
					<input class="small_input " id='limit' value=<?php echo $limit ?> >
					<li><a id="records" href="?page=" onclick="setURL()" >GO</a></li>
				</ul>
			</div>

			<?php
				// echo count($cursor);
				
				// echo $count_rec;



					echo "<div id='div'><table name='gridData' id='gridData' class='table table-striped table-condensed table-bordered table-responsive' >";
					echo "<thead >";
					foreach ($display_index as $key => $value) {
						$count=0;
						if($value=='1'){
							switch ($key) {
								case 'MerchantOutletName':
									$key='Merchant Outlet';
									break;
								
								case 'PurchaseOrderNumber':
									$key = 'PO Number';
									break;

								case 'InvoiceNumber':
									$key = 'Invoice Number';
									break;
								case 'timestamp':
									$key = 'Time Stamp';
									break;
								case 'email':
									$key = 'E-mail';
									break;
								case 'event':
									$key = 'Event';
									break;
								case 'POSName':
									$key = 'POS Name';
									break;
								case 'attempt':
									$key = 'Attempt';
									break;
								case 'status':
									$key = 'Status';
									break;
								case 'reason':
									$key = 'Reason';
									break;
								case 'type':
									$key = 'Type';
									break;
								default:
									# code...
									break;
							}
							echo "<th onclick='sort_table(ppl,"."$count"." )'>".$key."</th>";
							$count=$count+1;
						}
					}
					echo "</thead><tbody id='ppl'>";
					foreach ($cursor as $document) {
						echo "<tr>";
						foreach ($display_index as $key => $value) {
							if($display_index[$key]=='1'){
								if(array_key_exists($key, $document)){
									
									if($key == 'Time Stamp'){
										// $dateTimeStamp = date(DATE_ISO8601, $document[$key]->sec);
										echo "<td>".$dateTimeStamp."</td>";
									}
									else{
										echo "<td>".$document[$key]."</td>";
									}
								}
								else {
									$string = '';
									echo "<td>".$string."</td>";
								}
							}
						}
						echo "</tr>";
					}
					echo "</tbody></table></div>";

					$total= $cursor->count();
					$page_count = ceil($total/$limit);
					echo "<ul class='pager'>";
					echo '<li class="pull-left"><a href=?page=1'.'&limit='. $limit.' > First</a>&nbsp&nbsp</li>';
					if($page > 1){
						echo '<li><a href="?page=' . $prev. '&limit='. $limit .'">Previous</a>&nbsp&nbsp</li>';
						echo "<li><input type='text' id='count' class='small_input' value=".$page."> of ".$page_count."</li>";
						?>
						<li><a href="?page=" id='link' onclick="setCurrPage()">GO</a></li>
						<?php
						if($page * $limit < $total) {
							echo ' <li><a href="?page=' . $next .'&limit='. $limit . '">Next</a>&nbsp&nbsp</li>';
						}
						else{
							echo ' <li class="btn disabled"><a href="?page=' . $next .'&limit='. $limit . '" disabled>Next</a>&nbsp&nbsp</li>';
						}
						
					} 
					else {
						echo '<li class="btn disabled" ><a href="?page=' . $prev. '&limit='. $limit .'" disabled>Previous</a>&nbsp&nbsp</li>';
						echo "<li><input type='text' id='count' class='small_input' value=".$page."> of ".$page_count."</li>";
						?>
						<li><a href="?page=" id='link' onclick="setCurrPage()">GO</a></li>
						<?php
						if($page * $limit < $total) {
							echo ' <li><a href="?page=' . $next.'&limit='. $limit .  '">Next</a>&nbsp&nbsp</li>';
						}
					}
					
					
					// <li><a href='?page=1'>GO</a></li>;
					// if($page!=$page_count){
							echo ' <li class="pull-right"><a href="?page='.$page_count .'&limit='. $limit. '">Last</a>&nbsp&nbsp</li>';
					// }
					echo "&nbsp&nbsp&nbsp&nbsp ";
					echo "</ul>";
				}
				else{
				?>
					<center>
						<div class="container"> 
							<h1>Ooops!!There are no results for your query!</h1>
						</div>
					</center>
				<?php 		
				}
			?>

		</div>
	</center>
	<!-- <input type="button" value="Export" id="export" /> -->
	<?php 
		if($count_rec>0){
	?>
	<ul class="pager">
		<li><a href="#" class="export">Export Table data into CSV</a></li>
	</ul>
	<?php } ?>
</body>

<script type="text/javascript">


	$(document).ready(function() {

		$("#btnDateSearch").hide();
		$('#from').focusout(function(){
			if($(this).val()!='' || $("#to".val()!='')){
				$("#btnDateSearch").show();
			}
			else{
				$("#btnDateSearch").hide();
			}
		});

		$("#to").focusout(function(){
			if(($(this).val()!='') || $("#from").val()!='' ){
				$("#btnDateSearch").show();
			}
			else{
				$("#btnDateSearch").hide();
			}
		});

	    $("#btnExport").click(function(e) {
	        //getting values of current time for generating the file name
	        var dt = new Date();
	        var day = dt.getDate();
	        var month = dt.getMonth() + 1;
	        var year = dt.getFullYear();
	        var hour = dt.getHours();
	        var mins = dt.getMinutes();
	        var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
	        //creating a temporary HTML link element (they support setting file names)
	        var a = document.createElement('a');
	        //getting data from our div that contains the HTML table
	        var data_type = 'data:application/vnd.ms-excel';
	        var table_div = document.getElementById('div');
	        var table_html = table_div.outerHTML.replace(/ /g, '%20');
	        a.href = data_type + ', ' + table_html;
	        //setting the file name
	        a.download = 'exported_table_' + postfix + '.xls';
	        //triggering the function
	        a.click();
	        //just in case, prevent default behaviour
	        e.preventDefault();
	    });

		$(".export").on('click', function (event) {
	        // CSV
			
	        exportTableToCSV.apply(this, [$('#div>table'), 'export.csv']);
	        
	        // IF CSV, don't do event.preventDefault() or return false
	        // We actually need this to be a typical hyperlink
	    });
	});

	$(function(){

		$("#from").datepicker({
			defaultDate: "+lw",
			// changeMonth: true,
			// changeYear: true,
			numberOfMonths:2,
			// showButtonPanel:true,
			onClose: function(selectedDate){
				$( "#from" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
				$("#to").datepicker("option","minDate",selectedDate);
				$("#from").focus();
			}
		});
		

		$("#to").datepicker({
			defaultDate: "+lw",
			// changeMonth: true,
			// changeYear: true,
			numberOfMonths:2,
			// showButtonPanel:true,
			onClose: function(selectedDate){
				$( "#to" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
				$("#from").datepicker("option","maxDate",selectedDate);
				$("#to").focus();
			}
		});
	});

	function exportTableToCSV($table, filename) {

        var $rows = $table.find('tr'),

            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

            // actual delimiter characters for CSV format
            colDelim = ',',
            rowDelim = '\r\n',

            // Grab text from table into CSV formatted string
            csv = '' + $rows.map(function (i, row) {
                var $row = $(row),
                    $cols = $row.find('th,td');

                return $cols.map(function (j, col) {
                    var $col = $(col),
                        text = $col.text();

                    return text.replace('"', '""'); // escape double quotes

                }).get().join(tmpColDelim);

            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim) + '',

            // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        $(this)
            .attr({
            'download': filename,
                'href': csvData,
                'target': '_blank'
        });
    }

    function setSearchURL(){
    	$('#searchBtn').attr('href',"?search="+$('#searchBar').val());
    }

	function setCurrPage()
	{
		$('#link').attr('href',"?page="+$('#count').val()+"&limit="+<?php echo $limit?>);
	}

	function setURL(){
		$('#records').attr('href',"?page="+<?php echo $page ?>+"&limit="+$('#limit').val());
	}

	var asc=-1;
    function sort_table(tbody, col) {
		
        var rows = tbody.rows,
            rlen = rows.length,
            arr = new Array(),
            i, j, cells, clen;
        // fill the array with values from the table
        for (i = 0; i < rlen; i++) {
            cells = rows[i].cells;
            clen = cells.length;
            arr[i] = new Array();
            for (j = 0; j < clen; j++) {
                arr[i][j] = cells[j].innerHTML;
            }
        }
        // sort the array by the specified column number (col) and order (asc)
        arr.sort(function (a, b) {
            return (a[col] == b[col]) ? 0 : ((a[col] > b[col]) ? asc : -1 * asc);
        });
        // replace existing rows with new rows created from the sorted array
        for (i = 0; i < rlen; i++) {
            rows[i].innerHTML = "<td>" + arr[i].join("</td><td>") + "</td>";
        }
		if(asc==1)asc=-1;
		else asc=1;
    }

</script>



