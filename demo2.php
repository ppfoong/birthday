<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;" />
	<meta charset="UTF-8">
	<meta name="author" content="P.P. Foong">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Demo of birthday library functions</title>
</head>
<body>
	<p><div style="text-align:center;">
		<form action="" method="get">
			Input age range for the search:<br>
			<label for="age1">From (0-200):</label>
			<input type="number" id="age1" name="age1" min="0" max="200" />
			<label for="age2">To (0-200):</label>
			<input type="number" id="age2" name="age2" min="0" max="200" />
			<br><br>Birthday:<br>
			<label for="b_month">Month:</label>
			<input type="number" id="b_month" name="b_month" min="1" max="12" />
			<label for="b_day">Day:</label>
			<input type="number" id="b_day" name="b_day" min="1" max="31" />			
			<br><br><i>Note: All the above fields are optional to fill in.</i><br><br>
			<label for="animal">Symbolic animal for year matching:</label>
			<select name="animal" id="animal">
			  <option value="0">Rat</option>
			  <option value="1">Ox</option>
			  <option value="2">Tiger</option>
			  <option value="3">Hare</option>
			  <option value="4">Dragon</option>
			  <option value="5">Snake</option>
			  <option value="6">Horse</option>
			  <option value="7">Sheep</option>
			  <option value="8">Monkey</option>
			  <option value="9">Rooster</option>
			  <option value="10">Dog</option>
			  <option value="11">Boar</option>
			</select>
			<br><br><input type="submit">
		</form>

<?php
	require_once('lib_birthday.php');
	
	$animal = ['Rat','Ox','Tiger','Hare','Dragon','Snake','Horse','Sheep','Monkey','Rooster','Dog','Boar'];
	if (!empty($_GET)) {
		$age2 = isset($_GET['age2'])?(int)$_GET['age2']:0;
		if (isset($_GET['age1'])) {
			$age1 = (int)$_GET['age1'];
			if ($age2 == 0) {
				$age2 = $age1;
			}
		} else {
			$age1 = $age2;
		}
		$b_month = isset($_GET['b_month'])?(int)$_GET['b_month']:1;
		if (($b_month < 1) || ($b_month > 12)) {
			$b_month = 1;
		}
		$b_day = isset($_GET['b_day'])?(int)$_GET['b_day']:1;
		if (!checkdate($b_month,$b_day,2000))  {
			$b_day = 1;
		}
		$animalIndex = isset($_GET['animal'])?(int)$_GET['animal']:0;
		$today = todayYMD();
		$thisYear = $today[0];
		$thisMonth = $today[1];
		$thisDay = $today[2];
		echo "<br>Today: ".$thisYear."-".$thisMonth."-".$thisDay;
		echo "<br>Age range: ".$age1." to ".$age2;
		echo "<br>Birthday (mm/dd): ".$b_month."/".$b_day;
		echo "<br><br>";
		$years = getYearsByAge($age1,$age2,$b_month,$b_day);
		echo "Year range of the age range is from ".$years[0]." to ".$years[1];
		$results = getAnimalYearsByAge($animalIndex,$age1,$age2,$b_month,$b_day);
		echo "<br><br>".$animal[$animalIndex]. " year(s) within the age range: ";
		$total = count($results);
		if ($total == 0) {
			echo "none";
		} else {
			for ($i = 0; $i < $total; $i++) {
				echo $results[$i]." ";
			}
		}
		echo "<br><br>The information below is gotten from different function, and should have the same result as above.";
		$results = getAnimalYearsByRange($animalIndex,$years[0],$years[1]);
		echo "<br>".$animal[$animalIndex]. " year(s) between ".$years[0]." and ".$years[1].": ";
		$total = count($results);
		if ($total == 0) {
			echo "none";
		} else {
			for ($i = 0; $i < $total; $i++) {
				echo $results[$i]." ";
			}
		}
	}

?>

	</div></p>
</body>
</html>
