<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Panel</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%232d7a2d'/><text x='16' y='22' text-anchor='middle' font-size='18' font-family='Arial' font-weight='bold' fill='white'>A</text></svg>">
<link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#1a1a1a;min-height:100vh;}

/* ── ADMIN LOGIN ── */
#page-admin-login{
  display:flex;align-items:center;justify-content:center;
  min-height:100vh;background:#1a1a1a;
}
.admin-login-box{
  background:#fff;border-radius:8px;padding:2.5rem 2rem;
  width:100%;max-width:380px;box-shadow:0 4px 32px rgba(0,0,0,.5);
}
.admin-login-box h2{font-size:1.2rem;font-weight:800;color:#111;margin-bottom:.3rem;}
.admin-login-box p{font-size:.8rem;color:#888;margin-bottom:1.8rem;}
.adm-field{margin-bottom:1rem;}
.adm-field label{display:block;font-size:.8rem;font-weight:700;color:#333;margin-bottom:.4rem;letter-spacing:.04em;}
.adm-field input{
  width:100%;background:#f5f5f5;border:1.5px solid #ddd;
  border-radius:6px;padding:.7rem .9rem;color:#111;font-size:.92rem;outline:none;
  transition:border .2s;
}
.adm-field input:focus{border-color:#2d7a2d;background:#fff;}
.adm-error{color:#c0392b;font-size:.82rem;margin-bottom:.8rem;min-height:1.1rem;}
.btn-adm-login{
  width:100%;background:#2d7a2d;border:none;color:#fff;
  font-weight:700;font-size:.95rem;padding:.75rem;border-radius:6px;
  cursor:pointer;transition:background .2s;letter-spacing:.03em;
}
.btn-adm-login:hover{background:#1d5c1d;}
.btn-adm-login:disabled{background:#aaa;cursor:not-allowed;}
.adm-security{text-align:center;margin-top:1rem;font-size:.72rem;color:#aaa;}

/* ── ADMIN DASHBOARD ── */
#page-admin-dashboard{display:none;flex-direction:column;min-height:100vh;background:#f0f0e8;}
/* green header */
.adm-header{
  background:#2d7a2d;padding:.7rem 1.5rem;
  display:flex;align-items:center;justify-content:space-between;flex-shrink:0;
}
.adm-gbs{display:flex;align-items:center;gap:.8rem;}
.adm-gbs-logo{font-size:1.5rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:.25rem;}
.adm-gbs-sub{font-size:.62rem;color:rgba(255,255,255,.7);margin-top:.1rem;}
.adm-bayer{width:48px;height:48px;border-radius:50%;border:2px solid rgba(255,255,255,.6);
  background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;
  font-size:.55rem;font-weight:800;color:#fff;text-align:center;line-height:1.3;letter-spacing:.03em;}
.adm-header-right{display:flex;align-items:center;gap:1rem;}
.adm-welcome{font-size:.85rem;color:#fff;font-weight:600;}
.btn-logout{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.4);
  color:#fff;font-size:.8rem;font-weight:600;padding:.3rem .9rem;border-radius:4px;cursor:pointer;}
.btn-logout:hover{background:rgba(255,255,255,.25);}
/* sub nav */
.adm-subnav{
  background:#fff;border-bottom:1px solid #ccc;
  padding:.5rem 1.5rem;display:flex;align-items:center;gap:.25rem;
}
.adm-subnav a{
  color:#2a7a2a;font-weight:600;font-size:.92rem;
  text-decoration:none;padding:.25rem .1rem;cursor:pointer;
  border-bottom:2px solid transparent;
}
.adm-subnav a.active{border-bottom-color:#2a7a2a;}
.adm-subnav a:hover{text-decoration:underline;}
.adm-subnav .sep{color:#ccc;margin:0 .4rem;}
/* content */
.adm-content{flex:1;overflow-y:auto;padding:1.2rem 1.5rem;}
.adm-section{display:none;}
.adm-section.active{display:block;}
/* toolbar */
.adm-toolbar{display:flex;align-items:center;gap:2rem;margin-bottom:.9rem;font-size:.88rem;color:#333;}
.adm-toolbar strong{color:#111;}
.excel-icon{font-size:2rem;color:#1d6f2a;cursor:pointer;line-height:1;}
.btn-clear-data{background:#c0392b;border:none;color:#fff;font-size:.8rem;font-weight:700;padding:.35rem .9rem;border-radius:4px;cursor:pointer;margin-left:auto;}
.btn-clear-data:hover{background:#a93226;}
/* table */
.adm-table{width:100%;border-collapse:collapse;background:#fff;font-size:.88rem;}
.adm-table th{
  padding:.65rem 1rem;text-align:left;color:#222;font-weight:700;
  border-bottom:2px solid #ccc;background:#e8e8d8;
}
.adm-table td{padding:.6rem 1rem;color:#333;border-bottom:1px solid #e0e0d0;}
.adm-table tr:nth-child(even) td{background:#f5f5eb;}
.adm-table tr:hover td{background:#eaeadc;}
.adm-empty{text-align:center;padding:2rem;color:#888;font-size:.88rem;}
/* badge */
.badge-live{display:inline-block;background:#e8f5e9;color:#2d7a2d;
  font-size:.72rem;font-weight:700;padding:.2rem .5rem;border-radius:4px;}
.badge-off{display:inline-block;background:#f5f5f5;color:#888;
  font-size:.72rem;font-weight:700;padding:.2rem .5rem;border-radius:4px;}

/* ── Mobile Responsive ── */
@media (max-width: 768px) {
  .adm-header{flex-wrap:wrap;gap:.5rem;padding:.6rem 1rem;}
  .adm-gbs-logo{font-size:1.2rem;}
  .adm-bayer{width:36px;height:36px;font-size:.45rem;}
  .adm-subnav{padding:.4rem 1rem;flex-wrap:wrap;gap:.3rem;}
  .adm-subnav a{font-size:.82rem;}
  .btn-clear-data{font-size:.72rem;padding:.28rem .6rem;}
  .adm-content{padding:.75rem 1rem;overflow-x:auto;}
  .adm-section{overflow-x:auto;-webkit-overflow-scrolling:touch;}
  .adm-table{font-size:.78rem;min-width:480px;}
  .adm-table th,.adm-table td{padding:.4rem .6rem;}
  .adm-toolbar{flex-wrap:wrap;gap:.5rem;}
  .admin-login-box{padding:1.8rem 1.2rem;margin:1rem;}
}
</style>
</head>
<body>

<!-- ══ ADMIN LOGIN ══ -->
<div id="page-admin-login">
  <div class="admin-login-box">
    <h2>Admin Login</h2>
    <p>Restricted access — authorised personnel only</p>
    <div class="adm-field">
      <label>USERNAME</label>
      <input type="text" id="adm-username" placeholder="Enter username" autocomplete="username" oninput="clearAdmError()">
    </div>
    <div class="adm-field">
      <label>PASSWORD</label>
      <input type="password" id="adm-password" placeholder="Enter password" autocomplete="current-password" oninput="clearAdmError()"
             onkeydown="if(event.key==='Enter')handleAdmLogin()">
    </div>
    <div class="adm-error" id="adm-error"></div>
    <button class="btn-adm-login" id="adm-login-btn" onclick="handleAdmLogin()">LOGIN</button>
    <div class="adm-security"><i class="bi bi-lock-fill"></i> All sessions are logged and monitored</div>
  </div>
</div>

<!-- ══ ADMIN DASHBOARD ══ -->
<div id="page-admin-dashboard">
  <!-- Header -->
  <div class="adm-header">
    <div></div>
    <div class="adm-header-right">
      <span class="adm-welcome" id="adm-welcome-name"></span>
      <button class="btn-logout" onclick="handleAdmLogout()">Logout</button>
    </div>
  </div>

  <!-- Sub nav -->
  <div class="adm-subnav">
    <a id="tab-users" class="active" onclick="switchTab('users')">Users</a>
    <span class="sep">|</span>
    <a id="tab-questions" onclick="switchTab('questions')">Questions</a>
    <button class="btn-clear-data" onclick="clearAllData()">🗑 Clear All Data</button>
  </div>

  <!-- Content -->
  <div class="adm-content">

    <!-- USERS -->
    <div id="adm-section-users" class="adm-section active">
      <div class="adm-toolbar">
        <span class="excel-icon" title="Export to Excel" onclick="exportCSV()">
          <i class="bi bi-file-earmark-excel-fill"></i>
        </span>
        <span>Total Users: <strong id="total-users">0</strong></span>
        <span>Currently Logged In: <strong id="live-users">0</strong></span>
      </div>
      <table class="adm-table">
        <thead>
          <tr><th>Name</th><th>Email ID</th><th>Login Time</th><th>Logout Time</th></tr>
        </thead>
        <tbody id="users-tbody">
          <tr><td colspan="4" class="adm-empty">No registered users yet.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- QUESTIONS -->
    <div id="adm-section-questions" class="adm-section">
      <div class="adm-toolbar">
        <span class="excel-icon" title="Export to Excel" onclick="exportCSV()">
          <i class="bi bi-file-earmark-excel-fill"></i>
        </span>
      </div>
      <table class="adm-table">
        <thead>
          <tr><th>Name</th><th>Email ID</th><th>Question</th><th>Time</th></tr>
        </thead>
        <tbody id="questions-tbody">
          <tr><td colspan="4" class="adm-empty">No questions submitted yet.</td></tr>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
// ── Hardcoded admin credentials (change these to your real ones) ──
var ADMIN_CREDENTIALS = [
  { username: 'admin', password: 'admin@123' }
];

function clearAdmError() {
  document.getElementById('adm-error').textContent = '';
}

function handleAdmLogin() {
  var username = document.getElementById('adm-username').value.trim();
  var password = document.getElementById('adm-password').value;
  var btn = document.getElementById('adm-login-btn');

  if (!username) { document.getElementById('adm-error').textContent = 'Please enter username.'; document.getElementById('adm-username').focus(); return; }
  if (!password) { document.getElementById('adm-error').textContent = 'Please enter password.'; document.getElementById('adm-password').focus(); return; }

  btn.textContent = 'Verifying…';
  btn.disabled = true;

  setTimeout(function() {
    var match = ADMIN_CREDENTIALS.find(function(c) {
      return c.username === username && c.password === password;
    });

    if (!match) {
      document.getElementById('adm-error').textContent = 'Invalid username or password.';
      btn.textContent = 'LOGIN';
      btn.disabled = false;
      document.getElementById('adm-password').value = '';
      document.getElementById('adm-password').focus();
      return;
    }

    sessionStorage.setItem('adm_logged_in', '1');
    sessionStorage.setItem('adm_user', username);
    showDashboard(username);
  }, 800);
}

function handleAdmLogout() {
  sessionStorage.removeItem('adm_logged_in');
  sessionStorage.removeItem('adm_user');
  document.getElementById('adm-username').value = '';
  document.getElementById('adm-password').value = '';
  document.getElementById('page-admin-dashboard').style.display = 'none';
  document.getElementById('page-admin-login').style.display = 'flex';
}

function showDashboard(username) {
  document.getElementById('page-admin-login').style.display = 'none';
  var dash = document.getElementById('page-admin-dashboard');
  dash.style.display = 'flex';
  document.getElementById('adm-welcome-name').textContent = 'Welcome, ' + username;
  loadUsers();
  loadQuestions();
}

function switchTab(tab) {
  ['users','questions'].forEach(function(t) {
    document.getElementById('adm-section-'+t).classList.remove('active');
    document.getElementById('tab-'+t).classList.remove('active');
  });
  document.getElementById('adm-section-'+tab).classList.add('active');
  document.getElementById('tab-'+tab).classList.add('active');
  // Refresh data on tab switch
  if (tab === 'users') loadUsers();
  if (tab === 'questions') loadQuestions();
}

function loadUsers() {
  var users = [];
  try { users = JSON.parse(localStorage.getItem('pw_registered_users') || '[]'); } catch(e){}
  var tbody = document.getElementById('users-tbody');
  document.getElementById('total-users').textContent = users.length;
  var liveUser = localStorage.getItem('pw_user_email') || '';
  var heartbeat = parseInt(localStorage.getItem('pw_heartbeat') || '0');
  var isRecent = heartbeat && (Date.now() - heartbeat) < 60000;
  var liveCount = (liveUser && isRecent) ? 1 : 0;
  document.getElementById('live-users').textContent = liveCount;

  if (!users.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="adm-empty">No registered users yet.</td></tr>';
    return;
  }
  tbody.innerHTML = users.map(function(u) {
    var isLive = isRecent && liveUser && liveUser.toLowerCase() === u.email.toLowerCase();
    var logoutCell = isLive
      ? '<span class="badge-live">● Online</span>'
      : (u.lastLogoutAt || '—');
    return '<tr><td>'+esc(u.name)+'</td><td>'+esc(u.email)+'</td>'
      +'<td>'+(u.lastLoginAt||'—')+'</td>'
      +'<td>'+logoutCell+'</td></tr>';
  }).join('');
}

function loadQuestions() {
  var qs = [];
  try { qs = JSON.parse(localStorage.getItem('pw_questions') || '[]'); } catch(e){}
  var tbody = document.getElementById('questions-tbody');
  if (!qs.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="adm-empty">No questions submitted yet.</td></tr>';
    return;
  }
  tbody.innerHTML = qs.map(function(q) {
    return '<tr><td>'+esc(q.name)+'</td><td>'+esc(q.email)+'</td><td>'+esc(q.text)+'</td><td>'+esc(q.time)+'</td></tr>';
  }).join('');
}

function exportCSV() {
  var activeSection = document.querySelector('.adm-section.active');
  if (activeSection.id === 'adm-section-users') {
    var users = [];
    try { users = JSON.parse(localStorage.getItem('pw_registered_users') || '[]'); } catch(e){}
    if (!users.length) { alert('No users to export.'); return; }
    var csv = 'Name,Email ID,Login Time,Logout Time\n';
    csv += users.map(function(u){ return [u.name,u.email,u.lastLoginAt||'',u.lastLogoutAt||''].join(','); }).join('\n');
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'registered_users.csv';
    a.click();
  } else if (activeSection.id === 'adm-section-questions') {
    var questions = [];
    try { questions = JSON.parse(localStorage.getItem('pw_questions') || '[]'); } catch(e){}
    if (!questions.length) { alert('No questions to export.'); return; }
    var csv = 'Name,Email ID,Question,Time\n';
    csv += questions.map(function(q){ return [q.name,q.email,q.text,q.time].join(','); }).join('\n');
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'questions.csv';
    a.click();
  }
}

function clearAllData() {
  if (!confirm('This will delete ALL registered users, questions, and session data. Are you sure?')) return;
  ['pw_registered_users','pw_questions','pw_feedback','pw_user_name','pw_user_email','pw_page'].forEach(function(k){
    localStorage.removeItem(k);
  });
  loadUsers();
  loadQuestions();
  alert('All data cleared successfully.');
}

function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Auto-restore session if already logged in
(function(){
  if (sessionStorage.getItem('adm_logged_in') === '1') {
    showDashboard(sessionStorage.getItem('adm_user') || 'admin');
  }
})();

// Auto-refresh data every 5 seconds when dashboard is visible
setInterval(function(){
  if (sessionStorage.getItem('adm_logged_in') === '1') {
    var active = document.querySelector('.adm-section.active');
    if (!active) return;
    var id = active.id;
    if (id === 'adm-section-users') loadUsers();
    else if (id === 'adm-section-questions') loadQuestions();
  }
}, 2000);
</script>
</body>
</html>
