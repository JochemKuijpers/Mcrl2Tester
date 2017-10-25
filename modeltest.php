<?php

define("DEF", "\033[1;37m");
define("RED", "\033[1;31m");
define("YEL", "\033[1;33m");
define("BLU", "\033[1;34m");
define("GRN", "\033[1;32m");

function syscall($cmd) {
	echo DEF . "    < " . BLU . $cmd . "\n";
	$output = [];
	exec($cmd . " 2>&1", $output, $retval);
	$lastLine = '';
	foreach($output as $outLine) {
		echo DEF . "    > " . YEL . $outLine . "\n";
		$lastLine = $outLine;
	}
	return $lastLine;
}

function compile() {
	echo DEF . "Compiling model..\n";
	syscall("mcrl22lps trio_v2.mcrl2 trio_v2.lps --lin-method=stack");
	echo GRN . "Compiling complete\n\n";
}

function testReq($mcfName) {
	echo DEF . "Testing requirement $mcfName..\n";
	syscall("lps2pbes -f $mcfName.mcf trio_v2.lps $mcfName.pbes");
	$lastLine = syscall("pbes2bool $mcfName.pbes");
	if ($lastLine != "true") {
		echo RED . "$mcfName invalid\n";
	} else {
		echo GRN . "$mcfName valid\n";
	}
	echo "\n";
}

$reqs = [];

if (count($argv) <= 1) {
	for($n = 1; $n <= 13; $n += 1) {
		$reqs[] = "systemreq" . $n;
	}
}

foreach($argv as $index => $arg) {
	if (strpos($arg, "-") !== false) {
		list($from, $to) = explode("-", $arg, 2);
		for($n = $from; $n <= $to; $n += 1) {
			$reqs[] = "systemreq" . $n;
		}
	} elseif ($index > 0) {
		$reqs[] = "systemreq" . $arg;
	}
}

compile();

foreach($reqs as $req) {
	testReq($req);
}

echo DEF . "DONE.\n";

