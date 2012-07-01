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
                    // Note in SQL join, it seems I do not have to prepend
                    // "status." or "bugs." to fields if they are unique.
                    if (isDebugMode()) {
                        echo "in " .  __FILE__ . ", " . __FUNCTION__; print " ordering is $this->ordering\n";
                    }
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
     * Generate HTML bug
     * in SQL this LEFT JOINs bug with status_code to get the status description.
     *
     * @returns : error string if errors.
     */
    public function renderHTML ( ) {
        global $dbh;
        global $samePageURL;
        $listURL = $samePageURL . '&action=list';
        $resStr = '';
        try {
            // Build SQL clause
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
            if (isDebugMode()) {
                echo "in " .  __FILE__ . ", " . __FUNCTION__; print " ordering=$this->ordering\n";
            }
            // Bug http://bugs.php.net/bug.php?id=44639 , limit param appears as quoted string!
            $sth->bindValue( ':limit', $this->limit, PDO::PARAM_INT );
            $sth->setFetchMode(PDO::FETCH_INTO, new Bug());
            $sth->execute();
            if (isDebugMode()) {
                print "in " . __FUNCTION__ . " SQL statement="; $sth->debugDumpParams();
            }
            ?>
            <table id="buglist">
              <tr>
                <th>Bug</th>
                <th>Title</th>
                <th width="40%">Description</th>
                <!-- status code -->
                <th>Status</th>
                <th>Status last mod</th>
              </tr>
            <?
            while ( $bug = $sth->fetch() ) {
                // TODO jQuery "more" that does XMLHTTPrequest for status history.
                ?>
                <tr>
                  <td><a href="<?= $samePageURL . '&id=' . $bug->bug_id ?>"><?= $bug->bug_id ?></a></td>
                  <td><?= $bug->title ?></td>
                  <td><?= $bug->description ?></td>
                  <td><?= $bug->status_name ?></td>
                  <td style="white-space:nowrap;"><?= $bug->status_last_modified ?></td>
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
