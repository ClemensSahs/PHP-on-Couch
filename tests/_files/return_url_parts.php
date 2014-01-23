<?php

header('Content-Type: application/json');

$return = array(
    'path'  => $_SERVER['REQUEST_URI'],
    'query' => $_SERVER['QUERY_STRING'],
    'port'  => $_SERVER['SERVER_PORT'],
);


echo json_encode($return, true);
