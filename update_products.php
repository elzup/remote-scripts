<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

$url = 'https://docs.google.com/spreadsheets/d/17GVpMPAreHUSEz47I2J_ttRjkbMUcj6lHfv_9f1e-7w/pub?gid=0&single=true&output=csv';
$out_path = '/var/www/html/elzup.com/products.json';

// ----------- get data -----------
$csv = file_get_contents($url);
$header = array();
$records = toRecords($csv, $header);
$products = toProducts($records, $header);

// ----------- update data -----------
// var_dump($products);
echo json_encode($products);
file_put_contents($out_path, json_encode($products));


function toRecords($csv, &$header) {
    $lines = explode("\r\n", trim($csv));
    $records = Array();
    foreach ($lines as $line) {
        $records[] = explode(',', $line);
    }
    $header = array_shift($records);
    return $records;
}


// ----------- to object -----------
function toProducts($records, $header) {
    $products = Array();
    foreach ($records as $record) {
        $product = new stdClass();
        foreach ($header as $i=>$key) {
            $val = $record[$i];
            if ($key === "members") {
                $product->members = parse_members($val);
                continue;
            }
            if ($key === "tags") {
                $tags = explode('-', $val);
                $product->tags = $tags;
                continue;
            }

            // そのまま値をセット
            $product->{$key} = $val;
            if ($val === "") {
                $product->{$key} = null;
            }
        }
        $products[] = $product;
    }
    return $products;
}

function parse_members($line) {
    $members = Array();
    if ($line === "") {
        return $members;
    }
    foreach (explode('-', $line) as $member_str) {
        $member = new stdClass();
        $infos = explode(':', $member_str);
        // var_dump($infos);
        $member->name = $infos[0];
        $member->discription = $infos[1];
        $members[] = $member;
    }
    return $members;
}

