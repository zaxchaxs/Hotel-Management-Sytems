<?php
/**
 * Database connection and helper functions for Hotel Management System
 */

// Include configuration file if not already included
require_once 'config.php';

/**
 * Create a new database connection
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

/**
 * Sanitize input data to prevent SQL injection
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Execute a query and return the result
 * 
 * @param string $query SQL query
 * @return mysqli_result|bool Query result or false on failure
 */
function executeQuery($query) {
    global $conn;
    return $conn->query($query);
}

/**
 * Execute a prepared statement
 * 
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types (i: integer, d: double, s: string, b: blob)
 * @param array $params Parameters to bind
 * @return mysqli_stmt|bool Statement object or false on failure
 */
function executePreparedStatement($query, $types, $params) {
    global $conn;
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt;
    }
    
    return false;
}

/**
 * Get a single row from the database
 * 
 * @param string $query SQL query
 * @return array|null Row data or null if not found
 */
function getRow($query) {
    $result = executeQuery($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get multiple rows from the database
 * 
 * @param string $query SQL query
 * @return array Array of rows
 */
function getRows($query) {
    $result = executeQuery($query);
    $rows = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    
    return $rows;
}

/**
 * Insert data into a table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|bool Last insert ID or false on failure
 */
function insertData($table, $data) {
    global $conn;
    
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $types = '';
    $values = [];
    
    foreach ($data as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
        $values[] = $value;
    }
    
    $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = executePreparedStatement($query, $types, $values);
    
    if ($stmt) {
        $insert_id = $conn->insert_id;
        $stmt->close();
        return $insert_id;
    }
    
    return false;
}

/**
 * Update data in a table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $where WHERE clause
 * @param string $types Parameter types for where clause
 * @param array $where_params Parameters for where clause
 * @return bool True on success, false on failure
 */
function updateData($table, $data, $where, $types_where = '', $where_params = []) {
    global $conn;
    
    $set_parts = [];
    $types = '';
    $values = [];
    
    foreach ($data as $column => $value) {
        $set_parts[] = "$column = ?";
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
        $values[] = $value;
    }
    
    $set_clause = implode(', ', $set_parts);
    $query = "UPDATE $table SET $set_clause WHERE $where";
    
    // Combine types and parameters
    $types .= $types_where;
    $params = array_merge($values, $where_params);
    
    $stmt = executePreparedStatement($query, $types, $params);
    
    if ($stmt) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows > 0;
    }
    
    return false;
}

/**
 * Delete data from a table
 * 
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param string $types Parameter types
 * @param array $params Parameters for where clause
 * @return bool True on success, false on failure
 */
function deleteData($table, $where, $types, $params) {
    global $conn;
    
    $query = "DELETE FROM $table WHERE $where";
    $stmt = executePreparedStatement($query, $types, $params);
    
    if ($stmt) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows > 0;
    }
    
    return false;
}

/**
 * Get the last error message
 * 
 * @return string Error message
 */
function getLastError() {
    global $conn;
    return $conn->error;
}

/**
 * Begin a transaction
 * 
 * @return bool True on success, false on failure
 */
function beginTransaction() {
    global $conn;
    return $conn->begin_transaction();
}

/**
 * Commit a transaction
 * 
 * @return bool True on success, false on failure
 */
function commitTransaction() {
    global $conn;
    return $conn->commit();
}

/**
 * Rollback a transaction
 * 
 * @return bool True on success, false on failure
 */
function rollbackTransaction() {
    global $conn;
    return $conn->rollback();
}

/**
 * Close the database connection
 * 
 * @return void
 */
function closeConnection() {
    global $conn;
    $conn->close();
}

// Register a shutdown function to close the connection
register_shutdown_function('closeConnection');
?>