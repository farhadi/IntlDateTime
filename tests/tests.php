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

		$date = new IntlDateTime('1431/01/27', 'Asia/Tehran', 'islamic-civil');
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

	function testSet() {
		$date = new IntlDateTime('now', 'Asia/Tehran', 'gregorian');

		$date->set('2009/1/2 01:00 PM');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2009/01/02 13:00:00');

		$date->set('2009-10-25');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2009/10/25 00:00:00');

		$date->set('09/05/02 14:00');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2009/05/02 14:00:00');

		$date->set('14 Jan 2010');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2010/01/14 00:00:00');

		$date->set('Wed, 7 Jan 09');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2009/01/07 00:00:00');

		$date->set('Monday, 15 March 2010 06:22:30 PM');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2010/03/15 18:22:30');

		$date->set(strtotime('25 Dec 2009'));
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2009/12/25 00:00:00');

		$date->set(new DateTime('15 Jan 2010'));
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2010/01/15 00:00:00');

		$date->set('now');
		$result = $date->getTimestamp();
		$expected = strtotime('now');
		$this->assertEqual($result, $expected);

		$date->set('yesterday');
		$result = $date->getTimestamp();
		$expected = strtotime('yesterday');
		$this->assertEqual($result, $expected);

		$date->set('2010/01/10', 'UTC');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2010/01/10 03:30:00');

		$date->set('2010/07/10', 'UTC');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2010/07/10 04:30:00');

		$date->set('20100412182457', null, 'yyyyMMddHHmmss');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2010/04/12 18:24:57');

		$date->setLocale('fa');

		$date->set('دوشنبه ۱۵ مارس ۲۰۱۰ ۱۲:۳۲:۴۵');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '۲۰۱۰/۰۳/۱۵ ۱۲:۳۲:۴۵');

		$date->setCalendar('persian');

		$date->set('۲۳ دی ۱۳۸۸');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '۱۳۸۸/۱۰/۲۳ ۰۰:۰۰:۰۰');

		$date->set('۱۳۸۸-۱۱-۲۲ ۲۳:۵۰');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '۱۳۸۸/۱۱/۲۲ ۲۳:۵۰:۰۰');
	}

	function testModify() {
		$date = new IntlDateTime('1388/04/01', 'Asia/Tehran', 'persian');
		$date->modify('+1 month');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '1388/05/01 00:00:00');

		$date->set('1387/11/01');
		$date->modify('+1 year');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '1388/11/01 00:00:00');

		$date->set('1387/11/01 03:45');
		$date->modify('next year');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '1388/11/01 03:45:00');

		$date->set('1388/11/01');
		$date->modify('-12 months');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '1387/11/01 00:00:00');

		$date->set('1386/12/01');
		$date->modify('-4days +2years +3hours');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '1388/11/27 03:00:00');

		$date->set('1388/07/01');
		$date->modify('+1hour +30days -1month');
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '1388/07/01 01:00:00');
	}

	function testGetTimestamp() {
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

	function testSetDate() {
		$date = new IntlDateTime('yesterday');
		$date->setDate(2009, 1, 15);
		$result = $date->format('yyyy/MM/dd HH:mm:ss');
		$this->assertEqual($result, '2009/01/15 00:00:00');
	}
}
?>