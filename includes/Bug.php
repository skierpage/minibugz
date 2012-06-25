<?php
require_once('db_login.php');
require_once('Error.php');

class Bug {
    /* database fields */
    var $bug_id;
    var $title;
    var $description;
    var $status_id;
    var $status_last_modified;

    /**
     * constructor
     * @params $newParams : array of key-value pairs that should correspond to DB fields.
     * @returns new bug object, or throws exception with TODO validation errors object.
     */
    public function __construct($newParams = array() ) {
        // Pull known keys from newParams
        // XXX If I unset keys as I consume them,
        //     does the caller see the depleted newParams?
        foreach ($newParams as $field => $value) {
            switch ($field) {
                case 'bug_id':
                    //XXX Should
                    throw new Exception("INTERNAL: bug_id " . $value . "supplied but creating a bug!?"); 
                    break;
                case 'title':
                    $this->title = $value;
                    break;
                case 'description':
                    $this->description = $value;
                    break;
                case 'status_id':
                    // XXX TODO: this results in invalid 'abc' turning into valid 0.
                    // but without it, "123" from text input is a string.
                    $this->status_id = $value;
                    break;

                default:
                    // ignore random stuff.
            }
        }
    }

    /**
     * @param Error $err ; Error object (errors may accumulate in it).
     * @return boolean : true if passed validation, false otherwise
     */
    public function validate( Error $error) {
        $isValid = true;
        // OK not to have a bug_id? Or force it to null for a new bug.
        if ( isset($bug->bug_id) and ! is_int( $this->bug_id ) ) {
            $isValid = false;
            $error->validationErr( array (
                'field' => 'bug_id',
                'errStr' => 'must be an integer'
            ) );
        }
        if ( ! is_numeric( $this->status_id ) ) {
            $isValid = false;
            $error->validationErr( array (
                'field' => 'status_id',
                'errStr' => 'must be an integer'
            ) );
        } elseif ( false and $this->status_id  /* TODO validate status_id */) {
            $isValid = false;
            $error->validationErr( array (
                'field' => 'status_id',
                'errStr' => 'does not match any active status code'
            ) );
        }
        // XXX title, description, are required fields, length, etc.

        return $isValid;
    }

    public function getStatusDesc() {
        return self::retrieveStatusDesc($this->status_code);
    }

    /**
     * Database Access
     */

    /**
     * retrieve bug
     * @param int $bug_id
     * throws error? returns error?
     */
    public function retrieve ( $bug_id ) {
        global $dbh;
        if (! is_int($bug_id)) {
            throw new Exception("INTERNAL: bug_id $bug_id not integer in " . __FILE__ . " " . __FUNCTION__); 
        }
        $sql = 'SELECT *
                FROM bugs
                WHERE bug_id = ' . $bug_id;
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

    public function insert () {
        global $dbh;
        $sql = 'INSERT INTO bugs
                (title, description, status_id)
                VALUES (:title, :description, :status_id)';
        $stmt = $dbh->prepare( $sql );
        if (! $stmt->execute(array('title' => $this->title,
                                    'description' => $this->description,
                                    'status_id' => $this->status_id) )) {
            
            throw new Exception("INTERNAL: error inserting , code " . $stmt->errorCode);
        }
    }

    public function update () {
        throw new Exception("Ho ho, update not implemented yet");
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
