<?php
session_start();
require_once "db_connect.php";

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch reports
$stmt = $pdo->prepare("SELECT * FROM problems WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch map reports
$mapStmt = $pdo->prepare("SELECT * FROM problems WHERE status IN ('verified','assigned')");
$mapStmt->execute();
$mapReports = $mapStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GovConnect — Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>
:root {
  --bg1:#0f172a;
  --bg2:#1e293b;
  --accent1:#667eea;
  --accent2:#764ba2;
  --success:#27ae60;
  --danger:#e74c3c;
  --muted:#94a3b8;
}

/* Light (lavender) theme */
body.light {
  --bg1:#e6e1f9;
  --bg2:#f4f0ff;
  --accent1:#b48cf0;
  --accent2:#d1a9ff;
  --success:#2ecc71;
  --danger:#e74c3c;
  --muted:#555;
  color:#222;
  background: linear-gradient(135deg,var(--bg1),var(--bg2));
}

/* Base */
body {
  margin:0;
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg,var(--bg1),var(--bg2));
  color:#fff;
}

/* Topbar */
.topbar {
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 28px;
  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(12px);
  border-bottom:1px solid rgba(255,255,255,0.08);
}
.brand { display:flex;align-items:center;gap:10px;font-weight:700; }
.brand i { font-size:25px; }
.user-area { display:flex;align-items:center;gap:14px; }
.user-area span { font-weight:600; }
.btn-ghost {
  background: rgba(255,255,255,0.08);
  border:1px solid rgba(255,255,255,0.12);
  padding:8px 14px;
  border-radius:10px;
  color:#fff;
  cursor:pointer;
  transition:all .25s;
}
.btn-ghost:hover { background: rgba(255,255,255,0.18); }

/* Light mode button adjustments */
body.light .btn-ghost {
  background: rgba(0,0,0,0.05);
  border:1px solid rgba(0,0,0,0.1);
  color:#333;
}
body.light .btn-ghost:hover { background: rgba(0,0,0,0.1); }

.container { max-width:1100px; margin:30px auto; padding:0 18px; }

.card {
  background: rgba(255,255,255,0.05);
  border:1px solid rgba(255,255,255,0.08);
  backdrop-filter: blur(14px);
  border-radius:14px;
  padding:18px;
  margin-bottom:20px;
  box-shadow:0 6px 25px rgba(0,0,0,0.4);
}
body.light .card {
  background: rgba(255,255,255,0.9);
  border:1px solid rgba(0,0,0,0.1);
  color:#222;
  box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

#map { height:420px; border-radius:12px; }

.cta-btn {
  display:block;
  width:100%;
  padding:14px;
  border:none;
  border-radius:12px;
  background: linear-gradient(90deg,var(--accent1),var(--accent2));
  color:#fff;
  font-weight:700;
  font-size:20px;
  cursor:pointer;
  margin-top:14px;
  transition: all .25s;
}
.cta-btn:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,0.4); }

.sos-btn {
  display:block;
  width:100%;
  padding:14px;
  border:none;
  border-radius:12px;
  background: linear-gradient(90deg,#ff4d4d,#ff7a1a);
  color:#fff;
  font-weight:700;
  font-size:25px;
  cursor:pointer;
  margin-top:10px;
  transition: all .25s;
}
.sos-btn:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,0.5); }

.warning {
  background: rgba(255,0,0,0.08);
  border:1px solid rgba(255,0,0,0.2);
  padding:10px;
  border-radius:10px;
  font-size:22px;
  margin-bottom:20px;
}
body.light .warning {
  background: rgba(255,192,203,0.3);
  border:1px solid rgba(255,0,0,0.2);
}

.flash { margin-bottom:20px; padding:12px; border-radius:10px; font-weight:600; }
.flash.success { background: rgba(39,174,96,0.2); color: #2ecc71; border:1px solid #27ae60; }
.flash.error { background: rgba(231,76,60,0.2); color: #e74c3c; border:1px solid #e74c3c; }

table { width:100%; border-collapse:collapse; font-size:19px; }
thead { background:rgba(255,255,255,0.08); }
thead th { text-align:left; padding:10px; }
tbody td { padding:10px; border-top:1px solid rgba(255,255,255,0.06); }
.status-badge {
  padding:4px 10px;
  border-radius:999px;
  font-size:21px;
  font-weight:600;
  color:#fff;
}
.status-pending{ background:#f39c12; }
.status-verified{ background:var(--success); }
.status-assigned{ background:#0ea5a1; }
.status-resolved{ background:#2b7cff; }
.status-rejected{ background:var(--danger); }
</style>
</head>
<body>

<!-- Topbar -->
<header class="topbar">
  <div class="brand"><i class="fa-solid fa-shield-halved"></i> GovConnect</div>
  <div class="user-area">
    <span>Welcome, <?= htmlspecialchars($_SESSION['name']); ?></span>
    <!-- 🌙 Theme Toggle Button -->
    <button class="btn-ghost" id="themeToggle"><i class="fa-solid fa-sun"></i> Theme</button>
    <button class="btn-ghost" onclick="location.href='profile.php'"><i class="fa-solid fa-user"></i> Profile</button>
    <button class="btn-ghost" onclick="location.href='logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
  </div>
</header>

<div class="container">
  <!-- Flash Messages -->
  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="flash success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
  <?php elseif (!empty($_SESSION['flash_error'])): ?>
    <div class="flash error"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <!-- Map -->
  <div class="card">
    <h4><i class="fa-solid fa-map-location-dot"></i> Active Issues Map</h4>
    <div id="map"></div>
  </div>

  <button class="cta-btn" onclick="location.href='submit_problem.php'"><i class="fa-solid fa-plus"></i> Submit New Problem</button>
  <button class="sos-btn" onclick="triggerSOS()">⚠️ Emergency SOS</button>

  <script>
  function triggerSOS(){
      if(!navigator.geolocation){alert("Location not supported");return;}
      navigator.geolocation.getCurrentPosition(pos=>{
          const lat=pos.coords.latitude, lng=pos.coords.longitude;
          window.location.href=`process_submit_problem.php?sos=1&lat=${lat}&lng=${lng}`;
      },()=>alert("Failed to get location"));
  }
  </script>

  <div class="warning"><i class="fa-solid fa-triangle-exclamation"></i> Submitting false or misleading reports is a punishable offense.</div>

  <div class="card">
    <h4><i class="fa-solid fa-clipboard-list"></i> My Reports</h4>
    <table>
      <thead>
        <tr><th>ID</th><th>Category</th><th>Description</th><th>Location</th><th>Status</th><th>Suggestion</th><th>Submitted</th></tr>
      </thead>
      <tbody>
      <?php if (count($reports)>0): foreach($reports as $row): ?>
        <?php $s = strtolower($row['status'] ?: 'pending'); ?>
        <tr>
          <td><?= $row['problem_id'] ?></td>
          <td><?= ucfirst(htmlspecialchars($row['category'])) ?></td>
          <td><?= htmlspecialchars($row['description']) ?></td>
          <td><?= htmlspecialchars($row['location_name'] ?: $row['location']) ?></td>
          <td><span class="status-badge status-<?= $s ?>"><?= ucfirst($row['status'] ?: 'Pending') ?></span></td>
          <td><?= htmlspecialchars($row['suggestion']) ?></td>
          <td><?= $row['created_at'] ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="7" style="text-align:center;color:var(--muted)">No reports yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Theme toggle
const themeToggle = document.getElementById('themeToggle');
themeToggle.addEventListener('click', ()=>{
  document.body.classList.toggle('light');
  const icon = themeToggle.querySelector('i');
  if(document.body.classList.contains('light')){
    icon.classList.replace('fa-sun','fa-moon');
  } else {
    icon.classList.replace('fa-moon','fa-sun');
  }
});

// Auto-hide flash messages after 8s
setTimeout(()=>{
    document.querySelectorAll('.flash').forEach(el=>{
        el.style.transition="opacity 1s";
        el.style.opacity="0";
        setTimeout(()=>el.remove(),1000);
    });
}, 8000);
</script>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
var dhakaBounds = L.latLngBounds([23.65, 90.30], [23.90, 90.55]);
var map = L.map('map', {
  center: [23.78, 90.40],
  zoom: 12,
  maxBounds: dhakaBounds,
  maxBoundsViscosity: 1.0,
  minZoom: 11,
  maxZoom: 16
});
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);
<?php foreach($mapReports as $r): if(!empty($r['latitude']) && !empty($r['longitude'])): ?>
L.marker([<?= (float)$r['latitude']?>,<?= (float)$r['longitude']?>])
  .addTo(map).bindPopup("<b><?= addslashes($r['category']) ?></b><br><?= addslashes($r['description']) ?>");
<?php endif; endforeach; ?>
</script>
</body>
</html>
