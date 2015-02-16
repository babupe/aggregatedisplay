<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Real Time Graphing</title>
</head>

<body>
<div style='font-size: 40pt; background-color:DarkGray;'></div>
<div class="middiv" id="middivid">
  <p>&nbsp;</p>
  <form id="getgraph" name="getgraph" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
  	<p align="center">SWRevision
       <select name="swrevid" size="1" id="swrevid" form="getgraph" title="SWRevisionId">
        <option value="1.2.3">1.2.3</option>
        <option value="2.3.4">2.3.4</option>
        <option value="3.4.5">3.4.5</option>
        <option value="4.5.6">4.5.6</option>
        <option value="5.6.7">5.6.7</option>
        <option value="6.7.8">6.7.8</option>
      </select>
    </p>
    <p align="center">LogType
       <select name="logtyp" size="1" id="logtyp" form="getgraph" title="logtype">
        <option value="crash">crash</option>
        <option value="notif">notif</option>
        <option value="download">download</option>
      </select>
    </p>
    <p align="center">
      <label for="textfield">Start Time   </label>
      <input type="number" name="starttime" id="starttime" required>
      <select name="agounit" size="1" autofocus="autofocus" id="agounit" form="getgraph" title="agounits">
        <option value="minutes">minutes ago</option>
        <option value="hours">hours ago</option>
        <option value="days">days ago</option>
      </select>
    </p>
   <p align="center">End Time
       <input name="endtime" type="number" id="endtime" value="0" required>
       <select name="endunit" size="1" id="endunit" form="getgraph" title="endunits">
        <option value="minutes">minutes ago</option>
        <option value="hours">hours ago</option>
        <option value="days">days ago</option>
      </select>
    </p>
    <p align="center">
      <input type="submit" name="submit" id="submit" value="Submit">
    </p>
  </form>
<?php
	include('phpgraphlib.php');
	require 'aws-php-sample/vendor/autoload.php';
	use Aws\DynamoDb\DynamoDbClient;
	if(isset($_POST['submit'])) {
		$swrevid = $_POST["swrevid"];
		$logtype = $_POST["logtyp"];
		if ( $_POST["agounit"] == 'hours' ) {
        		$starttime = intval($_POST["starttime"]) * 60;
        	}
        	elseif ( $_POST["agounit"] == 'days' ) {
                	$starttime = intval($_POST["starttime"]) * 24 * 60;
       		}
        	else {
                	$starttime = $_POST["starttime"];
        	}
        	$agodiffstring = "-".$starttime." minutes";
        	$agodate = date('Y-m-d-H-i-s',strtotime($agodiffstring));
        	if ( $_POST["endunit"] == 'hours' ) {
        		$endtime = intval($_POST["endtime"]) * 60;
        	}
        	elseif ( $_POST["endunit"] == 'days' ) {
                	$endtime = intval($_POST["endtime"]) * 24 * 60;
        	}
        	else {
           		$endtime = $_POST["endtime"];
        	}
        	$enddiffstring = "-".$endtime." minutes";
        	$enddate = date('Y-m-d-H-i-s',strtotime($enddiffstring));	
		if ( $enddate <= $agodate ) {
			echo "<p align=\"center\" style=\"color:red\">";
			echo "End Date should be greater than Start Date";
		}
		if ( $enddate > $agodate ) {
			if ( $starttime-$endtime > 1382 ) {
				echo "<p align=\"center\" style=\"color:red\">";
                        	echo "Choose a date range smaller than 1 day!";
			}
			else {
				$agodate = substr($agodate,0,-2);
				$agodate = explode("-",$agodate);
				$agodate = intval(implode("",$agodate));
				$enddate = substr($enddate,0,-2);
				$enddate = explode("-",$enddate);
				$enddate = intval(implode("",$enddate));
				$client = DynamoDbClient::factory(array('region' => 'us-east-1'));
				$iterator = $client->getIterator('Query', array(
    						'TableName'     => 'tblswrevgrouping',
    						'KeyConditions' => array(
        						'swrevidLogtype' => array(
            							'AttributeValueList' => array(
                							array('S' => $swrevid."|".$logtype)
            								),
            								'ComparisonOperator' => 'EQ'
        							),
        						'aggdate' => array(
            							'AttributeValueList' => array(
                							array('N' => $agodate),
									array('N' => $enddate)
            								),
            								'ComparisonOperator' => 'BETWEEN'
							)
						)
					));
				$itemarr = array();
				foreach ($iterator as $item) {
					$aggdtval = substr_replace(substr_replace(substr_replace(substr_replace($item['aggdate']['N'],"-",4,0),"-",7,0)," ",10,0),":",13,0);
					$itemarr[$aggdtval] = $item['count']['N'];
				}
				if ( count($itemarr) < 1 ) {
					echo "<p align=\"center\" style=\"color:red\">";
                                	echo "No data found for this time range!!";
				}
				else {
					echo "<div id=abc>";
					echo "<p align=\"center\">";
?>
					<img src = "displaygraph.php?mydata=<?php echo urlencode(serialize($itemarr)); ?>" />
<?php
					echo "</div>";
				}
			}
		}
	}
?>
  <p>&nbsp;</p>
</div>

</body>
</html>
