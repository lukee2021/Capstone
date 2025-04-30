<?php
session_start();
include("connection.php");
include("functions.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_name = trim($_POST['user_name']);
    $password  = $_POST['password'];

    if (!empty($user_name) && !empty($password) && !is_numeric($user_name)) {
        $stmt = $con->prepare("SELECT user_id, password FROM users WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $stmt->bind_result($user_id, $hash);
        if ($stmt->fetch() && verifyPassword($password, $hash)) {
            $_SESSION['user_id'] = $user_id;
            header("Location: index.php");
            exit;
        }
        $stmt->close();
        $error = "Invalid credentials. Please check your username and password.";
    } else {
        $error = "Your user name cannot be a number, and fields cannot be empty.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Login | Statimate</title>
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
  <style>
    /* Override or supplement as needed */
    #statimate-logo {
      display: block;
      margin: 1rem auto;
      max-width: 150px;
      height: auto;
    }
  </style>
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card shadow-sm w-100" style="max-width: 400px;">
      <div class="card-body text-center">
        <!-- Logo -->
        <img src="statimate.webp" alt="Statimate Logo" id="statimate-logo">

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="p-3">
          <div class="mb-3 text-start">
            <label for="user_name" class="form-label">Username</label>
            <input 
              type="text" 
              class="form-control" 
              id="user_name" 
              name="user_name" 
              required
            >
          </div>
          <div class="mb-3 text-start">
            <label for="password" class="form-label">Password</label>
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              name="password" 
              required
            >
          </div>
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-sign-in-alt me-1"></i> Login
            </button>
          </div>
          <p class="text-center mb-0">
            Don't have an account? 
            <a href="signup.php">Sign up here</a>
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
