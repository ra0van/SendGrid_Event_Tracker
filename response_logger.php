<?php
	$data = file_get_contents("php://input");
	$records = json_decode($data,true);
	file_put_contents('a.txt',file_get_contents("php://input"));
	$conn = new Mongo();
	$dbName = $conn->selectDB('test');
	$collection  = $dbName->response;

	$fh = fopen('log.log', 'a');
	
	date_default_timezone_set('Asia/Calcutta');
	$date = date('Y-m-d H:i:s');
	
	fwrite($fh, 'log'.$date.':[');
	

	$details = array();
	
	foreach ($records as $record_name => $record) {
		fwrite($fh, " { ".PHP_EOL);
		if(is_array($record)){
			foreach ($record as $value_name => $value) {
				if(is_array($value)){
					$dup_details = array();
					fwrite($fh, $value_name.':'.'{');
					foreach ($value as $first => $last) {
						if($first=='timestamp'){
							$date = date('Y-m-d H:i:s');
							$last = $date;
						}
						$dup[$first] = $last;
						fwrite($fh, $first."=>".$last);
					}
					if($value_name=='sg_message_id'){
						$value_name = '_id';
					}
					$details[$value_name] = $dup_details;
					fwrite($fh, '}');
				}
				else{
					if($value_name=='sg_message_id'){
						$value_name = '_id';
					}
					if($value_name=='timestamp'){
						$date = date('Y-m-d H:i:s');
						$value = $date;
					}
					$details[$value_name] = $value;
					fwrite($fh, $value_name."=>".$value.",");
				}
			}
		}
		else{
			$details[$record_name] = $record;
			fwrite($fh, $record_name."=>".$record.",");
		}
		fwrite($fh, " }".PHP_EOL);
		// $getData = $collection->findOne(array('_id' => $details['_id']);
		$collection->save($details);
	}
	fwrite($fh, "] "."end of request ".PHP_EOL);
	fclose($fh);
?>