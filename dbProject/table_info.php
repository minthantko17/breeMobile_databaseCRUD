<?php
include 'db_connection.php';

function getTableNames() {
    global $conn;
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    return $tables;
}

function getTableColumns($table) {
    global $conn;
    $columns = array();
    $result = $conn->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

function getPrimaryKeys($table) {
    global $conn;
    $primaryKeys = array();
    $result = $conn->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
    while ($row = $result->fetch_assoc()) {
        $primaryKeys[] = $row['Column_name'];
    }
    return $primaryKeys;
}

function getPrimaryKeyWhereClause($table, $data) {
    global $conn;
    $primaryKeys = getPrimaryKeys($table);
    $whereClause = array();
    foreach ($primaryKeys as $key) {
        if (isset($data[$key])) {
            $whereClause[] = "$key = '" . $conn->real_escape_string($data[$key]) . "'";
        }
    }
    return implode(" AND ", $whereClause);
}
?>