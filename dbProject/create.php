<?php
include 'dynamic_crud.php';

$selectedTable = isset($_GET['table']) ? $_GET['table'] : '';

if (empty($selectedTable)) {
    die("Error: Missing table parameter");
}

$columns = getTableColumns($selectedTable);
$primaryKeys = getPrimaryKeys($selectedTable);
$foreignKeys = getForeignKeys($selectedTable);

$message = '';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
        $data = array();
        foreach ($columns as $column) {
            if (isset($_POST[$column]) && $_POST[$column] !== '') {
                $data[$column] = sanitizeInput($_POST[$column]);
            }
        }
        createRecord($selectedTable, $data);
        $message = "Record created successfully in table " . $selectedTable;
    }
} catch (Exception $e) {
    $message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Data - Bree Mobile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #2c3e50;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"], .btn {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        input[type="submit"]:hover, .btn:hover {
            background-color: #2980b9;
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
    </style>
</head>
<body>
    <h1>Add Data to <?php echo $selectedTable; ?></h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="create.php?table=<?php echo $selectedTable; ?>">
        <?php foreach ($columns as $column): ?>
            <?php 
            $isPrimaryKey = in_array($column, $primaryKeys);
            $isForeignKey = isset($foreignKeys[$column]);
            ?>
            <label for="<?php echo $column; ?>"><?php echo $column; ?>:</label>
            <?php if ($isForeignKey): ?>
                <select name="<?php echo $column; ?>" id="<?php echo $column; ?>" <?php echo $isPrimaryKey ? 'required' : ''; ?>>
                    <option value="">Select <?php echo $column; ?></option>
                    <?php
                    $fkTable = $foreignKeys[$column]['table'];
                    $fkColumn = $foreignKeys[$column]['column'];
                    $fkRecords = readRecords($fkTable);
                    foreach ($fkRecords as $fkRecord):
                    ?>
                        <option value="<?php echo $fkRecord[$fkColumn]; ?>"><?php echo $fkRecord[$fkColumn]; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="text" name="<?php echo $column; ?>" id="<?php echo $column; ?>" 
                       placeholder="Enter <?php echo $column; ?>" 
                       <?php echo $isPrimaryKey ? 'required' : ''; ?>
                       <?php echo ($isPrimaryKey && !$isForeignKey) ? '' : ''; ?>>
            <?php endif; ?>
        <?php endforeach; ?>
        <input type="submit" name="create" value="Create">
    </form>

    <a href="index.php?table=<?php echo $selectedTable; ?>" class="btn">Back to List</a>
</body>
</html>