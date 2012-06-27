<?php
require_once('db_login.php');
require_once('Bug.php');

class Buglist {
    /* database fields
    var $bug_id;
    var $title;
    var $description;
    var $status_id;
    var $status_last_modified;
     */

    private $ordering;
    private $limit;

    public function __construct($newParams = array() ) {
        $this->ordering = 'bug_id';
        $this->limit = 50;
        if (isDebugMode()) {
            $this->limit = 3;
        }
        foreach ($newParams as $field => $value) {
            switch ($field) {
                case 'ordering':
                    $this->ordering = trim($value);
                    // TODO in SQL join, do I have to prepend
                    break;

                case 'limit':
                    $this->limit = (int) trim($value);
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
            //      Or do it programmatically, compare status codes with array values.
            //      Or handle it upon creating bug object?
            $sth = $dbh->prepare('
                SELECT bugs.bug_id, bugs.title, bugs.description,
                       bugs.status_id, bugs.status_last_modified,
                       status_code.status_name, status_code.ordering, status_code.active
                FROM `bugs`
                LEFT JOIN `status_code` ON bugs.status_id = status_code.status_id
                ORDER BY :ordering
                LIMIT :limit
                ');
            $sth->bindValue( ':ordering', $this->ordering );
            // Bug http://bugs.php.net/bug.php?id=44639 , limit param appears as quoted string!
            $sth->bindValue( ':limit', $this->limit, PDO::PARAM_INT );
            $sth->setFetchMode(PDO::FETCH_INTO, new Bug());
            $sth->execute();
            if (isDebugMode()) {
                print "in " . __FUNCTION__ . " SQL statement="; $sth->debugDumpParams();
            }
            ?>
            <table>
              <tr>
                <th>Bug</th>
                <th>Title</th>
                <th width="40%">Description</th>
                <!-- status code -->
                <th>Status</th>
                <th>Timestamp</th>
              </tr>
            <?
            while ( $bug = $sth->fetch() ) {
                if (isDebugMode()) {
                    echo "in " . __FUNCTION__ . "After query & fetch bug is:"; print_r ($bug);
                    // TODO print column headings backed by cool JS,
                    // then iterate over rows outputing bug_id as a link, etc.
                    // Will need to get status names, either as a join or by
                    // asking for the whole set.
                }
                // TODO jQuery "more" that does XMLHTTPrequest for status history.
                ?>
                <tr>
                  <td><?= $bug->bug_id ?></td>
                  <td><?= $bug->title ?></td>
                  <td><?= $bug->description ?></td>
                  <td><?= $bug->status_name ?></td>
                  <td><?= $bug->status_last_modified ?></td>
                </tr>
                <?
                // errorCode is 000.. on success.
                if (! preg_match( '/^0+$/', $sth->errorCode() )) {
                    $resStr = implode(' - ', $sth->errorInfo());
                }
            }
        } catch (Exception $e) {
            print "In " . __FUNCTION__ . "exception!"; print_r( $e );
            $resStr = $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine();
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
