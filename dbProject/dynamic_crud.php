<?php
include 'db_connection.php';
include 'table_info.php';

function createRecord($table, $data) {
    global $conn;
    $columns = array();
    $values = array();
    $tableColumns = getTableColumns($table);
    
    foreach ($tableColumns as $column) {
        if (isset($data[$column]) && $data[$column] !== '') {
            $columns[] = $column;
            $values[] = "'" . $conn->real_escape_string($data[$column]) . "'";
        } else {
            // For empty fields, insert NULL
            $columns[] = $column;
            $values[] = "NULL";
        }
    }
    
    $columnsString = implode(", ", $columns);
    $valuesString = implode(", ", $values);
    $sql = "INSERT INTO $table ($columnsString) VALUES ($valuesString)";
    $sqlOrderDetailTotal="UPDATE ORDERDETAILS od
        JOIN PRODUCTS p ON od.productId = p.productId
        SET od.totalPrice = od.quantity * p.price";
    $sqlOrder = "UPDATE ORDERS o JOIN (
        SELECT orderId, SUM(totalPrice) AS totalSum
        FROM ORDERDETAILS
        GROUP BY orderId
        ) od ON o.orderId = od.orderId
        SET o.totalAmount = od.totalSum";

    error_log("Create SQL: " . $sql);  // Log the SQL query
    error_log("Update Order: ".$sqlOrder);
    error_log("Update Total: ".$sqlOrderDetailTotal);
    echo "table: {$table}";

    $result = $conn->query($sql);
    if($table=="orderdetails"){
        $conn->query($sqlOrderDetailTotal);
        $conn->query($sqlOrder);
    }
    if (!$result) {
        error_log("Create Error: " . $conn->error);  // Log any SQL errors
        throw new Exception("Error creating record: " . $conn->error);
    }
    return $result;
}

function readRecords($table, $limit = null, $offset = 0, $where = '') {
    global $conn;
    $sql = "SELECT * FROM $table";
    if ($where) {
        $sql .= " WHERE $where";
    }
    if ($limit !== null) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $result = $conn->query($sql);
    if (!$result) {
        error_log("Read Error: " . $conn->error);
        throw new Exception("Error reading records: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateRecord($table, $data) {
    global $conn;
    $setParts = array();
    $primaryKeys = getPrimaryKeys($table);
    
    foreach ($data as $key => $value) {
        if (!in_array($key, $primaryKeys)) {
            $setParts[] = "$key = '" . $conn->real_escape_string($value) . "'";
        }
    }
    
    if (empty($setParts)) {
        error_log("Update Error: No fields to update");
        throw new Exception("No fields to update");
    }
    
    $setClause = implode(", ", $setParts);
    $whereClause = getPrimaryKeyWhereClause($table, $data);
    
    if (empty($whereClause)) {
        error_log("Update Error: Missing primary key values");
        throw new Exception("Missing primary key values for update");
    }
    
    $sql = "UPDATE $table SET $setClause WHERE $whereClause";
    $sqlOrderDetailTotal="UPDATE ORDERDETAILS od
        JOIN PRODUCTS p ON od.productId = p.productId
        SET od.totalPrice = od.quantity * p.price";
    $sqlOrder = "UPDATE ORDERS o JOIN (
        SELECT orderId, SUM(totalPrice) AS totalSum
        FROM ORDERDETAILS
        GROUP BY orderId
        ) od ON o.orderId = od.orderId
        SET o.totalAmount = od.totalSum";

    error_log("Update SQL: " . $sql);
    error_log("Update Order: ".$sqlOrder);
    error_log("Update Total: ".$sqlOrderDetailTotal);
    
    $result = $conn->query($sql);
    if($table=="orderdetails"){
        $conn->query($sqlOrderDetailTotal);
        $conn->query($sqlOrder);
    }
    if (!$result) {
        error_log("Update Error: " . $conn->error);  // Log any SQL errors
        throw new Exception("Error updating record: " . $conn->error);
    }
    return $result;
}

function deleteRecord($table, $primaryKeyValues) {
    global $conn;
    $whereConditions = array();
    
    foreach ($primaryKeyValues as $key => $value) {
        $whereConditions[] = "$key = '" . $conn->real_escape_string($value) . "'";
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    $sql = "DELETE FROM $table WHERE $whereClause";
    $sqlOrder = "UPDATE ORDERS o 
            LEFT JOIN (
                SELECT orderId, SUM(totalPrice) AS totalSum
                FROM ORDERDETAILS
                GROUP BY orderId
            ) od ON o.orderId = od.orderId
            SET o.totalAmount = COALESCE(od.totalSum, 0)";

    
    error_log("Delete SQL: " . $sql);  // Log the SQL query
    error_log("Update Order: ".$sqlOrder);

    $result = $conn->query($sql);
    if($table=="orderdetails"){
        $conn->query($sqlOrder);
    }
    if (!$result) {
        error_log("Delete Error: " . $conn->error);  // Log any SQL errors
        throw new Exception("Error deleting record: " . $conn->error);
    }
    return $result;
}

function getRecordCount($table) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    if (!$result) {
        error_log("Count Error: " . $conn->error);
        throw new Exception("Error counting records: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row['count'];
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function getForeignKeys($table) {
    global $conn;
    $foreignKeys = array();
    $sql = "SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '$table'
            AND REFERENCED_TABLE_NAME IS NOT NULL";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $foreignKeys[$row['COLUMN_NAME']] = array(
            'table' => $row['REFERENCED_TABLE_NAME'],
            'column' => $row['REFERENCED_COLUMN_NAME']
        );
    }
    return $foreignKeys;
}