<?php
header('Content-type: image/svg+xml');

$xdoc = new DomDocument;
$xdoc->Load('moonDiag.svg');

$nodes = $xdoc->getElementsByTagName('path');

$illum = 0.0;
$age = 0.0;
if (array_key_exists("illum",$_REQUEST)) {
	$illum = $_REQUEST['illum'];
	#print "illum set: $illum\n";
}
if (array_key_exists("age",$_REQUEST)) {
	$age = $_REQUEST['age'];
}
$s=0.0;
if (array_key_exists("s",$_REQUEST)) {
	$s = $_REQUEST['s'];
}

$cx = 51;
$cy = 50;
#$cx=100;
#$cy=100;

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


foreach($nodes as $arc) {
	if ($arc->GetAttribute('id') == 'leftArc') {
		#$transform = $arc->GetAttribute('transform');
		if (array_key_exists('translate',$leftArc)) {
			$arc->SetAttribute('transform',sprintf("translate(%s,0) scale(%s,1)",
				$leftArc['translate'],
				$leftArc['scale']
			));
		}
		$arc->SetAttribute('style',sprintf("stroke: black; fill: %s",
			$leftArc['color']
		));
	}
	if ($arc->GetAttribute('id') == 'rightArc') {
		#$transform = $arc->GetAttribute('transform');
		if (array_key_exists('translate',$rightArc)) {
			$arc->SetAttribute('transform',sprintf("translate(%s,0) scale(%s,1)",
				$rightArc['translate'],
				$rightArc['scale']
			));
		}
		$arc->SetAttribute('style',sprintf("stroke: black; fill: %s",
			$rightArc['color']
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

echo $xdoc->saveXML();

?>
