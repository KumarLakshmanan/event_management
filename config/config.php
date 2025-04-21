<?php
// Application configuration
define('APP_NAME', 'Event Management System');

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Include database connection class
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email.php';

// Function to read data from database or mock files
function getData($table, $conditions = null, $orderBy = null)
{
    $db = Database::getInstance();

    $sql = "SELECT * FROM $table";
    $params = [];

    if ($conditions) {
        $whereClauses = [];
        foreach ($conditions as $key => $value) {
            $whereClauses[] = "$key = ?";
            $params[] = $value;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
    }

    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }

    return $db->query($sql, $params);
}

// Function to get a single record by ID
function getRecordById($table, $id)
{
    $db = Database::getInstance();
    return $db->queryOne("SELECT * FROM $table WHERE id = ?", [$id]);
}
