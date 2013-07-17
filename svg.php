<?php
header('Content-type: image/svg+xml');

$xdoc = new DomDocument;
$xdoc->Load('svg.svg');

$nodes = $xdoc->getElementsByTagName('text');

$phaseList = Array ();
if (array_key_exists("phases",$_REQUEST)) {
	$phaseList = split(",",urldecode($_REQUEST['phases']));
}

foreach($nodes as $textNode) {
	if ($textNode->GetAttribute('id') == 'phase') {
		$textNode->nodeValue = sprintf("%.2d%%", $_REQUEST['phase']);
	} else {
		if (preg_match ( "/phase(\d)/",$textNode->GetAttribute('id'), $matches)) {
			if (isset($phaseList[$matches[1]-1])) {
				$textNode->nodeValue = $phaseList[$matches[1]-1];
			}
		}
	}
}

echo $xdoc->saveXML();

?>
