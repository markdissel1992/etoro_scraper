<?php
$db = parse_ini_file("database.ini");

$username = $db['user'];
$password = $db['pass'];
$dbname = $db['name'];
$servername = $db['host'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getInvestors($conn) {
    $sql = 'SELECT id, name FROM investors';
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $investors = Array();
        while($row = $result->fetch_assoc()) {
            $investors[] = Array($row['id'], $row['name']);
        }
        return $investors;
    } else {
        return Array();
    }
}

function setMonthlyInvestorData($customerId, $data, $conn) {
    foreach($data as $key=>$value) {
        $stmt = $conn->prepare("INSERT INTO investing_history (customer_id, month_year, profit) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE profit=VALUES(profit)");
        $datetime = date("Y-m-d H:i:s", strtotime($key));
        $stmt->bind_param("iss", $customerId, $datetime, $value);
        $stmt->execute();
        printf($stmt->error);
    }
}

function setCustomerStats($customerId, $customerStats, $conn) {
    $stmt = $conn->prepare("UPDATE investors SET trades_per_week = ?, total_trades = ?, average_hold_time = ?, investing_score = ?, profitable_weeks = ?, average_score = ?, total_score_24_months = ?, last_6_months = ?, amount_data_months = ? WHERE id = ?");
    $stmt->bind_param("sssssssssi", $customerStats['tradesPerWeek'], $customerStats['totalTrades'], $customerStats['avgHoldingTimeInMonths'], $customerStats['investmentScore'], $customerStats['profitableWeeks'], $customerStats['avgScore'], $customerStats['totalLast24Months'], $customerStats['totalLast6Months'], $customerStats['totalMonths'], $customerId);
    $stmt->execute();
    printf($stmt->error);
}

function setInvestors($investors, $conn) {
    foreach($investors as $investor) {
        $stmt = $conn->prepare("INSERT INTO investors (name) VALUES (?)");
        $stmt->bind_param("s", $investor['UserName']);
        $stmt->execute();
        printf($stmt->error);
    }
}

function setInvestmentData($customerId, $investmentData, $conn) {
    foreach($investmentData as $investment) {
        $stmt = $conn->prepare("INSERT INTO investments (customer_id, market, action, invested, profit, value) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddd", $customerId, $investment['market'], $investment['action'], $investment['invested'], $investment['profit'], $investment['value']);
        $stmt->execute();
        printf($stmt->error);
    }
}