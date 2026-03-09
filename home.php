<?php
session_start();
require_once "db.php";

/* -------------------------------------------------
   AJAX JSON helper
------------------------------------------------- */
function respond_json($ok, $msg = "", $redirect = "", $type = "success"){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "ok"       => (bool)$ok,
        "msg"      => $msg,
        "type"     => $type,
        "redirect" => $redirect
    ]);
    exit;
}

/* ======================
   HANDLE LOGIN (Normal + AJAX)
====================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $isAjax = isset($_POST["ajax"]) && $_POST["ajax"] == "1";

    $account_id = trim($_POST["account_id"] ?? "");
    $password   = $_POST["password"] ?? "";

    // Request info for logs
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    if (!$account_id || !$password) {
        if ($isAjax) respond_json(false, "Please fill in all fields", "", "error");

        $_SESSION["toast"] = ["type" => "error", "msg" => "Please fill in all fields"];
        header("Location: home.php");
        exit;
    }

    // ✅ Select fname + lname (fix: you were using $user['name'] but it's not selected)
    $stmt = $conn->prepare("
        SELECT id, account_id, role, password, fname, lname
        FROM accounts
        WHERE account_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // ❌ Account not found
    if (!$user = $result->fetch_assoc()) {

        $log = $conn->prepare("
            INSERT INTO user_logs (user_id, account_id, name, login_at, status, ip_address, user_agent)
            VALUES (NULL, ?, 'Unknown', NOW(), 'Invalid log in', ?, ?)
        ");
        $log->bind_param("sss", $account_id, $ip, $ua);
        $log->execute();

        if ($isAjax) respond_json(false, "Account not found", "", "error");

        $_SESSION["toast"] = ["type" => "error", "msg" => "Account not found"];
        header("Location: home.php");
        exit;
    }

    // ✅ Build full name
    $fullName = trim(($user["fname"] ?? "") . " " . ($user["lname"] ?? ""));
    if ($fullName === "") $fullName = $user["account_id"];

    // ❌ Wrong password
    if (!password_verify($password, $user["password"])) {

        $log = $conn->prepare("
            INSERT INTO user_logs (user_id, account_id, name, login_at, status, ip_address, user_agent)
            VALUES (?, ?, ?, NOW(), 'Invalid log in', ?, ?)
        ");
        $log->bind_param("issss", $user["id"], $user["account_id"], $fullName, $ip, $ua);
        $log->execute();

        if ($isAjax) respond_json(false, "Invalid password", "", "error");

        $_SESSION["toast"] = ["type" => "error", "msg" => "Invalid password"];
        header("Location: home.php");
        exit;
    }

    // ✅ LOGIN SUCCESS
    $_SESSION["user_id"]    = $user["id"];
    $_SESSION["account_id"] = $user["account_id"];
    $_SESSION["role"]       = $user["role"];

    // ✅ log success + store log_id for logout update
    $log = $conn->prepare("
        INSERT INTO user_logs (user_id, account_id, name, login_at, status, ip_address, user_agent)
        VALUES (?, ?, ?, NOW(), 'Successful log in', ?, ?)
    ");
    $log->bind_param("issss", $user["id"], $user["account_id"], $fullName, $ip, $ua);
    $log->execute();
    $_SESSION["log_id"] = $conn->insert_id;

    $target = ($user["role"] === "admin")
        ? "admin/dashboard.php"
        : "cashier/dashboard.php";

    // ✅ AJAX response (so spinner + sound works reliably)
    if ($isAjax) {
        respond_json(true, "Login successful. Welcome back!", $target, "success");
    }

    // Normal (non-AJAX) fallback
    $_SESSION["toast"] = ["type" => "success", "msg" => "Login successful. Welcome back!"];
    header("Location: " . $target);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OC Brand - Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{
  --bg:#f3f4f6;
  --card:#ffffff;
  --text:#0f172a;
  --muted:#64748b;
  --line:#e5e7eb;
  --brand:#f59e0b;
  --brand2:#facc15;
  --shadow: 0 18px 50px rgba(15, 23, 42, .12);
}
*{box-sizing:border-box;font-family:Poppins,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}

body{
  margin:0;
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  background:
    radial-gradient(900px 450px at 15% 20%, rgba(245,158,11,.18), transparent 55%),
    radial-gradient(900px 450px at 85% 80%, rgba(250,204,21,.14), transparent 55%),
    var(--bg);
  padding:26px;
}

.card{
  width:min(460px, 100%);
  background:var(--card);
  border-radius:22px;
  box-shadow: var(--shadow);
  padding:30px 28px 22px;
  border:1px solid rgba(15,23,42,.06);
  position:relative;
}

.logo{
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  text-align:center;
  margin-bottom:18px;
}

.logo-box{
  width:104px;
  height:104px;
  border-radius:22px;
  overflow:hidden;
  background:#000;
  box-shadow:
    0 16px 35px rgba(15,23,42,.18),
    0 0 0 6px rgba(245,158,11,.10);
  margin-bottom:14px;
  display:grid;
  place-items:center;
}
.logo-box img{
  width:100%;
  height:100%;
  object-fit:cover;
  transform: scale(1.03);
}

.logo h1{
  margin:0;
  font-size:28px;
  color:var(--text);
  letter-spacing:.2px;
}
.logo p{
  margin:6px 0 0;
  color:var(--muted);
  font-size:13px;
}

.field{ margin-bottom:14px; }
.label{
  display:block;
  font-size:12px;
  color:var(--muted);
  margin:0 0 6px;
  font-weight:800;
}

.input{ position:relative; }
.input i{
  position:absolute;
  left:14px;
  top:50%;
  transform:translateY(-50%);
  color:#9ca3af;
}
.input input{
  width:100%;
  padding:14px 44px;
  border-radius:14px;
  border:1px solid var(--line);
  background:#f8fafc;
  font-size:15px;
  outline:none;
  transition:.2s;
}
.input input:focus{
  background:#fff;
  border-color: rgba(245,158,11,.55);
  box-shadow: 0 0 0 4px rgba(245,158,11,.16);
}
.input .toggle-pass{
  left:auto;
  right:14px;
  cursor:pointer;
  color:#9ca3af;
}
.input .toggle-pass:hover{ color:#475569; }

.caps{
  display:none;
  color:#b91c1c;
  background:#fee2e2;
  border:1px solid #fecaca;
  padding:10px 12px;
  border-radius:12px;
  font-size:12px;
  font-weight:900;
  margin-bottom:12px;
}

.btn{
  width:100%;
  padding:14px 16px;
  border:none;
  border-radius:14px;
  cursor:pointer;
  font-size:16px;
  font-weight:900;
  color:#111827;
  background: linear-gradient(135deg, var(--brand2), var(--brand));
  box-shadow: 0 12px 22px rgba(245,158,11,.20);
  transition:.2s transform, .2s filter;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  margin-top:6px;
}
.btn:hover{ transform: translateY(-1px); filter:brightness(1.02); }
.btn:active{ transform: translateY(0px); }
.btn.loading{ pointer-events:none; opacity:.92; }
.btn:disabled{ opacity:.85; cursor:not-allowed; }

.spinner{ display:none; }
.btn.loading .spinner{ display:inline-block; }

.note{
  text-align:center;
  margin-top:12px;
  font-size:12px;
  color:var(--muted);
}

/* Toast */
.toast{
  position:fixed;
  top:24px;
  right:24px;
  padding:14px 18px;
  border-radius:14px;
  font-weight:900;
  color:#fff;
  animation:slideIn .35s ease, fadeOut .35s ease 2.6s forwards;
  z-index:9999;
  box-shadow: 0 16px 40px rgba(0,0,0,.18);
}
.toast.success{background:#16a34a}
.toast.error{background:#dc2626}

@keyframes slideIn{
  from{transform:translateX(120%);opacity:0}
  to{transform:translateX(0);opacity:1}
}
@keyframes fadeOut{
  to{opacity:0;transform:translateX(120%)}
}
</style>
</head>

<body>

<?php if (isset($_SESSION["toast"])): ?>
  <div class="toast <?= $_SESSION["toast"]["type"] ?>">
    <?= htmlspecialchars($_SESSION["toast"]["msg"]) ?>
  </div>
<?php unset($_SESSION["toast"]); endif; ?>

<div class="card">

  <div class="logo">
    <div class="logo-box">
      <img src="uploads/ocb_logo.jpg" alt="OC Brand Logo">
    </div>
    <h1>OC Brand</h1>
    <p>POS & Inventory System</p>
  </div>

  <form method="POST" id="loginForm" autocomplete="off">
    <!-- ✅ AJAX flag so we can keep page alive for spinner + sound -->
    <input type="hidden" name="ajax" value="1">

    <div class="field">
      <label class="label">Account ID</label>
      <div class="input">
        <i class="fas fa-user"></i>
        <input type="text" name="account_id" placeholder="Enter your Account ID" required>
      </div>
    </div>

    <div class="caps" id="capsWarn">⚠ Caps Lock is ON</div>

    <div class="field">
      <label class="label">Password</label>
      <div class="input">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="password" placeholder="Enter your password" required>
        <i class="fas fa-eye toggle-pass" id="togglePass" title="Show/Hide Password"></i>
      </div>
    </div>

    <button type="submit" class="btn" id="loginBtn">
      Login
      <i class="fas fa-circle-notch fa-spin spinner"></i>
    </button>

    <div class="note">Authorized Personnel Only</div>
  </form>
</div>

<audio id="loginSound" preload="auto">
  <source src="https://assets.mixkit.co/sfx/preview/mixkit-positive-interface-beep-221.mp3" type="audio/mpeg">
</audio>

<script>
  // SHOW/HIDE PASSWORD
  const togglePass = document.getElementById("togglePass");
  const password = document.getElementById("password");
  togglePass.onclick = () => {
    password.type = password.type === "password" ? "text" : "password";
    togglePass.classList.toggle("fa-eye-slash");
  };

  // CAPS LOCK WARNING
  password.addEventListener("keyup", e => {
    document.getElementById("capsWarn").style.display =
      e.getModifierState("CapsLock") ? "block" : "none";
  });

  function showToast(type, msg){
    const t = document.createElement("div");
    t.className = "toast " + (type === "success" ? "success" : "error");
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(()=> t.remove(), 3500);
  }

  const loginForm  = document.getElementById("loginForm");
  const loginBtn   = document.getElementById("loginBtn");
  const loginSound = document.getElementById("loginSound");

  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // show spinner for sure
    loginBtn.classList.add("loading");
    loginBtn.disabled = true;

    try{
      const fd = new FormData(loginForm);

      const res = await fetch("home.php", {
        method: "POST",
        body: fd
      });

      const data = await res.json();

      if (data.ok) {
        // play sound while page still alive (best chance to work)
        loginSound.currentTime = 0;
        loginSound.play().catch(()=>{});

        showToast("success", data.msg);

        setTimeout(() => {
          window.location.href = data.redirect;
        }, 600);

      } else {
        showToast("error", data.msg);
        loginBtn.classList.remove("loading");
        loginBtn.disabled = false;
      }

    } catch(err){
      showToast("error", "Network error. Please try again.");
      loginBtn.classList.remove("loading");
      loginBtn.disabled = false;
    }
  });
</script>

</body>
</html>