<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Http\Controllers\Admin\OfferController;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Search and filters for the admin lists (?q=...&status=...).
 * Only allowed values are kept, so an old or edited link shows the whole list instead of an error.
 */
trait ReadsListFilters
{
    /**
     * @param  array<string, array|string>  $rules  name => allowed values, 'search' or 'date' (Y-m-d)
     * @return array<string, string>  only the filters that are set and valid
     */
    protected function listFilters(Request $request, array $rules): array
    {
        $filters = [];

        foreach ($rules as $name => $rule) {
            $value = $request->query($name);
            if (!is_string($value) || trim($value) === '') {
                continue;
            }
            $value = trim($value);

            $value = match ($rule) {
                'search' => mb_substr($value, 0, 100),
                'date'   => $this->isDate($value) ? $value : null,
                default  => in_array($value, array_map('strval', $rule), true) ? $value : null,
            };

            if ($value !== null) {
                $filters[$name] = $value;
            }
        }

        return $filters;
    }

    /**
     * Start (or end) of a day in Egypt time, in UTC for comparing with stored timestamps.
     */
    protected function dayBoundary(string $date, bool $end = false): Carbon
    {
        $day = Carbon::createFromFormat('Y-m-d', $date, OfferController::ADMIN_TIMEZONE);

        return ($end ? $day->endOfDay() : $day->startOfDay())->utc();
    }

    private function isDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value;
    }
}
