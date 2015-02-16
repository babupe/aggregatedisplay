<?php
include("phpgraphlib.php");
$graph = new PHPGraphLib(1600,800);
$data = unserialize(urldecode(stripslashes($_GET['mydata'])));
$graph->setBackgroundColor("black");
$graph->addData($data);
$graph->setBarColor('255,255,204');
$graph->setTitle("Data Aggregate Graph");
$graph->setupYAxis(12, 'yellow');
$graph->setupXAxis(20, 'yellow');
$graph->setGrid(false);
$graph->setGradient('silver', 'gray');
$graph->setBarOutlineColor('white');
$graph->setTextColor('white');
$graph->setLineColor('yellow');
$graph->createGraph(); 
?>
