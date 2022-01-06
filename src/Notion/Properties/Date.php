<?php namespace Notion\Properties;

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Notion\PropertyBase;

class Date extends PropertyBase
{
    public static $preferredTimezone = null;
    public static $stringFormat = 'D, M j, Y';

    public function value()
    {
        return $this->config->date->start;
    }

    public function set($value): void
    {
        if (is_object($value) && isset($value->start))
            $this->config->date = $value;
        else {
            if (!isset($this->config->date))
                $this->config->date = (object)['start' => null, 'end' => null];
            $this->config->date->start = $value;
            unset($this->config->date->end);
        }
    }

    public static function formatTime($date, $tz = null)
    {
        return $date === 'N/A'
            ? null
            : Carbon::parse($date, $tz ?? static::$preferredTimezone)
                ->format('g:i A');
    }

    public static function formatDateTimeRange($start, $end, $tz = null): string
    {
        $period = CarbonPeriod::create($start, $end);
        $days = $period->getDateInterval()->days;
        if ($days) {
            return self::formatDateTime($start, $tz) . ' - ' . self::formatDateTime($end, $tz);
        }
        return self::formatDateTime($start, $tz) . ' - ' . self::formatTime($end, $tz);
    }


    public static function formatDateTime($date, $tz = null)
    {
        if (empty($date))
            return null;
        $carbon = $date instanceof Carbon
            ? $date
            : Carbon::parse($date, $tz ? static::$preferredTimezone : null);
        if ($carbon->hour === 0 && $carbon->minute === 0 && $carbon->second === 0)
            return $carbon->format(static::$stringFormat);
        return $carbon->toDayDateTimeString();
    }

    public function toFlatValue()
    {
        $result = $this->getValue();
        if (isset($result->end, $result->start))
            return self::formatDateTimeRange($result->start, $result->end, $result->time_zone ?? null);
        elseif (isset($result->start))
            return self::formatDateTime($result->start, $result->time_zone ?? null);
        else
            return null;
    }

    public function getValue()
    {
        return $this->config->date;
    }
}
