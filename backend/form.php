<?php

require_once 'config.php';

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once 'sdbh.php';
$dbh = new sdbh();

function validVal($value)
{
    $value = trim($value);
    $value = stripslashes($value);
    $value = strip_tags($value);
    $value = htmlspecialchars($value);

    return $value;
}


if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $productId = $_POST['productId'];
    $days = $_POST['days'];
    $services = $_POST['services'];

    $days = validVal($days);

    if(empty($days)) {
        echo json_encode(['error' => 'Это поле не может быть пустым!']);
        exit;
    }

    

    if($days < 1 || $days > 30 || !is_numeric($days)) {
        echo json_encode(['error' => 'Укажите количество дней от 1 до 30']);
        exit;
    }

    $product = $dbh->mselect_rows('a25_products', ['id' => $productId], 0, 1, 'id')[0];

    if(!empty($product)) {
        $price = $product['PRICE'];
        $tariff = unserialize($product['TARIFF']);
    }

    foreach ($tariff as $key => $value) {
        if ($days >= $key) {
            $price = $value;
        }
    }

    $totalSum = $days * $price;

    $totalSumService = 0;

    if(!empty($services)) {
        foreach($services as $service) {
            $totalSumService += $service * $days;
        }
    }

    $totalSum = $totalSumService + $totalSum;
    echo json_encode(['success' => 'Форма успешно обработана', 'totalSum' => $totalSum]);
    exit;

}