<?php namespace Notion;

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class PropertyValue
{
    public static $preferredTimezone = null;
    public static $stringFormat = 'D, M j, Y';
    public \stdClass $config;

    /**
     * @param \stdClass $config
     */
    public function __construct(\stdClass $config)
    {
        $this->config = $config;
    }

    public function value()
    {
        return $this->flatten($this->config);
    }

    protected function flatten(\stdClass $config)
    {
        if (!isset($config->type) || !isset($config->{$config->type}))
            return null;
        return match ($config->type) {
            'date' => $this->flattenDate($config),
            'checkbox' => $this->flattenCheckbox($config),
            'relation' => $this->flattenRelation($config),
            'rollup' => $this->flattenRollup($config),
            'formula' => $this->flattenFormula($config),
            'rich_text' => $this->flattenRichText($config),
            'select' => $this->flattenSelect($config),
            'multi_select' => $this->flattenMultiSelect($config),
            'title' => $this->flattenTitle($config),
            default => $config->{$config->type}
        };
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

    /**
     * @return string|null
     */
    protected function flattenDate(\stdClass $config): ?string
    {
        $result = $config->date ?? null;
        if (isset($result->end, $result->start))
            return self::formatDateTimeRange($result->start, $result->end, $result->time_zone ?? null);
        elseif (isset($result->start))
            return self::formatDateTime($result->start, $result->time_zone ?? null);
        else
            return null;
    }

    /**
     * @return string|null
     */
    protected function flattenCheckbox(\stdClass $config): ?int
    {
        if (!is_bool($config->checkbox)) {
            return 0;
        }

        return $config->checkbox ? 1 : 0;
    }


    protected function flattenMultiSelect(\stdClass $config)
    {
        if (!is_array($config->multi_select))
            return null;
        $values = [];
        foreach ($config->multi_select as $select) {
            $value = $select->name ?? $select->id ?? null;
            if (isset($value))
                $values[] = $value;
        }
        return $values;
    }

    protected function flattenSelect(\stdClass $config)
    {
        if (!is_object($config->select))
            return null;
        return $config->select->name ?? $config->select->id ?? null;
    }

    protected function flattenTitle(\stdClass $config)
    {
        if (isset($config->title->plain_text)) {
            return $config->title->text->plain_text;
        }
        if (!is_array($config->title)) {
            return '';
        }
        $parts = [];
        foreach ($config->title as $text) {
            $content = $text->plain_text ?? $text->text->content ?? null;
            if (isset($content))
                $parts[] = $content;
        }
        return trim(join(PHP_EOL, $parts));
    }

    protected function flattenRichText(\stdClass $config)
    {
        if (!empty($config->rich_text)) {
            return $config->rich_text[0]->text->content ?? '';
        } else {
            return '';
        }
    }

    protected function flattenRollup(\stdClass $config)
    {
        if (isset($config->rollup->array))
            return $this->flatten($config->rollup->array[0]);
        return $config->rollup->{$config->rollup->type};
    }

    protected function flattenFormula(\stdClass $config)
    {
        $value = $config->formula->{$config->formula->type};
        if (is_array($value) && isset($value[0]->type))
            return $value[0]->{$value[0]->type};
        if (is_object($value))
            return $this->flatten($value);
        return $value;
    }

    protected function flattenRelation(\stdClass $config)
    {
        $value = $config->relation;
        $result = null;
        if (isset($value) && is_array($value)) {
            $result = [];
            foreach ($value as $related) {
                $result[] = data_get($related, 'id');
            }
            return array_unique($result);
        } elseif (is_object($value) && isset($value->id)) {
            $result = [$value->id];
        }
        return $result;
    }
}
