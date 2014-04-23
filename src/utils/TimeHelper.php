<?php

/**
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.utils
 * @since 1.1.1
 * @version 1.0
 *
 */
class TimeHelper
{
    public static function getDays($sStartDate, $sEndDate)
    {
        // Firstly, format the provided dates.
        // This function works best with YYYY-MM-DD
        // but other date formats will work thanks
        // to strtotime().
        $sStartDate = gmdate("Y-m-d", strtotime($sStartDate));
        $sEndDate = gmdate("Y-m-d", strtotime($sEndDate));

        // Start the variable off with the start date
        $aDays[] = $sStartDate;

        // Set a 'temp' variable, sCurrentDate, with
        // the start date - before beginning the loop
        $sCurrentDate = $sStartDate;

        // While the current date is less than the end date
        while ($sCurrentDate < $sEndDate) {
            // Add a day to the current date
            $sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));

            // Add this new day to the aDays array
            $aDays[] = $sCurrentDate;
        }

        // Once the loop has finished, return the
        // array of days.
        return $aDays;
    }

    public static function getDatesByWeek($_week_number, $_year = null)
    {
        $year = $_year ? $_year : date('Y');
        $week_number = sprintf('%02d', $_week_number);
        $date_base = strtotime($year . 'W' . $week_number . '1 00:00:00');
        $date_limit = strtotime($year . 'W' . $week_number . '7 23:59:59');

        return array($date_base, $date_limit);
    }

    /**
     * Returns a nicely formatted date string for given Datetime string.
     *
     * @param string $dateString Datetime string
     * @param int $format Format of returned date
     * @return string Formatted date string
     */
    public static function nice($dateString = null, $time = true)
    {
        $date = ($dateString) ? ((int)$dateString) ? $dateString : strtotime($dateString) : time();

        $y = (self::isThisYear($date)) ? '' : ' Y';

        if (self::isToday($date)) {
            $return = ($time) ? sprintf(CoreModule::t('Today') . ', %s', date("H:i", $date)) : CoreModule::t('Today');
        } elseif (self::wasYesterday($date)) {
            $return = ($time) ? sprintf(CoreModule::t('Yesterday') . ', %s', date("H:i", $date)) : CoreModule::t('Yesterday');
        } else {
            $return = ($time) ? date("d F{$y}, H:i", $date) : date("d M{$y}", $date);
        }

        return $return;
    }

    public static function left($date = null)
    {
        $date = ($date) ? $date : time();
        return ceil(($date - time()) / 86400);
    }

    /**
     * Returns true if given date is today.
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is today
     */
    public static function isToday($date)
    {
        return date('Y-m-d', $date) == date('Y-m-d', time());
    }

    /**
     * Returns true if given date was yesterday
     *
     * @param string $date Unix timestamp
     * @return boolean True if date was yesterday
     */
    public static function wasYesterday($date)
    {
        return date('Y-m-d', $date) == date('Y-m-d', strtotime('yesterday'));
    }

    /**
     * Returns true if given date is in this year
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is in this year
     */
    public static function isThisYear($date)
    {
        return date('Y', $date) == date('Y', time());
    }

    /**
     * Returns true if given date is in this week
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is in this week
     */
    public static function isThisWeek($date)
    {
        return date('W Y', $date) == date('W Y', time());
    }

    /**
     * Returns true if given date is in this month
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is in this month
     */
    public static function isThisMonth($date)
    {
        return date('m Y', $date) == date('m Y', time());
    }

    /**
     * Returns either a relative date or a formatted date depending
     * on the difference between the current time and given datetime.
     * $datetime should be in a <i>strtotime</i>-parsable format, like MySQL's datetime datatype.
     *
     * Options:
     *  'format' => a fall back format if the relative time is longer than the duration specified by end
     *  'end' =>  The end of relative time telling
     *
     * Relative dates look something like this:
     *    3 weeks, 4 days ago
     *    15 seconds ago
     * Formatted dates look like this:
     *    on 02/18/2004
     *
     * The returned string includes 'ago' or 'on' and assumes you'll properly add a word
     * like 'Posted ' before the function output.
     *
     * @param string $dateString Datetime string
     * @param array $options Default format if timestamp is used in $dateString
     * @return string Relative time string.
     */
    function timeAgoInWords($dateTime, $options = array())
    {
        $now = time();

        $inSeconds = strtotime($dateTime);
        $backwards = ($inSeconds > $now);

        $format = 'j/n/y';
        $end = '+1 month';

        if (is_array($options)) {
            if (isset($options['format'])) {
                $format = $options['format'];
                unset($options['format']);
            }
            if (isset($options['end'])) {
                $end = $options['end'];
                unset($options['end']);
            }
        } else {
            $format = $options;
        }

        if ($backwards) {
            $futureTime = $inSeconds;
            $pastTime = $now;
        } else {
            $futureTime = $now;
            $pastTime = $inSeconds;
        }
        $diff = $futureTime - $pastTime;

        // If more than a week, then take into account the length of months
        if ($diff >= 604800) {
            $current = array();
            $date = array();

            list($future['H'], $future['i'], $future['s'], $future['d'], $future['m'], $future['Y']) = explode('/', date('H/i/s/d/m/Y', $futureTime));

            list($past['H'], $past['i'], $past['s'], $past['d'], $past['m'], $past['Y']) = explode('/', date('H/i/s/d/m/Y', $pastTime));
            $years = $months = $weeks = $days = $hours = $minutes = $seconds = 0;

            if ($future['Y'] == $past['Y'] && $future['m'] == $past['m']) {
                $months = 0;
                $years = 0;
            } else {
                if ($future['Y'] == $past['Y']) {
                    $months = $future['m'] - $past['m'];
                } else {
                    $years = $future['Y'] - $past['Y'];
                    $months = $future['m'] + ((12 * $years) - $past['m']);

                    if ($months >= 12) {
                        $years = floor($months / 12);
                        $months = $months - ($years * 12);
                    }

                    if ($future['m'] < $past['m'] && $future['Y'] - $past['Y'] == 1) {
                        $years--;
                    }
                }
            }

            if ($future['d'] >= $past['d']) {
                $days = $future['d'] - $past['d'];
            } else {
                $daysInPastMonth = date('t', $pastTime);
                $daysInFutureMonth = date('t', mktime(0, 0, 0, $future['m'] - 1, 1, $future['Y']));

                if (!$backwards) {
                    $days = ($daysInPastMonth - $past['d']) + $future['d'];
                } else {
                    $days = ($daysInFutureMonth - $past['d']) + $future['d'];
                }

                if ($future['m'] != $past['m']) {
                    $months--;
                }
            }

            if ($months == 0 && $years >= 1 && $diff < ($years * 31536000)) {
                $months = 11;
                $years--;
            }

            if ($months >= 12) {
                $years = $years + 1;
                $months = $months - 12;
            }

            if ($days >= 7) {
                $weeks = floor($days / 7);
                $days = $days - ($weeks * 7);
            }
        } else {
            $years = $months = $weeks = 0;
            $days = floor($diff / 86400);

            $diff = $diff - ($days * 86400);

            $hours = floor($diff / 3600);
            $diff = $diff - ($hours * 3600);

            $minutes = floor($diff / 60);
            $diff = $diff - ($minutes * 60);
            $seconds = $diff;
        }
        $relativeDate = '';
        $diff = $futureTime - $pastTime;

        if ($diff > abs($now - strtotime($end))) {
            $relativeDate = sprintf('on %s', date($format, $inSeconds));
        } else {
            if ($years > 0) {
                // years and months and days
                $relativeDate .= ($relativeDate ? ', ' : '') . $years . ' ' . ($years == 1 ? 'year' : 'years');
                $relativeDate .= $months > 0 ? ($relativeDate ? ', ' : '') . $months . ' ' . ($months == 1 ? 'month' : 'months') : '';
                $relativeDate .= $weeks > 0 ? ($relativeDate ? ', ' : '') . $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks') : '';
                $relativeDate .= $days > 0 ? ($relativeDate ? ', ' : '') . $days . ' ' . ($days == 1 ? 'day' : 'days') : '';
            } elseif (abs($months) > 0) {
                // months, weeks and days
                $relativeDate .= ($relativeDate ? ', ' : '') . $months . ' ' . ($months == 1 ? 'month' : 'months');
                $relativeDate .= $weeks > 0 ? ($relativeDate ? ', ' : '') . $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks') : '';
                $relativeDate .= $days > 0 ? ($relativeDate ? ', ' : '') . $days . ' ' . ($days == 1 ? 'day' : 'days') : '';
            } elseif (abs($weeks) > 0) {
                // weeks and days
                $relativeDate .= ($relativeDate ? ', ' : '') . $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks');
                $relativeDate .= $days > 0 ? ($relativeDate ? ', ' : '') . $days . ' ' . ($days == 1 ? 'day' : 'days') : '';
            } elseif (abs($days) > 0) {
                // days and hours
                $relativeDate .= ($relativeDate ? ', ' : '') . $days . ' ' . ($days == 1 ? 'day' : 'days');
                $relativeDate .= $hours > 0 ? ($relativeDate ? ', ' : '') . $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') : '';
            } elseif (abs($hours) > 0) {
                // hours and minutes
                $relativeDate .= ($relativeDate ? ', ' : '') . $hours . ' ' . ($hours == 1 ? 'hour' : 'hours');
                $relativeDate .= $minutes > 0 ? ($relativeDate ? ', ' : '') . $minutes . ' ' . ($minutes == 1 ? 'minute' : 'minutes') : '';
            } elseif (abs($minutes) > 0) {
                // minutes only
                $relativeDate .= ($relativeDate ? ', ' : '') . $minutes . ' ' . ($minutes == 1 ? 'minute' : 'minutes');
            } else {
                // seconds only
                $relativeDate .= ($relativeDate ? ', ' : '') . $seconds . ' ' . ($seconds == 1 ? 'second' : 'seconds');
            }

            if (!$backwards) {
                $relativeDate = sprintf('%s ago', $relativeDate);
            }
        }
        return $relativeDate;
    }


    /*
    * Пример использования
    *
    * <?php
    * $checkdate = new ETime();
    * $test = $checkdate->getdelta(1317715274, time());
    * Y::dump($test);
    * ?>
    *
    */

    private $from = "seconds";

    //@todo сделать человекопонятные даты ниже через Yii::t() или лучше через CoreModule::t();
    private $intervals = array(
        "seconds" => array("секунду", "секунды", "секунд"),
        "minutes" => array("минуту", "минуты", "минут"),
        "hours" => array("час", "часа", "часов"),
        "mday" => array("день", "дня", "дней"),
        "mon" => array("месяц", "месяца", "месяцев"),
        "year" => array("год", "года", "лет")
    );

    // Creates new object.
    // If $from is specified, "granularity" while spelling is $from.
    public function ETime($from = "seconds")
    {
        $this->from = $from;
    }

    // returns the associative array with date deltas.
    public function getDelta($first, $last)
    {
        if ($last < $first) return false;

        // Solve H:M:S part.
        $hms = ($last - $first) % (3600 * 24);
        $delta['seconds'] = $hms % 60;
        $delta['minutes'] = floor($hms / 60) % 60;
        $delta['hours'] = floor($hms / 3600) % 60;

        // Now work only with date, delta time = 0.
        $last -= $hms;
        $f = getdate($first);
        $l = getdate($last); // the same daytime as $first!

        $dYear = $dMon = $dDay = 0;

        // Delta day. Is negative, month overlapping.
        $dDay += $l['mday'] - $f['mday'];
        if ($dDay < 0) {
            $monlen = $this->monthLength(date("Y", $first), date("m", $first));
            $dDay += $monlen;
            $dMon--;
        }
        $delta['mday'] = $dDay;

        // Delta month. If negative, year overlapping.
        $dMon += $l['mon'] - $f['mon'];
        if ($dMon < 0) {
            $dMon += 12;
            $dYear--;
        }
        $delta['mon'] = $dMon;

        // Delta year.
        $dYear += $l['year'] - $f['year'];
        $delta['year'] = $dYear;

        return $delta;
    }

    //@todo см. аналогичную функцию ниже. Взято с http://forum.dklab.ru/php/advises/TransformationOfADifferenceBetweenTwoUnixTimeAtLine.html
    // Makes the spellable phrase.
    /*function spellDelta($first, $last)
     {
         // Solve data delta.
         $delta = $this->getDelta($first, $last);
         if (!$delta) return false;

         // Make spellable phrase.
         $parts = array();
         foreach (array_reverse($delta) as $k=>$n) {
             if (!$n) continue;
             $parts[] = $this->declension($n, $this->intervals[$k]);
             if ($this->from && $k == $this->from) break;
         }
         return join(" ", $parts);
     }*/

    //с погрешностью до секунды
    function spellDelta($first, $last)
    {
        // Solve data delta.
        $delta = $this->getDelta($first, $last);
        if (!$delta) return false;

        // Make spellable phrase.
        $parts = array();
        foreach (array_reverse($delta) as $k => $n) {
            if ($n) //Modified
            {
                $parts[] = $this->declension($n, $this->intervals[$k]);
            }
            if ($this->from && $k == $this->from) break;
        }
        return join(" ", $parts);
    }

    // Returns the length (in days) of the specified month.
    function monthLength($year, $mon)
    {
        $l = 28;
        while (checkdate($mon, $l + 1, $year)) $l++;
        return $l;
    }

    // Функция предназначена для вывода численных результатов с учетом
    // склонения слов, например: "1 ответ", "2 ответа", "13 ответов" и т.д.
    // $int — целое число.
    // $expressions — массив, например: array("ответ", "ответа", "ответов")
    function declension($int, $expressions)
    {
        settype($int, "integer");
        $count = $int % 100;
        if ($count >= 5 && $count <= 20) {
            $result = $int . " " . $expressions['2'];
        } else {
            $count = $count % 10;
            if ($count == 1) {
                $result = $int . " " . $expressions['0'];
            } elseif ($count >= 2 && $count <= 4) {
                $result = $int . " " . $expressions['1'];
            } else {
                $result = $int . " " . $expressions['2'];
            }
        }
        return $result;
    }

    public static function formatMinutes($minutes)
    {
        $hours = (int)($minutes / 60);
        $minutes = $minutes % 60;

        if ($hours)
            $result = $hours . ' ч. ' . ((!empty($minutes)) ? $minutes . ' мин.' : '');
        else
            $result = $minutes . ' мин.';

        return $result;
    }

    public static function getYears($date)
    {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date('Y') - date('Y', $timestamp);
    }
}
