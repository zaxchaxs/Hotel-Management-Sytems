<?php


require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

function executeQuery($query) {
    global $conn;
    return $conn->query($query);
}

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

function getRow($query) {
    $result = executeQuery($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

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

 //Update data in a table
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


function getLastError() {
    global $conn;
    return $conn->error;
}

function beginTransaction() {
    global $conn;
    return $conn->begin_transaction();
}

function commitTransaction() {
    global $conn;
    return $conn->commit();
}

function rollbackTransaction() {
    global $conn;
    return $conn->rollback();
}

function closeConnection() {
    global $conn;
    $conn->close();
}

register_shutdown_function('closeConnection');
?>