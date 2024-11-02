<?php
include 'dynamic_crud.php';

$message = '';
$orderDetails = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check'])) {
    $searchType = sanitizeInput($_POST['searchType']);
    $searchValue = sanitizeInput($_POST['searchValue']);
    
    try {
        global $conn;
        $sql = "SELECT o.orderId, b.branchName, c.customerId, CONCAT(c.firstName, ' ', c.lastName) AS customerName, 
                c.mobileNumber, pr.productName, pr.price AS UnitPrice, od.quantity, od.totalPrice 
                FROM orders o 
                JOIN customers c USING (customerId) 
                JOIN (orderDetails od JOIN products pr USING (productId)) USING (orderId)
                JOIN (orderbranches ob JOIN branches b USING(branchId)) USING (orderId)
                WHERE ";

        switch ($searchType) {
            case 'orderId':
                $sql .= "o.orderId = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $searchValue);
                break;
            case 'customerId':
                $sql .= "c.customerId = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $searchValue);
                break;
            case 'customerName':
                $sql .= "CONCAT(c.firstName, ' ', c.lastName) LIKE ?";
                $searchValue = "%$searchValue%";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $searchValue);
                break;
            default:
                throw new Exception("Invalid search type");
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $orderDetails = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $message = "No order found for the given search criteria.";
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Order - Bree Mobile</title>
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
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .order-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            margin-bottom: 20px;
        }
        .order-details h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Check Order</h1>
    
    <?php if ($message): ?>
        <div class="message error">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="check_order.php">
        <label for="searchType">Search By:</label>
        <select name="searchType" id="searchType" required>
            <option value="orderId">Order ID</option>
            <option value="customerId">Customer ID</option>
            <option value="customerName">Customer Name</option>
        </select>
        <label for="searchValue">Search Value:</label>
        <input type="text" name="searchValue" id="searchValue" required>
        <input type="submit" name="check" value="Check Order">
    </form>

    <?php if ($orderDetails): ?>
        <div class="order-details">
            <h2>Order Details</h2>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Branch Name</th>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Mobile Number</th>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                </tr>
                <?php foreach ($orderDetails as $detail): ?>
                    <tr>
                        <td><?php echo $detail['orderId']; ?></td>
                        <td><?php echo $detail['branchName']; ?></td>
                        <td><?php echo $detail['customerId']; ?></td>
                        <td><?php echo $detail['customerName']; ?></td>
                        <td><?php echo $detail['mobileNumber']; ?></td>
                        <td><?php echo $detail['productName']; ?></td>
                        <td><?php echo $detail['UnitPrice']; ?></td>
                        <td><?php echo $detail['quantity']; ?></td>
                        <td><?php echo $detail['totalPrice']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn">Back to Home</a>
</body>
</html>