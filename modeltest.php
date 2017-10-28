<?php

define("DEF", "\033[1;37m");
define("GRY", "\033[0;37m");
define("RED", "\033[1;31m");
define("YEL", "\033[1;33m");
define("BLU", "\033[1;34m");
define("GRN", "\033[1;32m");

define("SILENT", array_search("--silent", $argv) !== false);

function syscall($cmd) {
    if (!SILENT) echo DEF . "    < " . BLU . $cmd . "\n";
	$output = [];
    echo GRY;
    list($cmdName, $params) = explode(" ", $cmd, 2);
    exec("$cmdName $params". (SILENT ? "" : " 2>&1"), $output, $retval);
    $lastLine = '';
    foreach($output as $outLine) {
        if (!SILENT) echo DEF . "    > " . YEL . $outLine . "\n";
        $lastLine = $outLine;
    }

    if ($retval !== 0) {
        echo DEF;
        die ("$cmdName returned error code $retval\n");
    }
    return $lastLine;
}

function compile() {
    if (!SILENT) echo DEF . "Compiling model..\n";
	syscall("mcrl22lps trio_v2.mcrl2 trio_v2.lps --lin-method=stack");
	echo GRN . "Compiling complete\n\n";
}

function testReq($mcfName) {
    if (!SILENT) echo DEF . "Testing requirement $mcfName..\n";
	syscall("lps2pbes -f $mcfName.mcf trio_v2.lps $mcfName.pbes");
	$lastLine = syscall("pbes2bool $mcfName.pbes");
	if ($lastLine != "true") {
		echo RED . "$mcfName invalid\n";
	} else {
		echo GRN . "$mcfName valid\n";
	}
    if (!SILENT) echo "\n";
}

function showGraph() {
    if (!SILENT) echo DEF . "Generating reachable states..\n";
    syscall("lps2lts trio_v2.lps trio_v2.aut");
    if (!SILENT) echo DEF . "Displaying graph..\n";
    syscall("ltsgraph trio_v2.aut");
    if (!SILENT) echo GRN . "Graph viewer closed\n";
}

$reqs = [];

if (count($argv) <= 1) {
	for($n = 1; $n <= 13; $n += 1) {
		$reqs[] = "systemreq" . $n;
	}
}

compile();

if (count($argv) == 2 && $argv[1] == 'graph') {
    showGraph();
    echo DEF . "DONE.\n";
    die;
}

foreach($argv as $index => $arg) {
	if (preg_match("/^[0-9]+\-[0-9]+$/", $arg)) {
		list($from, $to) = explode("-", $arg, 2);
		for($n = $from; $n <= $to; $n += 1) {
			$reqs[] = "systemreq" . $n;
		}
	} elseif ($index > 0 && is_numeric($arg)) {
		$reqs[] = "systemreq" . $arg;
	}
}

foreach($reqs as $req) {
	testReq($req);
}

echo DEF . "DONE.\n";

