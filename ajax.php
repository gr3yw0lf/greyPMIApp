<?php

include "./config.php";

$debug = false;
$auth = false;

$data = Array( 'date' => Date("m/d/y H:i"));

$mysqli = new mysqli($HOST, $USER, $PASS, $DB);

if ($mysqli->connect_errno) {
	$data["error"] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
	
	if ($debug) {
		$data['debug']['request'] = $_REQUEST;
	}

	
	if (array_key_exists('data', $_REQUEST)) {	
		#$jsonObj = json_decode(file_get_contents('php://input'));
		$jsonObj = json_decode($_REQUEST['data']);
		
		if (isset($jsonObj->{'auth'})) {
			$auth = checkSendAuth($jsonObj->{'auth'});
		}
		$data['authStatus'] = $auth;
		if (!$auth) {
			echo json_encode($data);
			exit;
		}

		// if we made it here, checkSendAuth returned true
		
		// check to see if debug was requested
		if (array_key_exists('debug',$jsonObj)) {
			# this is a string, not a bool
			if ($jsonObj->{'debug'} == "true") {
				$debug = true;
			}
			$data['debug']['requested'] = $debug;
		}

		if (array_key_exists('dataType',$jsonObj)) {
			
			// obtain the sql data types
			$sStmt = $mysqli->prepare("
					SELECT id,name
					FROM data_types;
			");
			$sStmt->execute();
			$sStmt->bind_result($id, $name);
			
			while ($sStmt->fetch()) {
					$mapDataTypeNameToId[$name] = $id;
			}
			$sStmt->close();

			// obtain the list of items in data
			$sStmt = $mysqli->prepare("
					SELECT id,data_type_id,`key`
					FROM data
		    ");
			$sStmt->execute();
			$sStmt->bind_result($id,$dataTypeId,$key);
			while ($sStmt->fetch()) {
				$mapDataTypeKeyToId[$dataTypeId][$key] = $id;
				if ($debug) {
					$data['debug']['mapDataTypeKeyToId'][$dataTypeId][$key] = $id;
				}
		    }
			$sStmt->close();

			if ($debug) {
				$data['debug']['valuesPassed']['dataType'] = $jsonObj->{'dataType'};
			}

			// prepare the insert query
			$insertStatement = $mysqli->prepare("
				INSERT INTO `data` (created,modified,data_type_id,valid,value,`key`)
				VALUES (now(),now(),?,?,?,?)
			"); 
			//  key and value inverted to make the bind params the same for insert and update
			$insertStatement->bind_param("iiss", $data_dataTypeId, $data_valid, $data_value, $data_key);
			// prepare the update query
			$updateStatement = $mysqli->prepare("
				UPDATE data SET modified=NOW(), data_type_id=?, valid=?, value=? WHERE `key`=?
			");
			$updateStatement->bind_param("iiss", $data_dataTypeId, $data_valid, $data_value, $data_key);

			// common elements per key passed (only one data type per json post)
			$data_valid = 1;
			$data_dataTypeId = $mapDataTypeNameToId[$jsonObj->{'dataType'}];

			foreach ($jsonObj->{'keys'} as $data_key => $data_value) {

				if (!isset($mapDataTypeKeyToId[$data_dataTypeId][$data_key])) {
					if ($debug) {
						$data['debug'][$data_dataTypeId][$data_key]['new'] = $data_value;
					}
					$insertStatement->execute();
				} else {
					if ($debug) {
						$data['debug'][$data_dataTypeId][$data_key]['update'] = $data_value;
					}
					$updateStatement->execute();
				}
			}
			$insertStatement->close();
			$updateStatement->close();
		} else {
			$data['error'] = "No datatype";
		}
	} 
	
	$mysqli->close();
}

echo json_encode($data);
?>
