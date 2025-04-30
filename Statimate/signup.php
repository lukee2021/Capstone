<<<<<<< HEAD:Statimate/signup.php
<?php
session_start();
include("connection.php");
include("functions.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name        = trim($_POST['user_name']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email            = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check email uniqueness
        $stmt = $con->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already in use.";
        }
        $stmt->close();
    }

    if (!$error) {
        if (!empty($user_name) && !is_numeric($user_name)) {
            $user_id = random_num(10);
            $hashed_password = hashPassword($password);

            $stmt = $con->prepare("
              INSERT INTO users (user_id, user_name, password, email) 
              VALUES (?,?,?,?)
            ");
            $stmt->bind_param("isss", $user_id, $user_name, $hashed_password, $email);
            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Signup failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Username cannot be empty or numeric.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Sign Up | Statimate</title>
  <!-- Google Font -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" 
    rel="stylesheet"
  />
  <!-- Bootstrap CSS -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
  <!-- Font Awesome -->
  <link 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
    rel="stylesheet"
  />
  <!-- Custom CSS -->
  <link href="assets/css/custom.css" rel="stylesheet"/>
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card shadow-sm w-100" style="max-width: 400px;">
      <div class="card-header text-center bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-user-plus me-1"></i>Sign Up</h4>
      </div>
      <div class="card-body">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label for="user_name" class="form-label">Username</label>
            <input 
              type="text" 
              class="form-control" 
              id="user_name" 
              name="user_name" 
              value="<?= isset($user_name) ? htmlspecialchars($user_name) : '' ?>" 
              required
            >
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input 
              type="email" 
              class="form-control" 
              id="email" 
              name="email" 
              value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" 
              required
            >
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              name="password" 
              required
            >
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input 
              type="password" 
              class="form-control" 
              id="confirm_password" 
              name="confirm_password" 
              required
            >
          </div>
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-user-plus me-1"></i> Sign Up
            </button>
          </div>
          <p class="text-center mb-0">
            Already have an account? 
            <a href="login.php">Log in here</a>
          </p>
        </form>
      </div>
    </div>
  </div>

  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
=======
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
include("connection.php");
include("functions.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name        = trim($_POST['user_name']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email            = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check email uniqueness
        $stmt = $con->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already in use.";
        }
        $stmt->close();
    }

    if (!$error) {
        if (!empty($user_name) && !is_numeric($user_name)) {
            $user_id = random_num(10);
            $hashed_password = hashPassword($password);

            $stmt = $con->prepare("
              INSERT INTO users (user_id, user_name, password, email) 
              VALUES (?,?,?,?)
            ");
            $stmt->bind_param("isss", $user_id, $user_name, $hashed_password, $email);
            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Signup failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Username cannot be empty or numeric.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Sign Up | Statimate</title>
  <!-- Google Font -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" 
    rel="stylesheet"
  />
  <!-- Bootstrap CSS -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
  <!-- Font Awesome -->
  <link 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
    rel="stylesheet"
  />
  <!-- Custom CSS -->
  <link href="assets/css/custom.css" rel="stylesheet"/>
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card shadow-sm w-100" style="max-width: 400px;">
      <div class="card-header text-center bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-user-plus me-1"></i>Sign Up</h4>
      </div>
      <div class="card-body">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label for="user_name" class="form-label">Username</label>
            <input 
              type="text" 
              class="form-control" 
              id="user_name" 
              name="user_name" 
              value="<?= isset($user_name) ? htmlspecialchars($user_name) : '' ?>" 
              required
            >
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input 
              type="email" 
              class="form-control" 
              id="email" 
              name="email" 
              value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" 
              required
            >
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              name="password" 
              required
            >
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input 
              type="password" 
              class="form-control" 
              id="confirm_password" 
              name="confirm_password" 
              required
            >
          </div>
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-user-plus me-1"></i> Sign Up
            </button>
          </div>
          <p class="text-center mb-0">
            Already have an account? 
            <a href="login.php">Log in here</a>
          </p>
        </form>
      </div>
    </div>
  </div>

  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
>>>>>>> 4a81d8e (final):Capstone/signup.php
