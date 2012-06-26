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
        $this->limit = 50;
        if (isDebugMode()) {
            $this->limit = 3;
        }
        foreach ($newParams as $field => $value) {
            switch ($field) {
                case 'ordering':
                    $this->ordering = addslashes($value);
                    break;

                case 'limit':
                    $this->limit = (int) $value;
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
    public function renderHTML ( ) {
        global $dbh;
        $resStr = '';
        if (isDebugMode()) {
            echo "in " .  __FILE__ . ", " . __FUNCTION__;
        }
        try {
            // Build SQL clause
            // TODO Retrieve status codes XXX joined with status names?
            //      Or do it programmatically, compare status codes with hash table values.
            $sql = 'SELECT *
                    FROM bugs
                    ORDER BY ' . $this->ordering . '
                    LIMIT ' . $this->limit;

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
                // TODO print column headings backed by cool JS,
                // then iterate over rows outputing bug_id as a link, etc.
                // Will need to get status names, either as a join or by
                // asking for the whole set.
                // TODO jQuery "more" that does XMLHTTPrequest for status history.
            }
            print '<pre>';
            print_r ($bugs);
            print '</pre>';
            // errorCode is 000.. on success.
            if (! preg_match( '/^0+$/', $result->errorCode() )) {
                $resStr = implode(' - ', $result->errorInfo());
            }
        } catch (Exception $e) {
            print "In " . __FUNCTION__ . "exception!"; print_r( $e );
            $resStr = $e->getMessage();
        }
        ?>
        </table>
        <?
        if ($resStr !== '') {
            $resStr = "ERROR, could not list bugs ($resStr)";
        }
        return $resStr;
    }

}
