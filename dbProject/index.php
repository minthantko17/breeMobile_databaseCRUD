<?php
include 'dynamic_crud.php';

$tables = getTableNames();
$selectedTable = isset($_POST['table']) ? $_POST['table'] : (isset($_GET['table']) ? $_GET['table'] : $tables[0]);
$columns = getTableColumns($selectedTable);
$primaryKeys = getPrimaryKeys($selectedTable);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

$message = '';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
        $primaryKeyValues = array();
        $allKeysPresent = true;
        foreach ($primaryKeys as $key) {
            if (isset($_POST[$key])) {
                $primaryKeyValues[$key] = sanitizeInput($_POST[$key]);
            } else {
                $allKeysPresent = false;
                break;
            }
        }
        
        if ($allKeysPresent) {
            deleteRecord($selectedTable, $primaryKeyValues);
            $message = "Record deleted successfully from table " . $selectedTable;
        } else {
            throw new Exception("Error: Not all primary key values provided for deletion");
        }
    }

    $totalRecords = getRecordCount($selectedTable);
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $records = readRecords($selectedTable, $recordsPerPage, $offset);
} catch (Exception $e) {
    $message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bree Mobile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            margin-bottom: 20px;
        }
        select {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-delete {
            background-color: #e74c3c;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            color: #3498db;
            padding: 6px 12px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
            margin: 0 2px;
            display: inline-block;
            font-size: 14px;
            line-height: 1;
        }
        .pagination a.active {
            background-color: #3498db;
            color: white;
            border: 1px solid #3498db;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .btn-group {
            margin-bottom: 20px;
        }
        .btn-group .btn {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Bree Mobile</h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php">
        <label for="table">Select Table:</label>
        <select name="table" id="table" onchange="this.form.submit()">
            <?php foreach ($tables as $table): ?>
                <option value="<?php echo $table; ?>" <?php echo $table == $selectedTable ? 'selected' : ''; ?>>
                    <?php echo $table; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="btn-group">
        <a href="create.php?table=<?php echo $selectedTable; ?>" class="btn">Add Data</a>
        <a href="check_order.php" class="btn">Check Order</a>
        <a href="track_shipment.php" class="btn">Track Shipment</a>
    </div>

    <h2>Records in <?php echo $selectedTable; ?></h2>
    <div style="overflow-x: auto;">
        <table>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th><?php echo $column; ?></th>
                <?php endforeach; ?>
                <th>Action</th>
            </tr>
            <?php foreach ($records as $record): ?>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <td><?php echo $record[$column]; ?></td>
                    <?php endforeach; ?>
                    <td class="action-buttons">
                        <a href="update.php?table=<?php echo $selectedTable; ?><?php 
                            foreach ($primaryKeys as $key) {
                                echo '&' . urlencode($key) . '=' . urlencode($record[$key]);
                            }
                        ?>" class="btn">Edit</a>
                        <form method="post" action="index.php" style="display:inline;">
                            <input type="hidden" name="table" value="<?php echo $selectedTable; ?>">
                            <?php foreach ($primaryKeys as $key): ?>
                                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $record[$key]; ?>">
                            <?php endforeach; ?>
                            <button type="submit" name="delete" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this record?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?table=<?php echo $selectedTable; ?>&page=<?php echo $i; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>