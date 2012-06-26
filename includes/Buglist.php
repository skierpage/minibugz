<?php
require_once('db_login.php');

class Buglist {
    /* database fields
    var $bug_id;
    var $title;
    var $description;
    var $status_id;
    var $status_last_modified;
     */

    public $ordering;

    public function __construct($newParams = array() ) {
        $this->ordering = 'bug_id';

        foreach ($newParams as $field => $value) {
            switch ($field) {
                case 'ordering':
                    $this->ordering = $value;
                    break;
                default:
                    // ignore random stuff.
            }
        }
    }

    /**
     * Generate HTML list
     * @param $params: array all the query string information TODO should decouple this.
     *
     * @returns : error string if errors.
     */
    public function renderHTML ( $params) {
        global $dbh;
        if (isDebugMode()) {
            echo "in " .  __FILE__ . ", " . __FUNCTION__;
        }
        // Build SQL clause
        // Retrieve even not active records
        $sql = 'SELECT *
                FROM bugs
                ORDER BY ' . $this->ordering;

        echo "in " .  __FILE__ . ", " . __FUNCTION__ . ", sql=" . $sql;
        ?>
        <table>
          <tr>
            <th>Bug</th>
            <th>Title</th>
            <th width="40%">Description</th>
            <th>Status</th>
            <th>Timestamp</th>
          </tr>
        </table>
        <?
        $result = $dbh->query($sql);
        $bugs = $result->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        if (isDebugMode()) {
            echo "in " . __FUNCTION__ . "After query & fetch row is:";
            print_r($statuses);
            // TODO print column headings backed by cool JS,
            // then iterate over rows outputing bug_id as a link, etc.
            // Will need to get status names, either as a join or by
            // asking for the whole set.
            // TODO jQuery "more" that does XMLHTTPrequest for status history.
        }
        print '<pre>';
        print_r ($bugs);
        print '</pre>';
        return ('');
    }

}
