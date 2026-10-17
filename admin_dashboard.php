<?php
session_start();
require_once "db_connect.php";

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Flash messages
$flash = "";
if (!empty($_SESSION['flash_success'])) {
    $flash = "<div class='flash success'>{$_SESSION['flash_success']}</div>";
    unset($_SESSION['flash_success']);
}
if (!empty($_SESSION['flash_error'])) {
    $flash = "<div class='flash error'>{$_SESSION['flash_error']}</div>";
    unset($_SESSION['flash_error']);
}

// Fetch problems
$stmt = $pdo->query("
    SELECT p.*, u.name AS user_name, u.email, u.phone
    FROM problems p
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.created_at DESC
");
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending response teams
$stmt2 = $pdo->query("SELECT * FROM users WHERE role='response' AND status='pending'");
$pending_responses = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Fetch active teams
$teams = $pdo->query("SELECT user_id, name, category FROM users WHERE role='response' AND status='active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GovConnect — Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
:root {
  --bg1:#0f172a; --bg2:#1e293b;
  --accent1:#667eea; --accent2:#764ba2;
  --success:#27ae60; --danger:#e74c3c;
  --muted:#94a3b8;
}
body {
  margin:0;font-family:Inter,sans-serif;
  background:linear-gradient(135deg,var(--bg1),var(--bg2));
  color:#fff;
}
.topbar {
  display:flex;justify-content:space-between;align-items:center;
  padding:14px 28px;
  background:rgba(255,255,255,0.05);
  backdrop-filter:blur(10px);
  border-bottom:1px solid rgba(255,255,255,0.1);
}
.brand{display:flex;align-items:center;gap:10px;font-weight:700}
.user-area{display:flex;align-items:center;gap:14px}
.btn-ghost{
  padding:8px 14px;border-radius:10px;
  border:1px solid rgba(255,255,255,0.15);
  background:rgba(255,255,255,0.05);
  color:#fff;cursor:pointer;
}
.btn-ghost:hover{background:rgba(255,255,255,0.15)}
.container{max-width:1250px;margin:25px auto;padding:0 18px}

/* Flash */
.flash{padding:14px;border-radius:12px;margin-bottom:20px;font-weight:600;text-align:center}
.flash.success{background:rgba(39,174,96,0.2);border:1px solid #27ae60;color:#2ecc71}
.flash.error{background:rgba(231,76,60,0.2);border:1px solid #e74c3c;color:#e74c3c}

/* Map */
#map{height:460px;border-radius:14px;margin-bottom:30px}
.legend{position:absolute;top:15px;right:15px;background:rgba(0,0,0,0.6);color:#fff;padding:10px 14px;border-radius:10px;font-size:19px}
.legend span{display:inline-block;width:14px;height:14px;margin-right:6px;border-radius:3px}

/* Tabs */
.tabs{display:flex;gap:18px;margin:20px 0;border-bottom:2px solid rgba(255,255,255,0.1)}
.tab-btn{position:relative;padding:10px 18px;cursor:pointer;font-weight:600;color:var(--muted);transition:0.3s}
.tab-btn.active{color:#fff}
.tab-btn.active::after{content:"";position:absolute;bottom:-2px;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent1),var(--accent2));border-radius:3px}

/* Cards */
.problem-card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:18px;margin-bottom:20px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.5);transition:transform .25s}
.problem-card:hover{transform:translateY(-3px)}
.problem-header{padding:16px 20px;background:rgba(255,255,255,0.05);display:flex;justify-content:space-between;align-items:center}
.problem-header h4{margin:0;font-size:21px}
.problem-body{padding:20px;font-size:17px;line-height:1.6}
.contact-card{background:rgba(255,255,255,0.05);padding:12px 16px;border-radius:12px;margin-top:12px;font-size:17px}
.problem-footer{display:flex;gap:10px;flex-wrap:wrap;padding:14px 20px;background:rgba(255,255,255,0.05);border-top:1px solid rgba(255,255,255,0.08)}
.footer-btn{padding:10px 14px;border-radius:10px;border:none;background:rgba(255,255,255,0.08);color:#fff;font-weight:600;cursor:pointer;transition:.25s}
.footer-btn:hover{background:rgba(255,255,255,0.2)}
select.footer-btn{appearance:none;padding-right:30px}
select.footer-btn option{background:#1e293b;color:#fff}

/* Badges */
.badge{padding:6px 12px;border-radius:8px;font-size:15px;font-weight:600}
.status-pending{background:#f39c12}
.status-verified{background:#3498db}
.status-assigned{background:#9b59b6}
.status-resolved{background:#27ae60}
.status-rejected{background:#e74c3c}
.priority-low{background:#2ecc71}
.priority-medium{background:#f1c40f;color:#000}
.priority-high{background:#e67e22}
.priority-sos{background:#e74c3c}

/* Light Theme */
.light-theme {
  --bg1:#f3e8ff; --bg2:#e9d5ff;
  --accent1:#a78bfa; --accent2:#c084fc;
  --muted:#6b7280;
  color:#1f1f1f;
  background:linear-gradient(135deg,var(--bg1),var(--bg2));
}
.light-theme .btn-ghost,
.light-theme .footer-btn {color:#333;background:rgba(255,255,255,0.8);border:1px solid #ddd;}
.light-theme .btn-ghost:hover,
.light-theme .footer-btn:hover {background:rgba(255,255,255,1);}
.light-theme .problem-card{background:rgba(255,255,255,0.8);color:#222;}
.light-theme .problem-header{background:rgba(255,255,255,0.9);}
.light-theme .problem-footer{background:rgba(255,255,255,0.9);}
.light-theme .contact-card{background:rgba(255,255,255,0.9);}
</style>
</head>
<body>

<header class="topbar">
  <div class="brand"><i class="fa-solid fa-shield-halved"></i> GovConnect Admin</div>
  <div class="user-area">
    <button id="themeToggle" class="btn-ghost"><i class="fa-solid fa-circle-half-stroke"></i> Theme</button>
    <span>Welcome, <?= htmlspecialchars($_SESSION['name']); ?></span>
    <button class="btn-ghost" onclick="location.href='logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
  </div>
</header>

<div class="container">
  <?= $flash ?>
  <h3><i class="fa-solid fa-map-location-dot"></i> Problem Map Overview</h3>
  <div id="map"></div>
  <div class="tabs">
    <div class="tab-btn active" data-tab="all">All</div>
    <div class="tab-btn" data-tab="police">Police</div>
    <div class="tab-btn" data-tab="fire">Fire</div>
    <div class="tab-btn" data-tab="medical">Medical</div>
    <div class="tab-btn" data-tab="gov">Government</div>
    <div class="tab-btn" data-tab="other">Other</div>
    <div class="tab-btn" data-tab="approval">Response Approvals</div>
  </div>

  <!-- Problems -->
  <div id="problems-container">
  <?php foreach($problems as $row): 
    $statusClass = "status-".strtolower($row['status'] ?: 'pending');
    $prioClass = "priority-".strtolower($row['priority'] ?: 'low');
  ?>
  <div class="problem-card" data-category="<?= htmlspecialchars($row['category']) ?>" data-priority="<?= strtolower($row['priority']) ?>">
    <div class="problem-header">
      <h4>#<?= $row['problem_id'] ?> — <?= ucfirst($row['category']) ?></h4>
      <div>
        <span class="badge <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span>
        <span class="badge <?= $prioClass ?>"><?= ucfirst($row['priority']) ?></span>
      </div>
    </div>
    <div class="problem-body">
      <p><b>Description:</b> <?= htmlspecialchars($row['description']) ?></p>
      <?php if($row['suggestion']): ?><p><b>Suggestion:</b> <?= htmlspecialchars($row['suggestion']) ?></p><?php endif; ?>
      <p><b>Location:</b> <?= htmlspecialchars($row['location']) ?></p>
      <div class="contact-card">
        <i class="fa fa-user"></i> <?= htmlspecialchars($row['user_name']) ?><br>
        <i class="fa fa-envelope"></i> <?= htmlspecialchars($row['email']) ?><br>
        <i class="fa fa-phone"></i> <?= htmlspecialchars($row['phone']) ?>
      </div>
    </div>
    <div class="problem-footer">
      <?php if($row['status']=='pending'): ?>
        <form method="post" action="admin_actions.php">
          <input type="hidden" name="problem_id" value="<?= $row['problem_id'] ?>">
          <button name="action" value="verify" class="footer-btn">✅ Accept</button>
          <button name="action" value="reject" class="footer-btn">❌ Reject</button>
        </form>
      <?php elseif($row['status']=='verified'): ?>
        <form method="post" action="admin_actions.php">
          <input type="hidden" name="problem_id" value="<?= $row['problem_id'] ?>">
          <select name="priority" class="footer-btn" required>
            <option value="">Set Priority</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="sos">SOS</option>
          </select>
          <select name="assigned_team_id" class="footer-btn" required>

            <option value="">Assign Team</option>
            <?php foreach($teams as $t): if($t['category']==$row['category']): ?>
              <option value="<?= $t['user_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endif; endforeach; ?>
          </select>
          <button name="action" value="assign" class="footer-btn">✔ Confirm</button>
        </form>
      <?php elseif($row['status']=='resolved'): ?>
        <form method="post" action="admin_actions.php">
          <input type="hidden" name="problem_id" value="<?= $row['problem_id'] ?>">
          <button name="action" value="delete" class="footer-btn">🗑 Delete</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  </div>

  <div id="approval-container" style="display:none;">
    <h3>Pending Response Team Approvals</h3>
    <table style="width:100%;background:rgba(255,255,255,0.05);border-radius:12px;overflow:hidden">
      <tr style="background:rgba(255,255,255,0.1)">
        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th>
      </tr>
      <?php foreach($pending_responses as $r): ?>
      <tr>
        <td><?= $r['user_id'] ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars($r['phone']) ?></td>
        <td>
          <form method="post" action="admin_actions.php" style="display:inline">
            <input type="hidden" name="user_id" value="<?= $r['user_id'] ?>">
            <button name="action" value="approve_team" class="footer-btn">✅ Approve</button>
            <button name="action" value="reject_team" class="footer-btn">❌ Reject</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.getElementById("themeToggle").addEventListener("click",()=>{
  document.body.classList.toggle("light-theme");
});

// Map setup
var dhakaBounds = L.latLngBounds([23.65,90.30],[23.90,90.55]);
var map = L.map('map',{center:[23.78,90.40],zoom:12,maxBounds:dhakaBounds,maxBoundsViscosity:1});
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap',minZoom:11,maxZoom:16}).addTo(map);
<?php foreach($problems as $p): if(!empty($p['latitude']) && !empty($p['longitude'])):
$color = ($p['priority']=='sos' || $p['priority']=='high')?'red':(($p['priority']=='medium')?'orange':'blue');
if ($p['status']=='pending') $color = 'gray';
?>
L.circleMarker([<?= $p['latitude']?>,<?= $p['longitude']?>],{color:"<?= $color?>",radius:8})
  .addTo(map).bindPopup("<b><?= addslashes($p['category']) ?></b><br><?= addslashes($p['description']) ?>");
<?php endif; endforeach; ?>

// Tabs filter
document.querySelectorAll('.tab-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const tab=btn.dataset.tab;
    document.getElementById('approval-container').style.display=(tab==='approval'?'block':'none');
    document.getElementById('problems-container').style.display=(tab==='approval'?'none':'block');
    document.querySelectorAll('.problem-card').forEach(c=>{
      if(tab==='all'||c.dataset.category===tab) c.style.display='block';
      else c.style.display='none';
    });
  });
});
</script>

<script>
  // Auto-dismiss flash messages after 20 seconds
  document.addEventListener('DOMContentLoaded', function () {
    const flash = document.querySelector('.flash');
    if (flash) {
      setTimeout(() => {
        flash.style.transition = "opacity 0.5s ease";
        flash.style.opacity = '0';
        setTimeout(() => flash.remove(), 500); // Optional: remove from DOM
      }, 9000); // 09 seconds
    }
  });
</script>

</body>
</html>
