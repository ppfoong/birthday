<?php
// This is a PHP library containing functions related to birthday, such as:
// 1. Getting the Zodiac sign (星座) of a date
// 2. Getting the symbolic animal (生肖) of a date
// 3. Calculating the age (年龄)
// 4. Calculating no. of days since born
// 5. Calculating no. of days to the next birthday
// 6. Calculating no. of days from the last birthday
//
// Author: P.P. Foong (https://www.linkedin.com/in/ppfoong/)
// Repository: https://github.com/ppfoong/lib_birthday
// License: The MIT License
// Version 1.1 (2024-08-10)
//
// function getZodiac($month, $day, $lang)
//		Note: Set $lang to 1 for English, 2 for Simplified Chinese, 3 for Traditional Chinese,
//						   4 for Simplified Chinese (alternative naming), 5 for Traditional Chinese (alternative naming)
//		Sample usage: echo getZodiac(8,31,1);
//		Sample usage: echo getZodiac(8,31);			// for English
//
// function getAnimal($year, $month, $day, $lang)
//		Note: Set $lang to 1 for English, 2 for Simplified Chinese, 3 for Traditional Chinese
//		Note: Year range from 1900 to 2139
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
// function getDays2Bday($month, $day, $opt)
//		Note: Set $opt to 0 to count days to next birthday, 1 to count days from last birthday
//		Sample usage: echo getDays2Bday(8,31,0);
//
// function countDays($year1, $month1, $day1, $year2, $month2, $day2)
//		Note: The 1st 3 parameters are for start date, and next 3 parameters are for end date
//		Sample usage: countDays(2000,8,31,2024,8,8);
//
// Note: In all the above functions, the $lang or $opt argument will default to the 1st selection if not defined when the function is called.
//
// Special note: in PHP, avoid declaring function inside function. That will cause "redeclaration error" when the parent function is called more than once.
//

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

function getZodiac($month, $day, $lang=1) {
// $lang selections: 1 = English; 2 = Simplified Chinese; 3 = Traditional Chinese 4 = Simplified Chinese (alternative naming) 5 = Traditional Chinese (alternative naming)
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

function getAnimal($year, $month, $day, $lang=1) {
// $lang selections: 1 = English; 2 = Simplified Chinese; 3 = Traditional Chinese
	if (($lang < 1) || ($lang > 3)) {	// hardcode 3 for count($animal);
		// Undefined language, set it to default (English)
		$lang=1;
	}
	$arr = [131, 219, 208, 129, 216, 204, 125, 213, 202, 122, 210, 130, 218, 206, 126, 214, 204, 123, 211, 201,
			220, 208, 128, 216, 205, 124, 213, 202, 123, 210, 130, 217, 206, 126, 214, 204, 124, 211, 131, 219,
			208, 127, 215, 205, 125, 213, 202, 122, 210, 129, 217, 206, 127, 214, 203, 124, 212, 131, 218, 208,
			128, 215, 205, 125, 213, 202, 121, 209, 130, 217, 206, 127, 215, 203, 123, 211, 131, 218, 207, 128,
			216, 205, 125, 213, 202, 220, 209, 129, 217, 206, 127, 215, 204, 123, 210, 131, 219, 207, 218, 216,
			205, 124, 212, 201, 122, 209, 129, 218, 207, 126, 214, 203, 123, 210, 131, 219, 208, 128, 216, 205,
			125, 212, 201, 122, 210, 129, 217, 206, 126, 213, 203, 123, 211, 131, 219, 208, 128, 215, 204, 124,
			212, 201, 122, 210, 130, 217, 206, 126, 214, 202, 123, 211, 201, 219, 208, 128, 215, 204, 124, 212,
			202, 121, 209, 129, 217, 205, 126, 214, 203, 123, 211, 131, 219, 207, 127, 215, 205, 124, 212, 202,
			122, 209, 129, 217, 206, 126, 214, 203, 124, 210, 130, 218, 207, 127, 215, 205, 125, 212, 201, 121,
			209, 129, 217, 207, 128, 215, 204, 124, 212, 131, 219, 208, 129, 216, 206, 126, 214, 202, 122, 210,
			130, 217, 207, 127, 215, 203, 123, 211, 201, 219, 208, 129, 217, 205, 125, 213, 202, 122, 210, 130];
	$animal =
		[['Rat','Ox','Tiger','Hare','Dragon','Snake','Horse','Sheep','Monkey','Rooster','Dog','Boar'],
		 ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'],
		 ['鼠','牛','虎','兔','龍','蛇','馬','羊','猴','雞','狗','猪']];
	if (!checkdate($month,$day,$year)) {
		throw new Exception(__FUNCTION__.'(): Invalid date.');
	}
	if ($year >= 1900 && $year < 2140) {
		$year -= 1900;
		$target = $month*100 + $day;
		if ($target >= $arr[$year]) {
			$index = $year % 12;
		} else {
			$index = ($year==0)?11:($year-1) % 12;
		}
		return $animal[$lang-1][$index];
	} else {
		// year is out of range
		return -1;
	}
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

function getDays2Bday($month, $day, $opt=0) {
// $opt == 0: count days to next birthday
// $opt == 1: count days from last birthday
	$today = date_create('now');
	$thisYear = date_format($today,'Y');
	$thisMonth = date_format($today,'m');
	$thisDay = date_format($today,'d');
	if (($thisMonth < $month) || (($thisMonth == $month)&&($thisDay <= $day))) {
		// this year's birthday not yet passed	
		$year = ($opt==0)?$thisYear:$thisYear-1;	
	} else {
		// this year's birthday has passed
		$year = ($opt==0)?$thisYear+1:$thisYear;	
	}
	return countDays($thisYear,$thisMonth,$thisDay,$year,$month,$day);
}

?>
