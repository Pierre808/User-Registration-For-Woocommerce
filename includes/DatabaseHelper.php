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
        $params = "ID BIGINT(20),
            verification_status INT(1),
            PRIMARY KEY (ID)";
        $this->createTable($this->prefix.'user', $params);
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
}