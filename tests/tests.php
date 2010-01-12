<?php
error_reporting(E_ALL);
require_once('simpletest/autorun.php');
require_once('../IntlDateTime.php');
date_default_timezone_set('Asia/Tehran');

class IntlDateTimeTestCase extends UnitTestCase {
	function testCalendars() {
		$expected = strtotime('2010/01/13');
		$date = new IntlDateTime('2010/01/13', 'Asia/Tehran', 'gregorian');
		$result = $date->getTimestamp();
		$this->assertEqual($result, $expected);

		$date = new IntlDateTime('1388/10/23', 'Asia/Tehran', 'persian');
		$result = $date->getTimestamp();
		$this->assertEqual($result, $expected);

		$result = $date->getCalendar();
		$this->assertEqual($result, 'persian');

		$date->setCalendar('gregorian');
		$result = $date->getCalendar();
		$this->assertEqual($result, 'gregorian');

		$result = $date->format('yyyy/MM/dd');
		$this->assertEqual($result, '2010/01/13');
	}

	function testLocales() {
		$date = new IntlDateTime('۲۰۱۰/۰۱/۱۳ ۱۲:۴۲:۲۰', 'Asia/Tehran', 'gregorian', 'fa');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '۲۰۱۰/۰۱/۱۳ ۱۲:۴۲:۲۰');

		$result = $date->getLocale();
		$this->assertEqual($result, 'fa');

		$date->setLocale('en');
		$result = $date->getLocale();
		$this->assertEqual($result, 'en');

		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2010/01/13 12:42:20');
	}

	function testGetTimestamp(){
		$date = new IntlDateTime('2010/01/01');
		$result = $date->getTimestamp();
		$expected = strtotime('2010/01/01');
		$this->assertEqual($result, $expected);

		$date = new IntlDateTime('2010/06/01 08:50 PM');
		$result = $date->getTimestamp();
		$expected = strtotime('2010/06/01 08:50 PM');
		$this->assertEqual($result, $expected);

		$date = new IntlDateTime('last year');
		$result = $date->getTimestamp();
		$expected = strtotime('last year');
		$this->assertEqual($result, $expected);

		$now = time();
		$date = new IntlDateTime($now);
		$result = $date->getTimestamp();
		$this->assertEqual($result, $now);
	}
}
?>