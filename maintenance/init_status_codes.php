<?php
require_once('../includes/db_login.php');

$sql_stmt = '
INSERT INTO status_code 
   (status_name, ordering)
   VALUES ';

$sql_values = <<<EOD
   ('UNCONFIRMED', 10),
   ('NEW', 20),
   ('ASSIGNED', 30),
   ('REOPENED', 40),
   ('RESOLVED', 50),
   ('VERIFIED', 60),
   ('CLOSED', 70)
EOD;

print "sql statement is " . $sql_stmt . $sql_values;

try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $stmt = $dbh->exec($sql_stmt . $sql_values);
    $dbh->commit();
} catch (PDOException $e) {
    print "Error on status_codes insert!: " . $e->getMessage() . "<br/>";
    die();
}

// See what happened.
try {
    foreach($dbh->query('SELECT * from status_code') as $row) {
        print_r($row);
    }
} catch (PDOException $e) {
    print "Error in standalone test: " . $e->getMessage() . "<br/>";
    die();
}
