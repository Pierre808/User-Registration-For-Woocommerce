<?php
class UserRegistrationForWoocommerceDatabaseHelper {
    private $wpdb;
    private $prefix = "user_registration_for_woocommerce_";

    /**
     * constructor for DatabaseHelper class
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Creates database and all the necessary tables for the plugin to work
     */
    public function init() {
        $this->createUserTable();
        $this->createVerificationCodeTable();
    }


    /**
     * drops a table inside the database
     * 
     * @param   $name the name of the table that should be dropped
     */
    private function dropTable($name) {
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "DROP TABLE IF EXISTS {$name}";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $result = $this->wpdb->query(
            $sql
        );
    }

    /**
     * Creates a table if it does not exist already
     * 
     * @param   $name   the name of the table that should be  created
     * @param   $params the rows that should be created in the table as SQL-Statement
     */
    private function createTable($name, $params) {
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $name ( {$params} ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        if ($result === false) {
            // Handle the error, perhaps throw an exception or return a message
        }
    }

    /**
     * Execute custom sql
     * 
     * @param   $sql    the sql that should be executed
     * @param   $values the values that should be placed inside the sql placeholders
     * 
     * @return  int|bool Boolean true for CREATE, ALTER, TRUNCATE and DROP queries. 
     *                   Number of rows affected/selected for all other queries. 
     *                   Boolean false on error.
     */
    public function executeSql($sql, ...$values) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $result = $this->wpdb->query(
            $this->wpdb->prepare($sql, $values)
        );
        
        if ($result === false) {
            // Handle the error, perhaps throw an exception or return a message
        }
        return $result;
    }
    

    /**
     * creates the table for users
     */
    private function createUserTable() {
        $params = "
            ID BIGINT(20),
            verification_status INT(1),
            PRIMARY KEY (ID)
        ";
        $this->createTable($this->prefix.'user', $params);
    }

    /**
     * Creates the table for verification codes
     */
    private function createVerificationCodeTable() {
        $params = "
            code VARCHAR(80) PRIMARY KEY,
            user_id BIGINT(20),
            created DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires DATETIME,
            FOREIGN KEY (user_id) REFERENCES {$this->prefix}user(ID)
        ";
        $this->createTable($this->prefix.'verification_code', $params);
    }


    /**
     * Adds an user to the database
     * 
     * @param   $user_id            ID of the user that should be added
     * @param   $status             Verification status
     * 
     * @return  string|int|false    string for error message
     *                              false on error
     *                              int as ID of the last inserted element
     */
    public function addUser($user_id, $status) {
        //checks wether a user with the same ID already exists in the database
        $user = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT ID FROM {$this->prefix}user WHERE ID = %d",
                $user_id
            )
        );
        // return if user is already in db
        if($user) {
            return 'User is already stored in the database!';
        }

        $this->wpdb->insert(
            $this->prefix . 'user',
            array(
                'ID' => $user_id,
                'verification_status' => $status
            )
        );

        $insertId = $this->wpdb->insert_id;
        
        return $insertId;
    }

    /**
     * 
     */
    public function getUser($user_id) {
        $user = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT ID, verification_status FROM {$this->prefix}user WHERE ID = %d",
                $user_id 
            ),
            OBJECT
        );

        return $user;
    }

    /**
     * Gets the verification_status from the database for a specific user
     * 
     * @param   $user_id            ID of the user
     * 
     * @return  object|null   Database query results.
     */
    public function getUserStatus($user_id) {
        $user = $this->wpdb->get_results(
            "SELECT verification_status FROM {$this->prefix}user WHERE ID = {$user_id}", 
            OBJECT
        );
        return $user;
    }

    /**
     * Sets a users verification status
     * 
     * @return  int|false   The number of rows updated, or false on error.
     */
    public function setUserStatus($user_id, $verification_status) {
        //checks wether user with the ID is in the database
        $user = $this->wpdb->get_row(
            "SELECT ID FROM {$this->prefix}user WHERE ID = " . $user_id
        );
        
        if($user) {
            $r = $this->wpdb->update(
                $this->prefix.'user',
                array(
                    'verification_status' => $verification_status
                ),
                array(
                    'ID' => $user_id
                )
            );
        }
        else {
            $r = $this->wpdb->insert(
                $this->prefix.'user',
                array(
                    'ID' => $user_id,
                    'verification_status' => $verification_status
                )
            );
        }

        return $r;
    }


    /**
     * Adds a verification code to the database
     * 
     * @param   $verification_code  code
     * @param   $user_id            ID of the user that the code belongs to
     * @param   $created            creation date (optional)
     * @param   $expires            datetime for the code to expire
     * 
     * @return  string|int|false    string for error message
     *                              false on error
     *                              int as ID of the last inserted element
     */
    public function addVerificationCode($verification_code, $user_id, $expires, $created = '') {
        //checks wether an identical code already exists in the database
        $code = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT code FROM {$this->prefix}verification_code WHERE code = %s",
                $verification_code
            )
        );
        
        if($code) {
            return 'Code is already stored in the database!';
        }


        $insertArr = array(
            'code' => $verification_code,
            'user_id' => $user_id,
            'expires' => $expires
        );

        if($created != '') {
            $insertArr['created'] = $created;
        }

        $this->wpdb->insert(
            $this->prefix . 'verification_code',
            $insertArr
        );

        $insertId = $this->wpdb->insert_id;
        
        return $insertId;
    }

    /**
     * Gets the entire verification_code entity from the database where code matches
     * 
     * @param   $verification_code  Code to search for in the table
     * 
     * @return  object|null         Database query results.
     */
    public function getVerificationCode($verification_code) {
        $verification_code = sanitize_text_field($verification_code);

        $code = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT code, user_id, created, expires FROM {$this->prefix}verification_code WHERE code = %s",
                $verification_code 
            ),
            OBJECT
        );

        return $code;
    }
}