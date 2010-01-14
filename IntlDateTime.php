<?php
/**
 * IntlDateTime is an extented version of php 5 DateTime class with integrated
 * IntlDateFormatter functionality (needs php >= 5.3.0 or PECL intl >= 1.0.0)
 * However, this class is not compatible with DateTime class because it uses ICU
 * pattern syntax for formatting and parsing date strings.
 * (@link http://userguide.icu-project.org/formatparse/datetime)
 *
 * @copyright   Copyright 2010, Ali Farhadi (http://farhadi.ir/)
 * @license     GNU General Public License 3.0 (http://www.gnu.org/licenses/gpl.html)
 */
class IntlDateTime extends DateTime {
	
	/**
	 * @var string The current locale in use
	 */
	protected $locale;

	/**
	 * @var string The current calendar in use
	 */
	protected $calendar;

	/**
	 * Creates a new instance of IntlDateTime
	 *
	 * @param mixed $time Unix timestamp or strtotime() compatible string or another DateTime object
	 * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
	 * @param string $calendar any calendar supported by ICU
	 * @param string $locale any locale supported by ICU
	 * @param string $pattern the date pattern in which $time is formatted.
	 * @return IntlDateTime
	 */
	public function __construct($time = null, $timezone = null, $calendar = 'gregorian', $locale = 'en_US', $pattern = null) {
		if (!isset($timezone)) $timezone = new DateTimeZone(date_default_timezone_get());
		elseif (!is_a($timezone, 'DateTimeZone')) $timezone = new DateTimeZone($timezone);

		parent::__construct(null, $timezone);
		$this->setLocale($locale);
		$this->setCalendar($calendar);
		if (isset($time)) $this->set($time, null, $pattern);
	}

	/**
	 * Returns an instance of IntlDateFormatter with specified options.
	 *
	 * @param array $options
	 * @return IntlDateFormatter
	 */
	protected function getFormatter($options = array()) {
		$locale = empty($options['locale']) ? $this->locale : $options['locale'];
		$calendar = empty($options['calendar']) ? $this->calendar : $options['calendar'];
		$timezone = empty($options['timezone']) ? $this->getTimezone() : $options['timezone'];
		if (is_a($timezone, 'DateTimeZone')) $timezone = $timezone->getName();
		$pattern = empty($options['pattern']) ? null : $options['pattern'];
		return new IntlDateFormatter($locale . '@calendar=' . $calendar,
				IntlDateFormatter::FULL,  IntlDateFormatter::FULL, $timezone,
				IntlDateFormatter::GREGORIAN, $pattern);
	}

	/**
	 * Replaces localized digits in $str with latin digits.
	 *
	 * @param string $str
	 * @return string Latinized string
	 */
	protected function latinizeDigits($str) {
		$result = '';
		$num = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
		preg_match_all('/.[\x80-\xBF]*/', $str, $matches);
		foreach ($matches[0] as $char) {
			$pos = 0;
			$parsedChar = $num->parse($char, NumberFormatter::TYPE_INT32, $pos);
			$result .= $pos ? $parsedChar : $char;
		}
		return $result;
	}

	/**
	 * Tries to guess the date pattern in which $time is formatted.
	 *
	 * @param string $time The date string
	 * @return string Detected ICU pattern on success, FALSE otherwise.
	 */
	protected function guessPattern($time) {
		$time = $this->latinizeDigits(trim($time));

		$shortDateRegex = '(\d{2,4})(-|\\\\|/)\d{1,2}\2\d{1,2}';
		$longDateRegex = '([^\d]*\s)?\d{1,2}(-| )[^-\s\d]+\4(\d{2,4})';
		$timeRegex = '\d{1,2}:\d{1,2}(:\d{1,2})?(\s.*)?';

		if (preg_match("@^(?:(?:$shortDateRegex)|(?:$longDateRegex))(\s+$timeRegex)?$@", $time, $match)) {
			if (!empty($match[1])) {
				$separator = $match[2];
				$pattern = strlen($match[1]) == 2 ? 'yy' : 'yyyy';
				$pattern .= $separator . 'MM' . $separator . 'dd';
			} else {
				$separator = $match[4];
				$pattern = 'dd' . $separator . 'LLL' . $separator;
				$pattern .= strlen($match[5]) == 2 ? 'yy' : 'yyyy';
				if (!empty($match[3])) $pattern = (preg_match('/,\s+$/', $match[3]) ? 'E, ' : 'E ') . $pattern;
			}
			if (!empty($match[6])) {
				$pattern .= !empty($match[8]) ? ' hh:mm' : ' HH:mm';
				if (!empty($match[7])) $pattern .= ':ss';
				if (!empty($match[8])) $pattern .= ' a';
			}
			return $pattern;
		}

		return false;
	}

	/**
	 * Sets the locale used by the object.
	 *
	 * @param string $locale
	 * @return IntlDateTime The modified DateTime.
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
		return $this;
	}

	/**
	 * Gets the current locale used by the object.
	 *
	 * @return string
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * Sets the calendar used by the object.
	 *
	 * @param string $calendar
	 * @return IntlDateTime The modified DateTime.
	 */
	public function setCalendar($calendar) {
		$this->calendar = strtolower($calendar);
		return $this;
	}

	/**
	 * Gets the current calendar used by the object.
	 *
	 * @return string
	 */
	public function getCalendar() {
		return $this->calendar;
	}

	/**
	 * Overrides the getTimestamp method to support timestamps out of the integer range.
	 *
	 * @return float Unix timestamp representing the date.
	 */
	public function getTimestamp() {
		return floatval(parent::format('U'));
	}

	/**
	 * Overrides the setTimestamp method to support timestamps out of the integer range.
	 *
	 * @param float $unixtimestamp Unix timestamp representing the date.
	 * @return IntlDateTime the modified DateTime.
	 */
	public function setTimestamp($unixtimestamp) {
		$diff = $unixtimestamp - $this->getTimestamp();
		$days = floor($diff / 86400);
		$seconds = $diff - $days * 86400;
		$timezone = $this->getTimezone();
		$this->setTimezone('UTC');
		parent::modify("$days days $seconds seconds");
		$this->setTimezone($timezone);
		return $this;
	}

	/**
	 * Alters object's internal timestamp with a string acceptable by strtotime() or a Unix timestamp or a DateTime object.
	 *
	 * @param mixed $time Unix timestamp or strtotime() compatible string or another DateTime object
	 * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
	 * @param string $pattern the date pattern in which $time is formatted.
	 * @return IntlDateTime The modified DateTime.
	 */
	public function set($time = null, $timezone = null, $pattern = null) {
		if (is_a($time, 'DateTime')) {
			$time = $time->format('U');
		} elseif (!is_numeric($time)) {
			if (!$pattern) {
				$pattern = $this->guessPattern($time);
			}
			
			$timezone = empty($timezone) ? $this->getTimezone() : $timezone;
			if (is_a($timezone, 'DateTimeZone')) $timezone = $timezone->getName();
			$defaultTimezone = @date_default_timezone_get();
			date_default_timezone_set($timezone);
			
			if ($pattern) {
				$time = $this->getFormatter(array('timezone' => 'GMT', 'pattern' => $pattern))->parse($time);
				$time -= date('Z', $time);
			} else {
				$time = strtotime($time);
			}

			date_default_timezone_set($defaultTimezone);
		}

		$this->setTimestamp($time);

		return $this;
	}

	/**
	 * Resets the current date of the object.
	 *
	 * @param integer $year
	 * @param integer $month
	 * @param integer $day
	 * @return IntlDateTime The modified DateTime.
	 */
	public function setDate($year, $month, $day) {
		$this->set("$year/$month/$day ".$this->format('HH:mm:ss'), null, 'yyyy/MM/dd HH:mm:ss');
		return $this;
	}

	/**
	 * Sets the timezone for the object.
	 * 
	 * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
	 * @return IntlDateTime The modified DateTime.
	 */
	public function setTimezone($timezone) {
		if (!is_a($timezone, 'DateTimeZone')) $timezone = new DateTimeZone($timezone);
		parent::setTimezone($timezone);
		return $this;
	}

	/**
	 * Alter the timestamp by incrementing or decrementing in a format accepted by strtotime().
	 * 
	 * @param string $modify a string in a relative format accepted by strtotime().
	 * @return IntlDateTime The modified DateTime.
	 */
	public function modify($modify) {
		//TODO: support for modifications based on current calendar
		parent::modify($modify);
		return $this;
	}

	/**
	 * Returns date formatted according to given pattern.
	 *
	 * @param string $pattern Date pattern in ICU syntax (@link http://userguide.icu-project.org/formatparse/datetime)
	 * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
	 * @return string Formatted date on success or FALSE on failure.
	 */
	public function format($pattern, $timezone = null) {
		if (isset($timezone)) {
			$tempTimezone = $this->getTimezone();
			$this->setTimezone($timezone);
		}
 
		$result = $this->getFormatter(array('timezone' => 'GMT'.parent::format('O'), 'pattern' => $pattern))->format($this->getTimestamp());

		if (isset($timezone)) {
			$this->setTimezone($tempTimezone);
		}

		return $result;
	}

	/**
	 * Preserve original DateTime::format functionality
	 *
	 * @param string $format Format accepted by date().
	 * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
	 * @return string Formatted date on success or FALSE on failure.
	 */
	public function classicFormat($format, $timezone = null) {
		if (isset($timezone)) {
			$tempTimezone = $this->getTimezone();
			$this->setTimezone($timezone);
		}

		$result = parent::format($format);

		if (isset($timezone)) {
			$this->setTimezone($tempTimezone);
		}

		return $result;
	}
}
?>