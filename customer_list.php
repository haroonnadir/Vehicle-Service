<?php
require_once './config.php';

class CustomerList extends DBConnection
{
    private $settings;

    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
        ini_set('display_error', 1);
    }

    public function getLoggedInMechanicId()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['mechanic_id']) ? $_SESSION['mechanic_id'] : null;
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function getAllCustomers()
    {
        $stmt = $this->conn->prepare("SELECT * FROM customers");
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all customers
        $customers = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $customers;
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }

    public function getLocationFromDatabase($table, $id)
{
    $stmt = $this->conn->prepare("SELECT location FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $location = json_decode($result->fetch_assoc()['location'], true);
        $stmt->close();
        return $location;
    } else {
        $stmt->close();
        return null;
    }
}

}

$customerList = new CustomerList();
$customers = $customerList->getAllCustomers();

$mechanicId = $customerList->getLoggedInMechanicId();
if ($mechanicId === null) {
    header('Location: mechanic_login.php');
    exit;
}

$mechanicLocation = $customerList->getLocationFromDatabase("mechanics", $mechanicId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Customers List</title>
</head>

<body>
    <header>
        <h1>Vehicle Service Management System [Customers]</h1>
    </header>

    <div class="container">
        <h2>All Customers List</h2>

        <?php if (count($customers) > 0) : ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Firstname</th>
                    <th>Lastname</th>
                    <th>Location</th>
                    <th>Phone No</th>
                    <th>Distance (km)</th>
                    <th>Est. Time to Arrival</th>
                </tr>
                <?php foreach ($customers as $customer) : ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo $customer['firstname']; ?></td>
                        <td><?php echo $customer['lastname']; ?></td>
                        <td><?php echo $customer['location']; ?></td>
                        <td><?php echo $customer['contact']; ?></td>
                        <?php
                        $customerLocation = json_decode($customer['location'], true);

                        if ($mechanicLocation !== null && $customerLocation !== null) {
                            $distance = $customerList->calculateDistance(
                                $mechanicLocation['lat'],
                                $mechanicLocation['lon'],
                                $customerLocation['lat'],
                                $customerLocation['lon']
                            );
                            $estimatedTime = $distance * 60 / 50; // Assuming an average speed of 50 km/h
                        } else {
                            $distance = $estimatedTime = 0;
                        }
                        ?>
                        <td><?php echo number_format($distance, 2); ?></td>
                        <td><?php echo number_format($estimatedTime, 2); ?> hours</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else : ?>
            <p>No customers found.</p>
        <?php endif; ?>
    </div>
</body>

<style>
    body {
        font-family: Arial, sans-serif;
    }

    header {
        background-color: #333;
        color: white;
        padding: 10px;
        text-align: center;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #333;
        color: white;
    }
</style>
</html>
