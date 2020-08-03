<?php
function getContents($str, $startDelimiter, $endDelimiter) {
    $contents = array();
    $startDelimiterLength = strlen($startDelimiter);
    $endDelimiterLength = strlen($endDelimiter);
    $startFrom = $contentStart = $contentEnd = 0;
    while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
        $contentStart += $startDelimiterLength;
        $contentEnd = strpos($str, $endDelimiter, $contentStart);
        if (false === $contentEnd) {
            break;
        }
        $contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
        $startFrom = $contentEnd + $endDelimiterLength;
    }

    return $contents;
}

function getMonthsAndYears($years, $months) {
    $monthsAndYears = array();
    $currentMonth = date('m');
    ($currentMonth[0] == 0 ? $currentMonth = $currentMonth[1] : $currentMonth);
    foreach($years as $year) {
        foreach(array_reverse($months) as $month) {
            array_push($monthsAndYears,str_replace(' ', '', $year) . "-" . $month);
        }
    }
    $monthsAndYears = array_slice($monthsAndYears, (12 - $currentMonth));
    return $monthsAndYears;
}

function getParsedValues($raw_values) {
    $values = array();
    foreach ($raw_values as $value) {
        if (preg_match("/^\d+$/", $value) || is_numeric($value)) {
            array_push($values, $value);
        }
    }
    return $values;
}

function getMonthlyData($monthsAndYears, $values) {
    $monthlyData = array();
    $i = 0;
    foreach($monthsAndYears as $key) {
        $monthlyData[$key] = $values[$i];
        $i++;
    }
    return $monthlyData;
}
function parseMonthlyData($html) {
    $years = getContents($html, 'performance-chart-slot year desk">', '</div>');
    $months = array("01","02","03","04","05","06","07","08","09","10","11","12");
    $raw_values = getContents($html, 'ng-star-inserted">', '</div>');
    $monthsAndYears = getMonthsAndYears($years, $months);
    $values = getParsedValues($raw_values);
    return getMonthlyData($monthsAndYears, $values);
}

function getCustomerStats($html, $data) {
    $tradesPerWeek = getContents($html, 'id="stats-user-trade-info" class="top-trade-profit-procent">', '</span>')[0];
    $avgHoldingTime = getContents($html, 'automation-id="stats-user-holding-info" class="top-trade-profit-procent">', '</span>');
    $profitableWeeks = trim(getContents($html, 'automation-id="stats-user-profit-info" class="top-trade-profit-procent">', '</span>')[0], '%');
    $avgHoldingTimeInMonths = round(getAvgHoldingTimeInMonths($avgHoldingTime), 2);
    $totalTrades = getContents($html, 'lass="performance-num"> ', ' <span')[0];
    // Calculate the profit over the last 6 months
    $totalLast6Months = $totalLast24Months = $totalMonths = $total = 0;
    foreach($data as $profit) {
        if($totalMonths < 6) {
            $totalLast6Months += $profit;
        }
        elseif($totalMonths < 24) {
            $totalLast24Months += $profit;
        }
        $total += $profit;
        $totalMonths++;
    }
    $avgScore = $total / $totalMonths;
    $investmentScore = ((50 * $avgScore) + $totalLast24Months + ($totalMonths * 10) + ($totalLast6Months * 2) * ($profitableWeeks / 100)) * 0.1;
    return array("investmentScore" => $investmentScore, "avgScore" => $avgScore, "tradesPerWeek" => $tradesPerWeek, "avgHoldingTimeInMonths" => $avgHoldingTimeInMonths, "totalTrades" => "$totalTrades", "totalLast6Months" => $totalLast6Months, "totalLast24Months" => $totalLast24Months, "totalMonths" => $totalMonths, "profitableWeeks" => $profitableWeeks);
}

function getAvgHoldingTimeInMonths($time) {
    $a = preg_split("~\s+~",$time[0]);
    $timeUnit = $a[1];
    $time = $a[0];
    if($timeUnit == "Days" || $timeUnit == "Day") {
        return ( (1/30) * $time);
    }
    elseif($timeUnit == "Year" || $timeUnit == "Years"){
        return $time * 12;
    }
    elseif($timeUnit == "Hours" || $timeUnit == "Hour"){
        return $time * 730.5;
    }
    elseif($timeUnit == "Month" || $timeUnit == "Months"){
        return $time;
    }
}

