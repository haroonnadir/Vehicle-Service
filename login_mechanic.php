<?php
require_once './config.php';

class MechanicLogin extends DBConnection
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

    public function handleMechanicLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle mechanic login form submission
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $this->conn->prepare("SELECT id, password FROM mechanics WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($mechanicId, $hashedPassword);
                $stmt->fetch();

                if (password_verify($password, $hashedPassword)) {
                    // Login successful, store mechanic ID in session
                    session_start();
                    $_SESSION['mechanic_id'] = $mechanicId;

                    // Redirect to customer list
                    header('Location: customer_list.php');
                    exit;
                } else {
                    echo "Invalid password. Please try again.";
                }
            } else {
                echo "Mechanic not found. Please check your username.";
            }

            $stmt->close();
        }
    }
}

$mechanicLogin = new MechanicLogin();
$mechanicLogin->handleMechanicLogin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Login</title>
</head>

<body>
    <div class="row mt-5 pt-5 pb-5 mb-5" style="display:flex; justify-content:center;">
        <div class="col-lg-3">
            <form method="POST" action="login_mechanic.php">
                <h2 class="text-center mb-4">Mechanic Login</h2>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
        </div>
    </div>
</body>

</html>
