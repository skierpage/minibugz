<?php
require_once('db_login.php');
require_once('util.php');
require_once('Bug.php');

class Bughistory {
    /* database fields
    var $bug_id;
    var $modified;
    var $status_id;
     */

    const QUERY_LIMIT = 250;
    /**
     * Generate HTML of status changes
     *
     * @returns : error string if errors.
     */
    public static function renderHTML ( $bug_id ) {
        global $dbh;
        $resStr = '';
        $statuses = Bug::retrieveStatusList();
        try {
            // Build SQL clause
            $sth = $dbh->prepare('
                SELECT modified, status_id
                FROM `bug_history`
                WHERE bug_id=:bug_id
                ORDER BY modified DESC
                LIMIT :limit
                ');
            $sth->setFetchMode(PDO::FETCH_INTO, new Bughistory());
            // Bug http://bugs.php.net/bug.php?id=44639 , limit param appears as quoted string!
            $sth->bindValue( ':limit', self::QUERY_LIMIT, PDO::PARAM_INT );
            $sth->bindValue( ':bug_id', $bug_id );
            $sth->execute();
            if (isDebugMode()) {
                print "in " . __FUNCTION__ . " SQL statement="; $sth->debugDumpParams();
            }
            ?>
            <div>
              Status history for bug <?= $bug_id ?>
            </div>
            <table id="bughistory">
              <tr>
                <!-- status code -->
                <th>Status</th>
                <th>Changed on</th>
              </tr>
            <?
            while ( $bughistory = $sth->fetch() ) {
                ?>
                <tr>
                  <td><?= $bughistory->status_id ?></a></td>
                  <td style="white-space:nowrap;"><?= $bughistory->modified ?></td>
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
            $resStr = "ERROR, could not list bug history for $bugid ($resStr)";
        }
        return $resStr;
    }
}

// Cheap, bad? equivalent of Python's
// if __name__ == '__main__':
//    _test()
if (!isset($_SERVER)
    or
    (array_key_exists('SCRIPT_FILENAME', $_SERVER)
     and basename($_SERVER['SCRIPT_FILENAME']) === basename( __FILE__ ))) {
    print "testing this code\n";
    print Bughistory::renderHTML( 1239 );
}