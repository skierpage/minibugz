<?php
require_once('db_login.php');

class Bug {
    /* database fields */
    var $bug_id;
    var $title;
    var $description;
    var $status_id;
    var $status_last_modified;

    public function __construct($newParams = array() ) {
        // if only bug_id supplied, then go look it up.
        if (count($newParams === 1) and isset($newParams['bug_id'])) {
            $this->bug_id = (int) $newParams['bug_id'];
            $this->retrieve();
        }
    }

    public function getStatusDesc() {
        return self::retrieveStatusDesc($this->status_code);
    }

    /**
     * database access
     */
    private function retrieve () {
        global $dbh;
        if (isDebugMode()) {
            echo "in Bug, go retrieve " . $this->bug_id;
        }
        if (! is_int($this->bug_id)) {
            throw new Exception("INTERNAL: bug_id not integer in . __FUNCTION__"); 
        }
        // TODO Assert bug_id is integer
        $sql = 'SELECT *
                FROM bugs
                WHERE bug_id = ' . $this->bug_id;
        $result = $dbh->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        if (isDebugMode()) {
            echo "in Bug->retrieve(), After query & fetch row is:";
            print_r($row);
        }
        $result->closeCursor();
        if ($row === false) {
            throw new Exception("NOT FOUND");
        } else {
            $this->bug_id = $row['bug_id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->status_id = $row['status_id'];
            $this->status_last_modified = $row['status_last_modified'];
            // ? Do we want to fill in status_description?
        }
    }

    private function create () {
        if (isDebugMode()) {
            echo "in Bug, go create Bug:"; var_dump ($this);
        }
        $sql = 'INSERT INTO bugs
                (title, description, status_id)
                VALUES (:title, :description, :status_id)';
        $stmt = $dbh->prepare( $sql );
        $stmt = $dbh->execute(array('title' => $this->title,
                                    'description' => $this->description,
                                    'status' => $this->status_id) );
    }

    private function update () {
        if (isDebugMode()) {
            echo "in Bug, go update Bug:"; var_dump ($this);
        }
    }

    private static function retrieveStatusDesc ( $status_id ) {
        global $dbh;
        if (isDebugMode()) {
            echo "in Bug, retrieveStatusDesc for status ID " . $status_id;
        }
        if (! is_int($this->status_id)) {
            throw new Exception("INTERNAL: status_id not integer in . __FUNCTION__"); 
        }
        $sql = 'SELECT status_name
                FROM status_code
                WHERE status_id = ' . $status_id;
        $result = $dbh->query($sql);
        $row = $result->fetch();
        if (isDebugMode()) {
            echo "in " . __FUNCTION__ . "After query & fetch row is:";
            print_r($row);
        }
        $result->closeCursor();
        if ($row === false) {
            return '';
        } else {
            return $row;
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->status_id = $row['status_id'];
            $this->status_last_modified = $row['status_last_modified'];
            // ? Do we want to fill in status_description?
        }
    }

    /**
     * return all statues
     * @param $retrieveOnlyActive : set true to only return active statuses.
     *                              Some bug might have an old status_id.
     * @returns : associative array in ordering organized by status_id.
     */
    public static function retrieveStatusList ( $retrieveOnlyActive=false ) {
        global $dbh;
        if (isDebugMode()) {
            echo "in Bug " . __FUNCTION__;
        }
        // Retrieve even not active records
        $sql = 'SELECT *
                FROM status_code';
        if ($retrieveOnlyActive) {
                $sql .= ' WHERE active = 1';
        }
        $sql .=  ' ORDER BY ordering';
        $result = $dbh->query($sql);
        $statuses = $result->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        if (isDebugMode()) {
            echo "in " . __FUNCTION__ . "After query & fetch row is:";
            print_r($statuses);
        }
        return ($statuses);
    }

}
