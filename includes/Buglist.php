<?php
require_once('db_login.php');

class Buglist {
    /* database fields */
    var $bug_id;
    var $title;
    var $description;
    var $status_id;
    var $status_last_modified;


    /**
     * Generate HTML list
     * @param $params: array all the query string information TODO should decouple this.
     *
     * @returns : error string if errors.
     */
    public static function renderHTML ( $params) {
        global $dbh;
        if (isDebugMode()) {
            echo "in " .  __FILE__ . ", " . __FUNCTION__;
        }
        // Build SQL clause
        // Retrieve even not active records
        $sql = 'SELECT *
                FROM bugs';
        /* process parameters like order=
        if ($retrieveOnlyActive) {
                $sql .= ' WHERE active = 1';
        }
        $sql .=  ' ORDER BY ordering';
        */

        echo "in " .  __FILE__ . ", " . __FUNCTION__ . ", sql=" . $sql;
        $result = $dbh->query($sql);
        $bugs = $result->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        if (isDebugMode()) {
            echo "in " . __FUNCTION__ . "After query & fetch row is:";
            print_r($statuses);
            // TODO: print column headings backed by cool JS,
            // then iterate over rows outputing bug_id as a link, etc.
            // Will need to get status names, either as a join or by
            // asking for the whole set.
        }
        print '<pre>';
        print_r ($bugs);
        print '</pre>';
        return ('');
    }

}
