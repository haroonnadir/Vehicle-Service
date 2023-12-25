<?php
require_once './config.php';

class Register extends DBConnection
{
    private $settings;

    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
        ini_set('display_error', 1);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function handleRegistration()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle user registration form submission
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $contact = $_POST['contact'];
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $location = $_POST['location'];

        $stmt = $this->conn->prepare("INSERT INTO `customers` (`firstname`, `lastname`, `username`, `password`, `contact`, `email`, `location`) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssssss", $firstname, $lastname, $username, $password, $contact, $email, $location);

        if ($stmt->execute()) {
            // Registration successful, redirect to home page
            header('Location: index.php');
            exit; // Make sure to exit after redirection
        } else {
            echo "Registration failed. Please try again later.";
            // Log the detailed error for debugging purposes
            error_log("Error: " . $stmt->error);
        }

        $stmt->close();
    }
}
}

$auth = new Register();
$auth->handleRegistration();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<div class="row mt-5 pt-5 pb-5 mb-5" style="display:flex; justify-content:center;">
    <div class="col-lg-3">
        <div id="map" style="height: 300px;"></div>
        <form method="POST" action="register_customer.php">
            <h2 class="text-center mb-4">Customer Registration</h2>

            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" class="form-control" name="firstname" required>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" class="form-control" name="lastname" required>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <div class="form-group">
                <label for="contact">Contact:</label>
                <input type="text" class="form-control" name="contact" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" class="form-control" name="location" id="location" readonly required>
                <button type="button" onclick="pickLocation()">Pick Location</button>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
    </div>
</div>

<script>
    function pickLocation() {
        var locationInput = document.getElementById('location');
        var map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([0, 0], { draggable: true }).addTo(map);

        marker.on('dragend', function (event) {
            var position = marker.getLatLng();
            locationInput.value = position.lat + ', ' + position.lng;
        });
    }
</script>
</html>
