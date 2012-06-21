<?php
require_once('Bug.php');

/**
 * creates an HTML select of status codes 
 * @returns : a select box with the passed status code (if any) selected
 *            and if not present, the description of the status code.
 */
function makeSelect( $status_id=null ) {
    $statuses = Bug::retrieveStatusList();
    print_r ($statuses);
    ## foreach ($status in $statuses) 
}

// Cheap, bad? equivalent of Python's
// if __name__ == '__main__':
//    _test()

if (!isset($_SERVER)
    or
    (array_key_exists('SCRIPT_FILENAME', $_SERVER)
     and basename($_SERVER['SCRIPT_FILENAME']) === basename( __FILE__ ))) {
    print "testing this code\n";
    makeSelect();
}
