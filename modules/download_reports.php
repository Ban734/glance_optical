<?php
// download_reports.php
session_start();
include("../engines/db.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Use the same GET filters as dashboard (simple re-use)
$filter_date_from = $_GET['from'] ?? '';
$filter_date_to   = $_GET['to'] ?? '';
$filter_search    = trim($_GET['q'] ?? '');
$filter_payment   = $_GET['payment'] ?? '';

$where = [];
$where[] = "archived = 0"; // only non-archived by default
if ($filter_date_from) $where[] = "date_of_sale >= '". $conn->real_escape_string($filter_date_from) ."'";
if ($filter_date_to)   $where[] = "date_of_sale <= '". $conn->real_escape_string($filter_date_to) ."'";
if ($filter_payment)   $where[] = "payment_method = '". $conn->real_escape_string($filter_payment) ."'";
if ($filter_search) {
    $q = $conn->real_escape_string($filter_search);
    $where[] = "(product_name LIKE '%$q%' OR customer_name LIKE '%$q%' OR recorded_by LIKE '%$q%')";
}
$whereSQL = count($where) ? implode(' AND ', $where) : '1=1';

$query = "SELECT id, date_of_sale, recorded_at, product_name, quantity, unit_price, total, customer_name, payment_method, card_type, recorded_by FROM sales_records WHERE $whereSQL ORDER BY date_of_sale DESC, recorded_at DESC";
$res = mysqli_query($conn, $query);

// Output CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=glance_reports_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID','Date','Time','Product','Qty','Unit Price','Total','Customer','Payment','Card Type','Recorded By']);

while ($r = mysqli_fetch_assoc($res)) {
    $row = [
        $r['id'],
        $r['date_of_sale'],
        $r['recorded_at'],
        $r['product_name'],
        $r['quantity'],
        number_format($r['unit_price'],2),
        number_format($r['total'],2),
        $r['customer_name'],
        $r['payment_method'],
        $r['card_type'],
        $r['recorded_by']
    ];
    fputcsv($out, $row);
}
fclose($out);
exit();
