<?php

namespace Baconfy\Analytics\Services\Visits;

use Baconfy\Analytics\Services\GetByHours;
use Carbon\Carbon;
use DB;

class GetVisitByPeriod
{

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return mixed
     */
    public function fire(Carbon $startDate, Carbon $endDate)
    {
        return $this->getData($startDate, $endDate);
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return mixed
     */
    public function csv(Carbon $startDate, Carbon $endDate)
    {
        $data = $this->getData($startDate, $endDate);

        $lines[] = [GetByHours::title($startDate, $endDate), trans('analytics::messages.visits'), trans('analytics::messages.unique')];

        if (count($data)) {
            foreach ($data as $record) {
                $lines[] = get_object_vars($record);
            }
        }

        $output = '';
        if (count($lines)) {
            foreach ($lines as $line) {
                $output .= implode(',', $line) . "\n";
            }
        }

        return $output;
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return mixed
     */
    private function getData(Carbon $startDate, Carbon $endDate)
    {
        $key = GetByHours::key($startDate, $endDate);

        $Select = DB::table('analytcs_visits')->select(
            DB::raw("date_format(created_at, '$key') as `key`"),
            DB::raw("count(uuid) as total"),
            DB::raw("count(distinct uuid) as uniques")
        );

        $Select->where('created_at', '>=', "$startDate 00:00:00");
        $Select->where('created_at', '<=', "$endDate 23:59:59");
        $Select->groupBy('key');

        return $Select->get();
    }

}