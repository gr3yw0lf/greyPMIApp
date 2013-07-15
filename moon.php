<!DOCTYPE HTML>
<html>
<head>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-status-bar-style" content="black">

<link rel="apple-touch-icon" href="moon-icon-iphone.png" />
<link rel="apple-touch-icon" sizes="72x72" href="moon-icon-72-72.png" />
<link rel="apple-touch-icon" sizes="114x114" href="moon-icon-114-114.png" />
<link rel="apple-touch-icon" sizes="144x144" href="moon-icon-144-144.png" />
<link rel="apple-touch-startup-image" href="startup.png">


<link rel="stylesheet" type="text/css" href="../includes/jquery-ui-1.10.2/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="./moon.iphone5.css">
<script type="text/javascript" src="../includes/jquery-1.9.1.js"></script>
<script type="text/javascript" src="../includes/jquery-ui-1.10.2/ui/jquery-ui.js"></script>
<script type="text/javascript" src="./moon.js"></script>

</head>
<body>
<?php

include "./config.php";

$data = Array( 
		'date' => Date("m/d/y H:i"),
		'error' => "init"
);

$mysqli = new mysqli($HOST, $USER, $PASS, $DB);

if ($mysqli->connect_errno) {
	$data['error'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
	
	// $data["req"] = $_REQUEST;
	
	if (array_key_exists('_', $_REQUEST)) {
		
		$stmt = $mysqli->prepare("
				SELECT 
				 data.id, data.data_type_id, data.created, data.modified, data.key, data.value
				FROM 
				 `data` 
				join 
				 `data_types` as dt 
				where 
				 data.data_type_id=dt.id 
				and 
				 dt.name='Moon'
		");

		$stmt->execute();
		$stmt->bind_result($id,$tId,$created,$modified,$key,$value);

		while ($stmt->fetch()) {
			printf ("<!-- %s -->\n", join(",",array($id,$tId,$created,$modified,$key,$value)));
			$data['Moon'][$key] = Array (
				"value" => $value,
				"created" => $created,
				"modified" => $modified
			);
			$data['error'] = "";
		}
		$stmt->close();
	}
	
	$mysqli->close();
}

?>

<h1>Moon Data</h1>

<div id="date"><?php echo $data['Moon']['phase']['modified']; ?></div>
<br>
<?php
	//printf('<embed src="svg.php?phase=%s&fullMoon=%s" type="image/svg+xml" />',
	$phaseDates=Array();
	foreach (split(",",$data['Moon']['phaseOrder']['value']) as $item) {
		array_push($phaseDates, $data['Moon'][$item]['value']);
	}
	printf('<object width=295 height=215 data="svg.php?phase=%s&phases=%s" type="image/svg+xml"></object>',
		$data['Moon']['phase']['value']*100,
		urlencode(implode(",",$phaseDates))
	);
	print "<br>";
	foreach (Array( 
		'distance', 
		'iconNumber', 
		'age', 
		'sunAngle', 
		'sunDistance', 
		'angle', 
		'illum' 
		) as $item) {
		printf("%s: %s<br>\n",
			$item,
			$data['Moon'][$item]['value']
		);
	}
	foreach (split(",",$data['Moon']['phaseOrder']['value']) as $item) {
		printf("<br>%s: %s\n",
			$item,
			$data['Moon'][$item]['value']
		);
	}
?>

<hr>
<?php echo $data['error']; ?>
<br>

</body>
</html>
