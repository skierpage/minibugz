<?php
require_once('db_login.php');

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
        // if only bug_id supplied, then go look it up.
        // TODO: don't mix DB operation with bug creation ?!?
        if (count($newParams === 1) and isset($newParams['bug_id'])) {
            $this->bug_id = (int) $newParams['bug_id'];
            $this->retrieve();
            // TODO: what if retrieved bug is invalid?
        } elseif ($newParams) {
            // Pull known keys from newParams
            // XXX If I delete keys as I consume them,
            //     does the caller see the depleted newParams?
            $validationErrs = array();
            foreach ($newParams as $field => $value) {
                switch ($field) {
                    case 'bug_id':
                        //XXX Should
                        throw new Exception("INTERNAL: bug_id " . $value . "suppled but creating a bug!?"); 
                        break;
                    case 'title':
                        $this->title = $value;
                        break;
                    case 'description':
                        $this->description = $value;
                        break;
                    case 'status_id':
                        $this->status_id = $value;
                        if (! is_int($value)) {
                            // TODO make this a function or object?
                            $validationErrs[] = array(
                                'field' => $field,
                                'errStr' => 'must be an integer'
                            );
                        }
                        break;

                    default:
                        // ignore random stuff.
                }
            }
            if (count($validationErrs !== 0)) {
                throw new Exception("VALIDATION failed" . $validationErrs); 
            }
            // TODO check for required fields and lengths? or let DB throw
            // XXX How to throw/return detailed validation error?
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

    public function insert () {
        global $dbh;
        echo "in Bug, go insert Bug:"; var_dump ($this);
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
        echo "in Bug, go update Bug:"; var_dump ($this);
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
