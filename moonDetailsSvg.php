<?php
header('Content-type: image/svg+xml');

include "./config.php";

$data = Array(
        'date' => Date("m/d/y H:i"),
        'error' => "init"
);

$mysqli = new mysqli($HOST, $USER, $PASS, $DB);
if ($mysqli->connect_errno) {
    $data['error'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
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
		$data['Moon'][$key] = Array (
			"value" => $value,
			"created" => $created,
			"modified" => $modified
		);
		$data['error'] = "";
	}
	$stmt->close();

	$mysqli->close();
}

$xdoc = new DomDocument;
$xdoc->Load('moonDetailsDiag.svg');


$age = $data['Moon']['age']['value'];
$distance =  $data['Moon']['distance']['value'];
$illum = $data['Moon']['illum']['value'];

if (array_key_exists("illum",$_REQUEST)) {
	$illum = $_REQUEST['illum'];
	#print "illum set: $illum\n";
}
if (array_key_exists("age",$_REQUEST)) {
	$age = $_REQUEST['age'];
}
if (array_key_exists("distance",$_REQUEST)) {
	$distance = $_REQUEST['distance'];
}
$s=0.0;
if (array_key_exists("s",$_REQUEST)) {
	$s = $_REQUEST['s'];
}
$phaseDateList=split(",",$data['Moon']['phaseOrder']['value']);
if (array_key_exists("phases",$_REQUEST)) {
	$phaseDateList = split(",",$_REQUEST["phases"]);
}

$cx = 150;
$cy = 250;

# from the moonSvg.php

$background =Array ( 'color' => 'black' );

if ($age<15) {
	# this is pre full moon
	if ($illum<0.5) {

		$rightArc=Array (
			'color'=> 'black',
			'translate'=> ($cx*2*$illum),
            'scale'=> (-2.0*$illum)+1.0
		);
		$background['color'] = 'white';

		$leftArc = Array (
			'color' => 'black',
		);

	} else {
		# illum > 0.5
		$rightArc = Array (
			'color' => "white"
		);
		$leftArc = Array (
			'color' => 'white',
			'translate'=> ($cx*-2*$illum)+($cx*2),
			'scale'=> (2.0*$illum)-1.0
		);
	}
} else {
	# $age >15
	# this is post-full moon
	if ($illum<0.5) {
		$rightArc = Array (
			'color' => "black"
		);
		$background['color'] = 'white';
		$leftArc = Array (
			'color' => 'black',
			'translate'=> ($cx*2*$illum),
			'scale'=> (-2.0*$illum)+1.0
		);
	} else {
		#$illum > 0.5
		$rightArc = Array (
			'color' => "white",
			'translate'=> ($cx*-2*$illum)+($cx*2),
			'scale'=> (2.0*$illum)-1.0
		);
		$leftArc = Array (
			'color' => 'white'
		);
	}
}

# calculate the new dx, and dy bassed on illumination of 0->1 for 0% to 100%
# dx = 90*SIN($illum*2*pi()) + $cx
# dy = 90*COS($illum*2*pi()) + $cy
$dx = 90.0*sin($illum*2.0*pi());
$dy = 90.0-(90*cos($illum*2.0*pi()));
$dir = 0;
if ($illum>0.5) {
	$dir = 1;
}
#$newDxDy = Array( "$dx,$dy" );
$end = (($illum*2.0)+1.5)*pi();

$nodes = $xdoc->getElementsByTagName('path');
foreach($nodes as $path) {
	if ($path->GetAttribute('id') == 'path3760') { # the green arc
		# need to extract d "M 150,160 ..... dx,dy"
		$dValues = split(" ",$path->GetAttribute('d'));

		#print var_dump($dValues);
		$dValues = array_splice ( $dValues,0,5);
		#print var_dump($dValues);
		$path->SetAttribute('d',sprintf("%s %s 1 %s",
			join($dValues," "), 
			$dir,
			"$dx,$dy"
		));
		$path->SetAttribute('sodipodi:end',$end);
	}
	if ($path->GetAttribute('id') == 'leftArc') {
		#$transform = $arc->GetAttribute('transform');
		if (array_key_exists('translate',$leftArc)) {
			$path->SetAttribute('transform',sprintf("translate(%s,0) scale(%s,1)",
				$leftArc['translate'],
				$leftArc['scale']
			));
		}
		$path->SetAttribute('style',sprintf("stroke: black; fill: %s",
			$leftArc['color']
		));
	}
	if ($path->GetAttribute('id') == 'rightArc') {
		#$transform = $arc->GetAttribute('transform');
		if (array_key_exists('translate',$rightArc)) {
			$path->SetAttribute('transform',sprintf("translate(%s,0) scale(%s,1)",
				$rightArc['translate'],
				$rightArc['scale']
			));
		}
		$path->SetAttribute('style',sprintf("stroke: black; fill: %s",
			$rightArc['color']
		));
	}
	if ($path->GetAttribute('id') == 'arcArrowDec') {
		$path->SetAttribute('transform',sprintf("rotate(%s,150,822)",
			(360.0*$illum)
		));
	}
}

$nodes = $xdoc->getElementsByTagName('circle');
foreach($nodes as $circle) {
	if ($circle->GetAttribute('id') == 'background') {
		$circle->SetAttribute('style',sprintf("stroke: black; fill: %s",
			$background['color']
		));
	}
}



$nodes = $xdoc->getElementsByTagName('tspan');
foreach($nodes as $tspan) {
	if ($tspan->GetAttribute('id') == 'tspan3793') { 
		$tspan->nodeValue = sprintf("%0.2f%%", $illum*100);
	}
	if ($tspan->GetAttribute('id') == 'tspan3793-4') {
		$tspan->nodeValue = sprintf("%0.2f", $distance);
	}
	if ($tspan->GetAttribute('id') == 'tspan3983') {
		$tspan->nodeValue = sprintf("%0.2f", $age);
	}
	if (preg_match('/phaseDateText-(\d)/',$tspan->GetAttribute('id'),$matches)) {
		// $phaseDateList[$matches[1]] = timeInSeconds=phase
		list($time, $phase) = split("=",$phaseDateList[$matches[1]]);
		$tspan->nodeValue = sprintf("%s", strftime("%a %b %e %H:%M" ,$time));
	}
	if (preg_match('/tspan-(rise|set)-(\d)/',$tspan->GetAttribute('id'),$matches)) {
		$riseSetTimes=split(",",$data['Moon']['riseSet']['value']);
		if ($matches[1] == "rise"){
			$time = $riseSetTimes[$matches[2]];
		} else {
			$time = $riseSetTimes[$matches[2]+3];
		}
		$tspan->nodeValue = sprintf("%s", strftime("%H:%M" ,$time));
	}
}

echo $xdoc->saveXML();

?>
