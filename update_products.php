<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

$url = 'https://docs.google.com/spreadsheets/d/17GVpMPAreHUSEz47I2J_ttRjkbMUcj6lHfv_9f1e-7w/pub?gid=0&single=true&output=csv';
$out_path = '/var/www/html/elzup.com/data/products.json';

// ----------- get data -----------
$csv = file_get_contents($url);
$header = array();
$records = toRecords($csv, $header);
$tags = array();
$types = array();
$products = toProducts($records, $header, $tags, $types);
$root = new stdClass();
$root->types = $types;
$root->tags = $tags;
$root->products = $products;

// ----------- update data -----------
// var_dump($products);
echo json_encode($root);
file_put_contents($out_path, json_encode($root));


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
function toProducts($records, $header, &$allTags, &$allTypes) {
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
                $allTags = array_merge($allTags, $tags);
                continue;
            }
            if ($key === "type") {
                $allTypes[] = $val;
            }

            // そのまま値をセット
            $product->{$key} = $val;
            if ($val === "") {
                $product->{$key} = null;
            }
        }
        $products[] = $product;
    }
    $allTags = array_values(array_unique($allTags));
    $allTypes = array_values(array_unique($allTypes));
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

