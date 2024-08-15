<?php
// This is a PHP library containing functions related to birthday, such as:
// 1. Getting the Zodiac sign (星座) of a date
// 2. Getting the symbolic animal (生肖) of a date
// 3. Calculating the age (年龄)
// 4. Calculating the years matching the symbolic animal within a range of years (or ages)
// 5. Calculating the year of birth from actual age, where age increment is on the birthday
// 6. Calculating no. of days since born
// 7. Calculating no. of days to the next birthday
// 8. Calculating no. of days from the last birthday
// 9. Check whether this year's birthday has already passed
// 10. Check whether a date has passed Chinese New Year of the year
// 11. Getting the Chinese New Year date of a year (range from 1876 to 2163)
//
// Author: P.P. Foong (https://www.linkedin.com/in/ppfoong/)
// Repository: https://github.com/ppfoong/lib_birthday
// License: The MIT License
// Version 2.11 (2024-08-12)
//
// function getZodiac($month, $day, $lang)
//		Note: Set $lang to 1 for English, 2 for Simplified Chinese, 3 for Traditional Chinese,
//							4 for Simplified Chinese (alternative naming), 5 for Traditional Chinese (alternative naming)
//		Sample usage: echo getZodiac(8,31,1);
//		Sample usage: echo getZodiac(8,31);			// for English
//
// function getAnimal($year, $month, $day, $lang)
//		Note: Set $lang to 1 for English, 2 for Simplified Chinese, 3 for Traditional Chinese, 
//							4 for Simplified Chinese with Earthly Branch (地支), 5 for Traditional Chinese with Earthly Branch (地支)
//		Note: Year range from 1876 to 2163
//		Sample usage: echo getAnimal(2000,8,31,1);
//		Sample usage: echo getAnimal(2000,8,31);	// for English
//
// function getAge($year, $month, $day, $opt)
//		Note: Set $opt to 0 for years only, 1 to include years, months and days, 2 for number of days
//		Note: Returned year will be negative for birthday in the future
//		Sample usage: echo getAge(2000,8,31,0);
//		Sample usage: print_r(getAge(2000,8,31,1));
//		Sample usage: echo getAge(2000,8,31,2);
//
// function getYearsByAge($age1, $age2, $month, $day)
//		Note: Return an array containing the start year and end year that matches with the age range
//		Note: $month and $day are optional. Default to 1/1 if not defined.
//		Sample usage: print_r(getYearsbyAge(18,22,8,31));
//
// function getAnimalYearsByRange($animalIndex, $year1, $year2,$month,$day)
//		Note: Return an array containing the years that match with the symbolic animal between $year1 and $year2
//		Note: $month and $day are optional. Default to 1/1 if not defined.
//		Sample usage: print_r(getAnimalYearsByRange(4,2000,2020,8,31));
//
// function getAnimalYearsByAge($animalIndex, $age1, $age2, $month, $day)
//		Note: Return an array containing the years that match with the symbolic animal within the age range. Age is determined by the birthday date.
//		Note: $month and $day are optional. Default to 1/1 if not defined.
//		Sample usage: print_r(getYearsbyAge(4,18,22,8,31));
//
// function getDays2Bday($month, $day, $opt)
//		Note: Set $opt to 0 to count days to next birthday, 1 to count days from last birthday
//		Sample usage: echo getDays2Bday(8,31,0);
//
// function countDays($year1, $month1, $day1, $year2, $month2, $day2)
//		Note: The 1st 3 parameters are for start date, and next 3 parameters are for end date
//		Sample usage: echo countDays(2000,8,31,2024,8,8);
//
// function todayYMD()
//		Note: Return today's date in an array of Year, Month, Day
//		Sample usage: print_r(todayYMD());
//
// function inThePast($month, $day)
//		Note: Return TRUE if today has passed the date in this year
//		Sample usage: echo inThePast(8,31);
//
// function passedCNY($index, $month, $day)
//		Note: Return TRUE if the date has passed Chinese New Year
//		Note: Index can be the year in 4 digit (eg. 1900) or (1876-year). Maximum is 287 (year 2163).
//		Note: $month and $day are optional. Default to 8/8 if not defined.
//		Sample usage: echo passCNY(123,2,2);
//
// function getCNYday($year)
//		Note: Return an array containing month and day of Chinese New Year date of the year
//		Sample usage: print_r(getCNYday(2000));
//
// Note: In all the above functions, the $lang or $opt argument will default to the 1st selection if not defined when the function is called.
//
// Special note: in PHP, avoid declaring function inside function. That will cause "redeclaration error" when the parent function is called more than once.
//

// This function is used by getYearsByAge() and getAnimalYearsByRange()
function swap(&$item1, &$item2) {
    $tmp = $item1;
    $item1 = $item2;
    $item2 = $tmp;
}

// This function is used by getZodiac()
function findIndex($target, $arr, $left=0) {
	$max = count($arr)-2;
	// interpolate the range to speed up search with less iteration
	// $left <= $right <= $max. This defines our search range :)
	$right = ($left>=$max)?$max:$left+1;
	// perform binary search matching...
	while ($left <= $right) {
		$mid = floor(($left + $right)/2);
		if ($target >= $arr[$mid] && $target < $arr[$mid+1]) {
			// target is already within the tightest range
			return $mid;
		}
		// tighten down the search range
		if ($arr[$mid] < $target) {
			$left = $mid+1;
		} else {
			$right = $mid-1;
		}
	}
	// target is out of range
	return -1;
}

// This function is used by getDays2Bday()
function countDays($year1, $month1, $day1, $year2, $month2, $day2) {
	if (!checkdate($month1,$day1,$year1)) {
		throw new Exception(__FUNCTION__.'(): Invalid start date.');
	}
	if (!checkdate($month2,$day2,$year2)) {
		throw new Exception(__FUNCTION__.'(): Invalid end date.');
	}
	$day1 = date_create($year1."-".$month1."-".$day1);
	$day2 = date_create($year2."-".$month2."-".$day2);
	return date_diff($day1,$day2)->format("%r%a");
}

// This function is used by inThePast(), getYearbyAge() and getDays2Bday()
function todayYMD() {
	$today = date_create('now');
	return array(date_format($today,'Y'), date_format($today,'m'), date_format($today,'d'));
}

// This function is used by getDays2Bday()
function inThePast($month, $day) {
	$today = todayYMD();
	$thisYear = $today[0];
	$thisMonth = $today[1];
	$thisDay = $today[2];
	if (($thisMonth < $month) || (($thisMonth == $month)&&($thisDay <= $day))) {
		// the date is in the future	
		return FALSE;
	} else {
		// the date has passed today
		return TRUE;
	}
}

// This function is used by getCNYday() and passedCNY()
function getCNY($year) {
	$arr = [126, 213, 202, 122, 210, 130, 218, 208, 128, 215, 204, 124, 212, 131, 121, 209, 130, 217, 206, 126, 213, 202, 122, 210,
			131, 219, 208, 129, 216, 204, 125, 213, 202, 122, 210, 130, 218, 206, 126, 214, 204, 123, 211, 201, 220, 208, 128, 216, 
			205, 124, 213, 202, 123, 210, 130, 217, 206, 126, 214, 204, 124, 211, 131, 219, 208, 127, 215, 205, 125, 213, 202, 122,
			210, 129, 217, 206, 127, 214, 203, 124, 212, 131, 218, 208, 128, 215, 205, 125, 213, 202, 121, 209, 130, 217, 206, 127,
			215, 203, 123, 211, 131, 218, 207, 128, 216, 205, 125, 213, 202, 220, 209, 129, 217, 206, 127, 215, 204, 123, 210, 131,
			219, 207, 218, 216, 205, 124, 212, 201, 122, 209, 129, 218, 207, 126, 214, 203, 123, 210, 131, 219, 208, 128, 216, 205,			
			125, 212, 201, 122, 210, 129, 217, 206, 126, 213, 203, 123, 211, 131, 219, 208, 128, 215, 204, 124, 212, 201, 122, 210,
			130, 217, 206, 126, 214, 202, 123, 211, 201, 219, 208, 128, 215, 204, 124, 212, 202, 121, 209, 129, 217, 205, 126, 214,
			203, 123, 211, 131, 219, 207, 127, 215, 205, 124, 212, 202, 122, 209, 129, 217, 206, 126, 214, 203, 124, 210, 130, 218,
			207, 127, 215, 205, 125, 212, 201, 121, 209, 129, 217, 207, 128, 215, 204, 124, 212, 131, 219, 208, 129, 216, 206, 126,
			214, 202, 122, 210, 130, 217, 207, 127, 215, 203, 123, 211, 201, 219, 208, 129, 217, 205, 125, 213, 202, 122, 210, 130,
			218, 207, 127, 215, 204, 123, 211, 201, 220, 208, 129, 216, 205, 125, 212, 202, 123, 210, 130, 218, 207, 126, 214, 203];
	
	$index = ($year>=1876)?$year-1876:$year;	// first year is 1876
	if(($index < 0) || ($index >= 288)) {		// hardcode 288 for count($arr);
		// out of range
		return -1;
	}
	return $arr[$index];	
}

function getCNYday($year) {
	$cny = getCNY($year);
	if ($cny != -1) {
		$month = intdiv($cny,100);
		$day = $cny % 100;
		return array($month,$day);
	}
	// out of range
	return -1;
}

// This function is used by getAnimal()
function passedCNY($index, $month=8, $day=8) {
	if ($month > 2) {
		// CNY always passed after February
		return TRUE;
	}
	$cny = getCNY($index);
	if ($cny <= ($month*100 + $day)) {
		// this includes condition of $cny == -1 (out of range)
		return TRUE;
	}
	return FALSE;
}

function getZodiac($month, $day, $lang=1) {
// $lang selections: 1 = English; 2 = Simplified Chinese; 3 = Traditional Chinese; 4 = Simplified Chinese (alternative naming); 5 = Traditional Chinese (alternative naming)
	if (($lang < 1) || ($lang > 5)) {	// hardcode 5 for count($constellation);
		// Undefined language, set it to default (English)
		$lang=1;
	}
	$arr = [101, 120, 219, 321, 420, 521, 621, 723, 823, 923, 1023, 1122, 1222, 1232];
	$constellation = 
		[['Capricorn','Aquarius','Pisces','Aries','Taurus','Gemini','Cancer','Leo','Virgo','Libra','Scorpio','Sagittarius','Capricorn'],
		 ['摩羯座','水瓶座','双鱼座','牡羊座','金牛座','双子座','巨蟹座','狮子座','处女座','天枰座','天蝎座','射手座','魔羯座'],
		 ['摩羯座','水瓶座','雙魚座','牡羊座','金牛座','雙子座','巨蟹座','獅子座','處女座','天秤座','天蝎座','射手座','魔羯座'],
		 ['山羊座','水瓶座','双鱼座','白羊座','金牛座','双子座','巨蟹座','狮子座','处女座','天枰座','天蝎座','人马座','山羊座'],
		 ['山羊座','水瓶座','雙魚座','白羊座','金牛座','雙子座','巨蟹座','獅子座','處女座','天秤座','天蝎座','人馬座','山羊座']];

	$target = $month*100 + $day;
	$zodiac = findIndex($target, $arr, $month-1);
	if ($zodiac == -1) {
		throw new Exception(__FUNCTION__.'(): Invalid date.');
	}
	return $constellation[$lang-1][$zodiac];
}

// This function is used by getAnimal() and getAnimalYearsByRange()
function getAnimalIndex($year, $month, $day) {
	if (!checkdate($month,$day,$year)) {
		throw new Exception(__FUNCTION__.'(): Invalid date.');
	}
	$year -= 1900;
	if (passedCNY($year,$month,$day)) {
		$index = $year % 12;
	} else {
		$index = ($year+11) % 12;
	}
	if ($index < 0) {
		$index += 12;
	}
	return $index;
}

function getAnimal($year, $month, $day, $lang=1) {
// $lang selections: 1 = English; 2 = Simplified Chinese; 3 = Traditional Chinese; 4 = Simplified Chinese with Earthly Branch; 5 = Traditional Chinese with Earthly Branch
	if (($lang < 1) || ($lang > 5)) {	// hardcode 5 for count($animal);
		// Undefined language, set it to default (English)
		$lang=1;
	}
	$animal = [['Rat','Ox','Tiger','Hare','Dragon','Snake','Horse','Sheep','Monkey','Rooster','Dog','Boar'],
				['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'],
				['鼠','牛','虎','兔','龍','蛇','馬','羊','猴','雞','狗','猪'],
				['子鼠','丑牛','寅虎','卯兔','辰龙','巳蛇','午马','未羊','申猴','酉鸡','戌狗','亥猪'],
				['子鼠','丑牛','寅虎','卯兔','辰龍','巳蛇','午馬','未羊','申猴','酉雞','戌狗','亥猪']];

	$index = getAnimalIndex($year,$month,$day);
	return $animal[$lang-1][$index];
}

function getAge($year, $month, $day, $opt=0) {
// $opt == 0: age in years only
// $opt == 1: age in array of years, months, days
// $opt == 2: age in number of days
	if (!checkdate($month,$day,$year)) {
		throw new Exception(__FUNCTION__.'(): Invalid date.');
	}
	$date = date_create($year."-".$month."-".$day);
	$today = date_create('now');
	$diff = date_diff($date,$today);
	switch($opt) {
		case 0:
			return $diff->format("%r%y");
		case 1:
			return array($diff->format("%r%y"),$diff->format("%r%m"),$diff->format("%r%d"));
		case 2:
			return $diff->format("%r%a");
		default:
			// Opt is out of range
			return -1;
	}
}

// This function is used by getYearFromAnimalByAge()
function getYearsByAge($age1, $age2, $month=1, $day=1) {
	$today = todayYMD();
	$thisYear = $today[0];
	$offset1 = $offset2 = 0;

	if ($age1 < $age2) {
		swap($age1,$age2);
	}
	if (!inthePast($month,$day-1)) {
		if ($age1 > 0) {
			$offset1 = 1;
		}
		if ($age2 > 0) {
			$offset2 = 1;
		}
	}
	$year1 = $thisYear - $age1 - $offset1;
	$year2 = $thisYear - $age2 - $offset2;
	return array($year1, $year2);
}

// This function is used by getAnimalYearsByAge()
function getAnimalYearsByRange($animalIndex, $year1, $year2, $month=1, $day=1) {
// $animalIndex range from 0 to 11, according to the sequence as listed in getAnimal()
	if ($year1 > $year2) {
		swap($year1,$year2);
	}
	$result = array();
	for ($year = $year1; $year <= $year2; $year++) {
		if (getAnimalIndex($year,$month,$day) == $animalIndex) {
			if(passedCNY($year,$month,$day)) {
				array_push($result,$year-1);
			} else {
				array_push($result,$year);
			}
		}
	}
	return $result;
}

function getAnimalYearsByAge($animalIndex, $age1, $age2, $month=1, $day=1) {
// $animalIndex range from 0 to 11, according to the sequence as listed in getAnimal()
	$range = getYearsByAge($age1,$age2,$month,$day);
	return getAnimalYearsByRange($animalIndex,$range[0],$range[1],$month,$day);
}

function getDays2Bday($month, $day, $opt=0) {
// $opt == 0: count days to next birthday
// $opt == 1: count days from last birthday
	$today = todayYMD();
	$thisYear = $today[0];
	$thisMonth = $today[1];
	$thisDay = $today[2];
	if (inthePast($month,$day)) {
		// this year's birthday has passed
		$year = ($opt==0)?$thisYear+1:$thisYear;
	} else {
		// this year's birthday not yet passed
		$year = ($opt==0)?$thisYear:$thisYear-1;
	}
	return countDays($thisYear,$thisMonth,$thisDay,$year,$month,$day);
}

?>
