<?php
header('Content-type: image/svg+xml');

$xdoc = new DomDocument;
$xdoc->Load('moonDetailsDiag.svg');


$illum = 0.0;
$age = 0.0;
$distance = 0.0;
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

$cx = 150;
$cy = 250;
#$cx=100;
#$cy=100;

$nodes = $xdoc->getElementsByTagName('path');

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
}

echo $xdoc->saveXML();

?>
