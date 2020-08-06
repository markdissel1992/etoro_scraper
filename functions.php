<?php

function getContents($str, $startDelimiter, $endDelimiter) {
    /**
     * Find substring between two predefined delimiters and returns all occurences in an array
     * @param string $str The string you want to search in
     * @param string $startDelimiter first substring you want to look for
     * @param string $endDelimiter Second substring you want to look for
     * @return array All the occurences found
     */
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

function getMonthsAndYears($years) {
    /**
     * This function returns an array with the years and months found on the investor page
     * @param array $years An array with all the years found on the investor page
     * @return array An optimized array with the months and years
     */
    $months = array("01","02","03","04","05","06","07","08","09","10","11","12");
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

function getIntAndFloats($raw_values) {
    /**
     * Strips all the non integers and floats from an array
     * @param array $years An array with values which needs to be checked
     * @return array An optimized array with only integers and floats
     */
    $values = array();
    foreach ($raw_values as $value) {
        if (preg_match("/^\d+$/", $value) || is_numeric($value)) {
            array_push($values, $value);
        }
    }
    return $values;
}

function getMonthlyData($monthsAndYears, $values) {
    /**
     * Combines an array with months and years with a value array
     * @param   array $monthsAndYears An array with the dates from the investor page
     * @param  array $values the values from the investor page
     * @return array combined array with the dates and the corresponding values
     */
    $monthlyData = array();
    $i = 0;
    foreach($monthsAndYears as $key) {
        if(array_key_exists($i, $values)) {
            $monthlyData[$key] = $values[$i];
        }
        $i++;
    }
    return array_filter($monthlyData);
}

function getValuesPerMonthAndYear($html) {
    /**
     * Combines an array with months and years with a value array
     * @param   array $monthsAndYears An array with the dates from the investor page
     * @param  array $values the values from the investor page
     * @return array combined array with the dates and the corresponding values
     */
    $years = getContents($html, 'performance-chart-slot year desk">', '</div>');
    $raw_values = getContents($html, 'ng-star-inserted">', '</div>');
    $monthsAndYears = getMonthsAndYears($years);
    $values = getIntAndFloats($raw_values);
    return getMonthlyData($monthsAndYears, $values);
}

function getCustomerStats($html, $data) {
    /**
     *
     * @param string $html File with the code generated from the investor page
     * @param array $data Array with the monthly value data of the investor
     * @return array An array with all the values specified in the database
     */
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
    /**
     * Transforms the average holding time to months
     * @param string $time The time value and unit found on the investor page
     * @return string Returns the average holding time in months
     */
    $a = preg_split("~\s+~",$time[0]);
    $timeUnit = $a[1];
    $time = $a[0];
    if($timeUnit == "Days" || $timeUnit == "Day") {
        return ( (1/30) * $time);
    }
    elseif($timeUnit == "Years" || $timeUnit == "Year"){
        return $time * 12;
    }
    elseif($timeUnit == "Hours" || $timeUnit == "Hour"){
        return $time * 730.5;
    }
    return $time;
}


function getInvestments($html) {
    /**
     * Gets the investment data from the HTML files
     * @param string $html A HTML string from the investor portfolio page
     * @return array Returns a key-value array per investment with the market, action, invested, profit and value (last three in percentages)
     */
    $markets = getContents($html, 'i-portfolio-table-name-symbol ng-binding">', '</div>');
    $actions = getContents($html, '<span ng-if="!item.MirrorID" class="ng-binding ng-scope">', '</span>');
    $percentages = getContents($html, '<ui-table-cell class="ng-binding">', '</ui-table-cell>');
    $profits = getContents($html, '<ui-table-cell ng-class="{negative: item.NetProfit < 0, positive: item.NetProfit > 0}" class="ng-binding', '</ui-table-cell>');
    return parseInvestmentData($markets, $actions, $percentages, $profits);
}

function parseInvestmentData($markets, $actions, $percentages, $profits) {
    /**
     * Parses the investment data in a readable array
     * @param array $markets An array containing the invested markets
     * @param array $actions An array containing the actions (Buying or Selling)
     * @param array $percentages An array containing the invested or value percentages
     * @param array $profits An array containing the profit in percentages
     * @return array Returns a key-value array per investment with the market, action, invested, profit and value (last three in percentages)
     */
    foreach($profits as $key=>$profit) {
        $profit = substr($profit, strpos($profit, ">") + 1);
        $profits[$key] = $profit;
    }
    $investmentData = array();
    $x = 0;
    for ($i = 0; $i < count($markets); $i++) {
        $investmentData[$i] = array("market" => $markets[$i],  "action" => $actions[$i], "invested" => $percentages[$x], "profit" => $profits[$i], "value" => $percentages[$x + 1]);
        $x += 2;
    }
    foreach($investmentData as $key=>$investments) {
        foreach($investments as $subkey=>$investment) {
            $investmentData[$key][$subkey] = str_replace("%", "", $investment);
        }
    }
    return $investmentData;
}
