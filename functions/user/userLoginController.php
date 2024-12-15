<?php

// Include necessary files
include_once 'conn.php';
$response = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response['request']=true;
    // Check if user is already authenticated
    if (isset($_COOKIE['authenticated']) && $_COOKIE['authenticated'] == true) {
        // User is already logged in
        $response['status'] = false;
        $response['message'] = "You are already logged in. Please log out first.";
    } else {
        // Check if email and password are provided and not empty
        if (isset($_POST['st_email']) && isset($_POST['st_password']) && !empty($_POST['st_email']) && !empty($_POST['st_password'])) {
            
            // Sanitize inputs to prevent SQL injection
            $email = mysqli_real_escape_string($conn, $_POST['st_email']);
            $pass = $_POST['st_password'];

            // Query the database for the user with the provided email
            $query = "SELECT * FROM student WHERE st_email = '$email'";
            $result = mysqli_query($conn, $query);

            if ($result) {
                // Check if a user with that email exists
                if (mysqli_num_rows($result) == 1) {
                    $data = mysqli_fetch_assoc($result);

                    // Verify the provided password against the stored hash
                    if (password_verify($pass, $data['st_password'])) {
                        // Set cookies securely
                        $cookie_options = [
                            'expires' => time() + (86400 * 365), // 365 days expiration
                            'path' => '/',
                            'domain' => '', // Specify your domain
                            'secure' => isset($_SERVER['HTTPS']), // Set true if HTTPS is being used
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ];

                        // Set the cookies
                        setcookie('authenticated', true, $cookie_options);
                        setcookie('email', $data['st_email'], $cookie_options);
                        setcookie('role', $data['role'], $cookie_options);
                        setcookie('id', $data['st_id'], $cookie_options);

                        $response['status'] = true;
                        $response['message'] = "Login successful.";
                    } else {
                        $response['status'] = false;
                        $response['message'] = "Invalid email or password.";
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = "User not registered.";
                }
            } else {
                // In case of database error
                $response['status'] = false;
                $response['message'] = "Database query error. Please try again later.";
            }
        } else {
            $response['status'] = false;
            $response['message'] = "Invalid parameters. Both email and password are required.";
        }
    }
} else {
    $response['request'] = false;
    $response['message'] = "Invalid request method.";
}

// Output response in JSON format
echo json_encode($response);

?>
