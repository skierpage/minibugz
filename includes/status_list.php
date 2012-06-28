<?php
require_once('Bug.php');
require_once('util.php');

/**
 * creates an HTML select of status codes 
 * @returns : a select box with the passed status code (if any) selected
 *            and if not present, the description of the status code.
 */
function makeSelect( $status_id=null ) {
    $outStr = '';
    $found = false;
    $statuses = Bug::retrieveStatusList();
    $outStr .= '<select name="status_id">';
    $outStr .= '<option value="">--</option>';
    foreach ($statuses as $status) {
        if ($status['active']) {
            $outStr .= '<option value="' . $status['status_id'] . '"';
            // $status_id from the form is a string, but do a loose comparison here.
            if ($status_id == $status['status_id']) {
                $outStr .= ' selected="selected"';
                $found = true;
            }
            $outStr .= '>' . $status['status_name'] . "</option>\n";
        }
    }
    $outStr .= '</select>';
    if ($status_id !== null and $status_id !== '' and ! $found ) {
        $outStr .= '<div class="error"> current status ' . $status_id . ' not found</div>';
    }
    return $outStr;
}

// Cheap, bad? equivalent of Python's
// if __name__ == '__main__':
//    _test()
if (!isset($_SERVER)
    or
    (array_key_exists('SCRIPT_FILENAME', $_SERVER)
     and basename($_SERVER['SCRIPT_FILENAME']) === basename( __FILE__ ))) {
    print "testing this code\n";
    print makeSelect( 666 );
}
