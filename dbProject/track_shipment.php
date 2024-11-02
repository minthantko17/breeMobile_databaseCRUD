<?php
include 'dynamic_crud.php';

$message = '';
$shipmentDetails = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['track'])) {
    $trackingNumber = sanitizeInput($_POST['trackingNumber']);
    
    try {
        global $conn;
        $sql = "SELECT s.trackingNumber, c.customerId, CONCAT(c.firstName, ' ', c.lastName) AS customerName,
                s.shipmentId, s.shipmentStatus, s.shipmentDate, s.shipmentMethod, s.shipmentCost      
                FROM shipments s 
                JOIN customers c ON s.customerId = c.customerId 
                WHERE s.trackingNumber = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $trackingNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $shipmentDetails = $result->fetch_assoc();
        } else {
            $message = "No shipment found for the given tracking number.";
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
    <title>Track Shipment - Bree Mobile</title>
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
        input[type="text"] {
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
        .shipment-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            margin-bottom: 20px;
        }
        .shipment-details h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .shipment-details p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Track Shipment</h1>
    
    <?php if ($message): ?>
        <div class="message error">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="track_shipment.php">
        <label for="trackingNumber">Tracking Number:</label>
        <input type="text" name="trackingNumber" id="trackingNumber" required>
        <input type="submit" name="track" value="Track Shipment">
    </form>

    <?php if ($shipmentDetails): ?>
        <div class="shipment-details">
            <h2>Shipment Details</h2>
            <p><strong>Tracking Number:</strong> <?php echo $shipmentDetails['trackingNumber']; ?></p>
            <p><strong>Customer ID:</strong> <?php echo $shipmentDetails['customerId']; ?></p>
            <p><strong>Customer Name:</strong> <?php echo $shipmentDetails['customerName']; ?></p>
            <p><strong>Shipment ID:</strong> <?php echo $shipmentDetails['shipmentId']; ?></p>
            <p><strong>Shipment Status:</strong> <?php echo $shipmentDetails['shipmentStatus']; ?></p>
            <p><strong>Shipment Date:</strong> <?php echo $shipmentDetails['shipmentDate']; ?></p>
            <p><strong>Shipment Method:</strong> <?php echo $shipmentDetails['shipmentMethod']; ?></p>
            <p><strong>Shipment Cost:</strong> <?php echo $shipmentDetails['shipmentCost']; ?> THB</p>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn">Back to Home</a>
</body>
</html>