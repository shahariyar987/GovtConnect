<?php
session_start();
require_once "db_connect.php";

// auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GovConnect — Change Password</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
:root{
  --bg1:#0f172a;
  --bg2:#1e293b;
  --accent1:#667eea;
  --accent2:#764ba2;
  --danger:#ef4444;
  --success:#22c55e;
  --muted:#94a3b8;
}
body{
  margin:0;
  font-family:Inter,system-ui,sans-serif;
  background:linear-gradient(135deg,var(--bg1),var(--bg2));
  color:#fff;
}
.topbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 30px;
  background:rgba(255,255,255,0.05);
  backdrop-filter:blur(12px);
  border-bottom:1px solid rgba(255,255,255,0.1);
}
.brand{font-weight:700;font-size:22px;display:flex;align-items:center;gap:10px}
.container{max-width:600px;margin:50px auto;padding:0 20px}
.card{
  background:rgba(255,255,255,0.05);
  border:1px solid rgba(255,255,255,0.08);
  backdrop-filter:blur(14px);
  border-radius:16px;
  padding:32px;
  box-shadow:0 10px 40px rgba(0,0,0,0.6);
}
h2{
  margin-top:0;
  font-size:26px;
  margin-bottom:20px;
  display:flex;
  align-items:center;
  gap:10px;
}
.form-group{margin-bottom:20px}
.label{font-size:24px;color:var(--muted);margin-bottom:6px;display:block}
.input{
  width:100%;
  padding:14px 16px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,0.15);
  background:rgba(255,255,255,0.07);
  color:#fff;
  font-size:20px;
  box-sizing:border-box;
}
.input:focus{outline:none;border-color:var(--accent1);box-shadow:0 0 0 2px rgba(102,126,234,0.4);}
.btn{
  display:block;width:100%;padding:15px;
  border-radius:12px;border:none;
  background:linear-gradient(90deg,var(--accent1),var(--accent2));
  color:#fff;font-weight:700;font-size:19px;
  cursor:pointer;transition:.25s;
}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(0,0,0,0.5)}
.alert{
  padding:12px 16px;
  border-radius:10px;
  margin-bottom:16px;
  font-size:18px;
}
.alert-success{background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.25);color:#a7f3d0}
.alert-error{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#fecaca}
</style>
</head>
<body>
<header class="topbar">
  <div class="brand"><i class="fa-solid fa-shield-halved"></i> GovConnect</div>
  <button class="btn" style="width:auto;padding:10px 18px;font-size:18px" onclick="location.href='profile.php'">
    <i class="fa-solid fa-arrow-left"></i> Back to Profile
  </button>
</header>

<div class="container">
  <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="card">
    <h2><i class="fa-solid fa-key"></i> Change Password</h2>
    <form method="post" action="change_password_controller.php">
      <div class="form-group">
        <label class="label">Current Password</label>
        <input type="password" name="current_password" class="input" required>
      </div>
      <div class="form-group">
        <label class="label">New Password</label>
        <input type="password" name="new_password" class="input" required>
      </div>
      <div class="form-group">
        <label class="label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="input" required>
      </div>
      <button type="submit" name="change_password" class="btn">
        <i class="fa-solid fa-paper-plane"></i> Update Password
      </button>
    </form>
  </div>
</div>
</body>
</html>
