<?php

namespace WyllyMk\KommoCRM;

class Logger {
    private static $instance = null;
    private $table_name;

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'kommo_logs';
    }

    /**
     * Add a log entry
     *
     * @param string $event_type The type of event
     * @param string $message The log message
     * @return bool|int False on failure, number of rows affected on success
     */
    public function log($event_type, $message) {
        global $wpdb;

        return $wpdb->insert(
            $this->table_name,
            array(
                'event_type' => $event_type,
                'message' => $message,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s')
        );
    }

    /**
     * Clear old logs
     *
     * @param int $days Number of days to keep logs for
     * @return bool|int False on failure, number of rows affected on success
     */
    public function clear_old_logs($days = 30) {
        global $wpdb;

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE created_at < %s",
                $date
            )
        );
    }
} 