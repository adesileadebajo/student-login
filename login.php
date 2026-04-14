<?php
/* ============================================================
   SWD 414 – Backend Development
   File   : login.php
   Purpose: Authenticate a student using matric number & password
   ============================================================ */

// ── 1. DATABASE CONFIGURATION ────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'swd414_db');
define('DB_PORT',  3306);

// ── 2. ESTABLISH CONNECTION ───────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ── 3. HANDLE FORM SUBMISSION ─────────────────────────────────
$errors  = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $matric_num = trim($conn->real_escape_string($_POST['matric_num']));
    $password   = $_POST['password'];

    // Basic validation
    if (empty($matric_num)) $errors[] = "Matric number is required.";
    if (empty($password))   $errors[] = "Password is required.";

    if (empty($errors)) {
        // Look up student by matric number
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password FROM students WHERE matric_num = ?");
        $stmt->bind_param("s", $matric_num);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();

            // Verify password against the stored hash
            if (password_verify($password, $student['password'])) {
                $success = "Welcome back, " . htmlspecialchars($student['first_name']) . " " . htmlspecialchars($student['last_name']) . "!";
            } else {
                $errors[] = "Incorrect password. Please try again.";
            }
        } else {
            $errors[] = "No account found with that matric number.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWD 414 – Student Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="page-wrapper">

        <!-- Header -->
        <div class="page-header">
            <div class="badge">SWD 414 &mdash; Backend Development</div>
            <h1>Student <span>Login</span></h1>
            <p>Sign in with your matric number and password</p>
        </div>

        <?php if (!empty($errors)): ?>
            <!-- Error messages -->
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p>⚠ <?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <!-- Success message -->
            <div class="alert alert-success">
                <p>✓ <?php echo $success; ?></p>
                <p class="alert-sub">You are now logged in.</p>
            </div>
        <?php else: ?>
            <!-- Login Card -->
            <div class="card">
                <form action="login.php" method="POST">

                    <div class="form-group">
                        <label for="matric_num">Matric Number</label>
                        <input
                            type="text"
                            id="matric_num"
                            name="matric_num"
                            placeholder="e.g. CSC/2021/001"
                            value="<?php echo isset($_POST['matric_num']) ? htmlspecialchars($_POST['matric_num']) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <hr class="divider">

                    <button type="submit" class="btn-submit">Sign In</button>

                </form>
            </div>
        <?php endif; ?>

        <p class="footer-note">
            Don't have an account? <a href="register.html">Register here</a>
        </p>

    </div>

</body>
</html>
