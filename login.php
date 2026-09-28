<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>GovConnect — Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
  body {
    margin:0;
    font-family:'Poppins', sans-serif;
    background:linear-gradient(135deg,#1f1c2c,#928dab);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:30px;
    color:#fff;
  }
  .container {
    display:grid;
    grid-template-columns:1fr 1fr;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 8px 40px rgba(0,0,0,0.4);
    max-width:1100px;
    width:100%;
    min-height:550px;
  }
  .form-box {
    padding:40px;
    background:rgba(20,20,30,0.9);
    display:flex;
    flex-direction:column;
    justify-content:center;
  }
  .form-box h2 { margin:0 0 20px; font-size:24px; text-align:center; }
  .row { margin-bottom:15px; }
  .control {
    width:100%;
    padding:14px;
    border-radius:10px;
    border:none;
    font-size:19px;
    outline:none;
    box-sizing:border-box;
  }
  .btn {
    composes: control; /* makes it inherit same size */
    width:100%;
    padding:14px;
    border-radius:10px;
    border:none;
    font-weight:bold;
    cursor:pointer;
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:#fff;
    transition:transform 0.2s;
    font-size:20px;
    box-sizing:border-box;
  }
  .btn:hover { transform:scale(1.02); }
  .forgot { text-align:right; font-size:17px; margin-top:8px; }
  .forgot a { color:#cfe4ff; text-decoration:none; }
  .forgot a:hover{ text-decoration:underline; }

  .message { padding:12px 14px; margin-bottom:15px; border-radius:8px; font-size:19px; display:flex; align-items:center; gap:10px; }
  .error { background:#ffdddd; color:#900; border:1px solid #e0a0a0; }
  .success { background:#ddffdd; color:#060; border:1px solid #90c090; }

  .info-box {
    padding:40px;
    background:linear-gradient(135deg,#232526,#414345);
    display:flex;
    flex-direction:column;
    justify-content:center;
    text-align:center;
  }
  .info-box h1 { font-size:35px; margin-bottom:15px; }
  .info-box p { font-size:20px; margin-bottom:12px; opacity:0.9; }
  .toggles { display:flex; justify-content:center; gap:15px; margin-top:20px; }
  .toggles button {
    flex:1;
    padding:14px;
    border-radius:10px;
    border:none;
    font-weight:600;
    cursor:pointer;
    background:linear-gradient(180deg,#f2c94c,#f2994a);
    color:#222;
    transition:transform 0.2s;
  }
  .toggles button:hover { transform:scale(1.05); }
  .register-link { margin-top:24px; font-size:17px; color:#fff; }
  .register-link a { color:#fff; font-weight:600; text-decoration:underline; }

  .hidden { display:none; }

  @media(max-width:900px) {
    .container { grid-template-columns:1fr; }
    .info-box { order:-1; padding:30px; }
    .form-box { padding:30px; }
  }


  /* Light theme for Login page */
  body.light-theme {
    background: #e6e1f9;
    color: #333;
  }
  body.light-theme .container {
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.1);
    background: #f6f5fa;
  }
  body.light-theme .form-box {
    background: #f8f7fc;
    color: #222;
  }
  body.light-theme .btn {
    background: linear-gradient(90deg, #9b87e8, #a77be8);
    color: #fff;
  }
  body.light-theme .info-box {
    background: linear-gradient(135deg, #c1bae6, #9c96d6);
    color: #222;
  }
  body.light-theme .control {
    background: #fff;
    color: #222;
    border: 1px solid #bbb;
  }
  body.light-theme .forgot a, 
  body.light-theme .register-link a {
    color: #5a4d9f;
  }
  body.light-theme .message.error {
    background: #ffdddd;
    color: #900;
    border-color: #e0a0a0;
  }
  body.light-theme .message.success {
    background: #ddffdd;
    color: #060;
    border-color: #90c090;
  }
</style>
</head>
<body>

<!-- Theme toggle button top right -->
<button id="themeToggleBtn" style="
  position: fixed; 
  top: 15px; 
  right: 15px; 
  padding: 10px 18px; 
  background: linear-gradient(180deg,#f2c94c,#f2994a); 
  border: none; 
  border-radius: 12px; 
  cursor: pointer; 
  font-weight: bold;
  z-index: 9999;
">
  Light Theme
</button>

<div class="container">

  <!-- LEFT: Login Forms -->
  <div class="form-box">
    <?php if($error): ?>
      <div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
      <div class="message success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>

    <!-- Citizen Login (default) -->
    <form id="userForm" method="post" action="/govconnect/login_process.php">
      <input type="hidden" name="role" value="user"/>
      <h2><i class="fa-solid fa-user"></i> Citizen Login</h2>
      <div class="row"><input class="control" type="email" name="email" placeholder="Email Address" required></div>
      <div class="row"><input class="control" type="password" name="password" placeholder="Password" required></div>
      <button type="submit" class="btn">Login</button>
      <div class="forgot"><a href="/govconnect/forgot_password.php">Forgot password?</a></div>
    </form>

    <!-- Admin Login -->
    <form id="adminForm" class="hidden" method="post" action="/govconnect/login_process.php">
      <input type="hidden" name="role" value="admin"/>
      <h2><i class="fa-solid fa-shield-halved"></i> Administration</h2>
      <div class="row"><input class="control" type="email" name="email" placeholder="Admin Email" required></div>
      <div class="row"><input class="control" type="password" name="password" placeholder="Password" required></div>
      <button type="submit" class="btn">Login</button>
      <div class="forgot"><a href="/govconnect/forgot_password.php">Forgot password?</a></div>
    </form>

    <!-- Response Team Login -->
    <form id="responseForm" class="hidden" method="post" action="/govconnect/login_process.php">
      <input type="hidden" name="role" value="response"/>
      <h2><i class="fa-solid fa-truck-fast"></i> Response Team</h2>
      <div class="row"><input class="control" type="email" name="email" placeholder="Team Email" required></div>
      <div class="row"><input class="control" type="password" name="password" placeholder="Password" required></div>
      <button type="submit" class="btn">Login</button>
      <div class="forgot"><a href="/govconnect/forgot_password.php">Forgot password?</a></div>
    </form>
  </div>

  <!-- RIGHT: Info + Switch -->
  <div class="info-box">
    <h1>Welcome Back</h1>
    <p>Access your GovConnect account securely.<br>
       Default login is for Citizens.</p>
    <p style="font-size:17px;opacity:0.85">Response Team accounts require admin approval before activation.</p>

    <div class="toggles">
      <button id="btnAdmin">Administration</button>
      <button id="btnResponse">Response Team</button>
    </div>

    <div class="register-link">
      Don’t have an account? <a href="/govconnect/register.php">Register here</a>
    </div>
  </div>

</div>

<script>
  const userForm = document.getElementById('userForm');
  const adminForm = document.getElementById('adminForm');
  const responseForm = document.getElementById('responseForm');
  const btnAdmin = document.getElementById('btnAdmin');
  const btnResponse = document.getElementById('btnResponse');

  btnAdmin.addEventListener('click', () => {
    userForm.classList.add('hidden');
    responseForm.classList.add('hidden');
    adminForm.classList.remove('hidden');
  });

  btnResponse.addEventListener('click', () => {
    userForm.classList.add('hidden');
    adminForm.classList.add('hidden');
    responseForm.classList.remove('hidden');
  });

  // Theme toggle
  const themeToggleBtn = document.getElementById('themeToggleBtn');

  // On page load, check saved theme and apply it
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    document.body.classList.add('light-theme');
    themeToggleBtn.textContent = 'Dark Theme';
  } else {
    themeToggleBtn.textContent = 'Light Theme';
  }

  // Toggle theme on button click
  themeToggleBtn.addEventListener('click', () => {
    const isLight = document.body.classList.toggle('light-theme');
    if (isLight) {
      localStorage.setItem('theme', 'light');
      themeToggleBtn.textContent = 'Dark Theme';
    } else {
      localStorage.setItem('theme', 'dark');
      themeToggleBtn.textContent = 'Light Theme';
    }
  });
</script>

</body>
</html>
