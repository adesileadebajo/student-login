<?php
/* ============================================================
   SWD 414 – Backend Development
   File   : register.php
   Purpose: Connect to MySQL database and register a new student
   ============================================================ */

// ── 1. DATABASE CONFIGURATION ────────────────────────────────
define('DB_HOST',   'localhost');   // Database host
define('DB_USER',   'root');        // Database username
define('DB_PASS',   '');            // Database password
define('DB_NAME',   'swd414_db');   // Database name
define('DB_PORT',    3306);         // MySQL default port

// ── 2. ESTABLISH CONNECTION ───────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");


// ── 3. HANDLE FORM SUBMISSION ─────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- Collect & sanitise inputs ---
    $first_name  = trim($conn->real_escape_string($_POST['first_name']));
    $last_name   = trim($conn->real_escape_string($_POST['last_name']));
    $matric_num  = trim($conn->real_escape_string($_POST['matric_num']));
    $password    = $_POST['password'];
    $confirm_pwd = $_POST['confirm_password'];

    // --- Basic validation ---
    $errors = [];

    if (empty($first_name))  $errors[] = "First name is required.";
    if (empty($last_name))   $errors[] = "Last name is required.";
    if (empty($matric_num))  $errors[] = "Matric number is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_pwd) $errors[] = "Passwords do not match.";

    // --- Check if matric number already exists ---
    $check = $conn->prepare("SELECT id FROM students WHERE matric_num = ?");
    $check->bind_param("s", $matric_num);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $errors[] = "Matric number already registered.";
    }
    $check->close();

    // --- If no errors, insert into database ---
    if (empty($errors)) {

        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare(
            "INSERT INTO students (first_name, last_name, matric_num, password, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("ssss", $first_name, $last_name, $matric_num, $hashed_password);

        if ($stmt->execute()) {
            $success = "Registration successful! Welcome, $first_name.";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// ── 4. CLOSE CONNECTION ───────────────────────────────────────
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration – SWD 414</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Extra styles for feedback messages */
        .message-box {
            max-width: 480px;
            margin: 60px auto;
            padding: 30px 36px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(12px);
        }
        .success-box {
            background: rgba(0, 212, 120, 0.12);
            border: 1px solid rgba(0, 212, 120, 0.35);
            color: #00d478;
        }
        .error-box {
            background: rgba(255, 80, 80, 0.1);
            border: 1px solid rgba(255, 80, 80, 0.3);
            color: #ff6b6b;
        }
        .message-box h2  { font-size: 1.4rem; margin-bottom: 10px; }
        .message-box ul  { list-style: none; padding: 0; }
        .message-box li  { margin: 6px 0; font-size: 0.92rem; }
        .message-box a   { color: #00d4ff; text-decoration: none; margin-top: 16px; display: inline-block; }
    </style>
</head>
<body>

<?php if (!empty($errors)): ?>
    <div class="message-box error-box">
        <h2>⚠ Registration Failed</h2>
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
        </ul>
        <a href="register.html">← Go Back</a>
    </div>

<?php elseif (isset($success)): ?>
    <div class="message-box success-box">
        <h2>✓ <?php echo htmlspecialchars($success); ?></h2>
        <p style="color:rgba(255,255,255,0.5); margin-top:8px; font-size:0.88rem;">
            You can now log in with your matric number.
        </p>
        <a href="login.php">Proceed to Login →</a>
    </div>

<?php else: ?>
    <!-- No POST yet – redirect back to the form -->
    <script>window.location.href = "register.html";</script>
<?php endif; ?>

</body>
</html>
