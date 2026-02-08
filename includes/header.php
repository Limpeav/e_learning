<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Define base path for links if project is in a subdirectory (e_learning)
// Better base_url detection
if ($_SERVER['SERVER_PORT'] == '3000' || $_SERVER['HTTP_HOST'] == 'localhost:3000') {
    $base_url = '';
} else {
    // Check if we are in a subdirectory named e_learning
    $base_url = (strpos($_SERVER['REQUEST_URI'], '/e_learning/') === 0) ? '/e_learning' : '';
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Prism.js VS Code Dark Plus Theme -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism-themes/1.9.0/prism-vs.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css" rel="stylesheet">
    
    <link href="<?php echo $base_url; ?>/public/css/style.css" rel="stylesheet">
    
    <!-- Prism.js Core & Plugins -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js"></script>


    <style>
        body { padding-top: 60px; }
        .footer { position: fixed; bottom: 0; width: 100%; height: 60px; line-height: 60px; background-color: #f5f5f5; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $base_url; ?>/index.php">E-Learning</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <?php $current_page = basename($_SERVER['SCRIPT_NAME']); ?>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>/index.php">Home</a>
        </li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'dashboard.php' && strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? 'active' : ''; ?>" href="<?php echo $base_url; ?>/views/admin/dashboard.php">Dashboard</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'dashboard.php' && strpos($_SERVER['SCRIPT_NAME'], '/student/') !== false) ? 'active' : ''; ?>" href="<?php echo $base_url; ?>/views/student/dashboard.php">My Learning</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>/views/auth/profile.php">Profile</a></li>
            <li class="nav-item"><a class="nav-link btn btn-danger text-white btn-sm ms-2" href="<?php echo $base_url; ?>/actions/logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>

        <?php else: ?>
            <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'login.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>/views/auth/login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'register.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>/views/auth/register.php">Register</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
