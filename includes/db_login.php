<?php

$database = 'minibugz'; 
$username = 'minibugz_user';
$password = 'pwd';
$hostspec = 'localhost';

$phptype = 'mysql';

$dsn = "$phptype:host=$hostspec;dbname=$database";

try {
    $dbh = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

// Cheap, bad? equivalent of Python's
// if __name__ == '__main__':
//    _test()

if (!isset($_SERVER)
    or
    (array_key_exists('SCRIPT_FILENAME', $_SERVER)
     and basename($_SERVER['SCRIPT_FILENAME']) === basename( __FILE__ ))) {
    print "testing this code\n";
    try {
        foreach($dbh->query('SELECT * from status_code') as $row) {
            print_r($row);
        }
    } catch (PDOException $e) {
        print "Error in standalone test: " . $e->getMessage() . "<br/>";
        die();
    }
}
