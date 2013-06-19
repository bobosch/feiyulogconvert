<?php
require_once('fpv.php');

if(isset($_GET['fpv'])){
	highlight_file('fpv.php');
	exit;
}

if(isset($_GET['test'])){
// 	$name='FEIYU036';
	$name='06131902';
// 	$name='airsig';
	$feiyu=new feiyu();
	$feiyu->setOutputFormat('ass');
	$feiyu->convert($name.'.txt');
	file_put_contents($name.'.ass',$feiyu->output);
	exit;
}

if($_FILES['file']){
	if(is_uploaded_file($_FILES['file']['tmp_name'])){
		$feiyu=new feiyu();
		$feiyu->setOutputFormat($_POST['output']);
		$feiyu->convert($_FILES['file']['tmp_name']);
		if($feiyu->errors){
			echo '<pre>';
			var_dump($feiyu->errors);
			echo '</pre>';
		}else{
			header('Content-Type: application/vnd.google-earth.kml+xml');
			header('Content-Disposition: attachment; filename="'.pathinfo($_FILES['file']['name'],PATHINFO_FILENAME).'.'.$feiyu->getOutputFormat().'"');
			echo $feiyu->output;
			exit;
		}
	}
}
?>

<html>
<head>
	<style>
		input[type="file"],input[type="submit"] {display:block;}
	</style>
</head>

<body>
	<h1>Feiyu log converter</h1>
	<h2>Log file upload</h2>
	<p>Please upload a feiyu log file with gps coordinates. You will receive a kml file with track path and fly animation for google earth.</p>
	<form action="index.php" enctype="multipart/form-data" method="post">
		<input name="file" type="file" />
		<input name="output" type="radio" value="kml" checked="checked" />Google KML File<br />
		<input name="output" type="radio" value="csv" />CSV File
		<input type="submit" />
	</form>
	<h2>Thanks to</h2>
	<ul>
		<li>NorCalMatCat <a href="http://www.rcgroups.com/forums/showthread.php?t=1664005">http://www.rcgroups.com/forums/showthread.php?t=1664005</a></li>
		<li>Feiyu Tech <a href="The%20Intuduction%20of%20OSD%20recording%20files.pdf">The Intuduction of OSD recording files.pdf</a></li>
		<li>Open Source <a href="index.php?fpv">My class feiyu</a></li>
	</ul>
	<p>Feedback to <a href="mailto:fpv@bobosch.de">fpv@bobosch.de</a></p>
</body>
</html>
