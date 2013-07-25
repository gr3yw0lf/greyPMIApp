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
}

echo $xdoc->saveXML();

?>
