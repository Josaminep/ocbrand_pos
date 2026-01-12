<?php
session_start();
require_once "db.php";

/* ======================
   HANDLE LOGIN
====================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $account_id = trim($_POST["account_id"] ?? "");
    $password   = $_POST["password"] ?? "";

    if (!$account_id || !$password) {
        $_SESSION["toast"] = [
            "type" => "error",
            "msg"  => "Please fill in all fields"
        ];
        header("Location: home.php");
        exit;
    }

    $stmt = $conn->prepare("
        SELECT id, account_id, role, password
        FROM accounts
        WHERE account_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$user = $result->fetch_assoc()) {
        $_SESSION["toast"] = [
            "type" => "error",
            "msg"  => "Account not found"
        ];
        header("Location: home.php");
        exit;
    }

    if (!password_verify($password, $user["password"])) {
        $_SESSION["toast"] = [
            "type" => "error",
            "msg"  => "Invalid password"
        ];
        header("Location: home.php");
        exit;
    }

    // ✅ LOGIN SUCCESS
    $_SESSION["user_id"]    = $user["id"];
    $_SESSION["account_id"] = $user["account_id"];
    $_SESSION["role"]       = $user["role"];

    $_SESSION["toast"] = [
        "type" => "success",
        "msg"  => "Login successful. Welcome back!"
    ];

    header("Location: " . ($user["role"] === "admin"
        ? "admin/dashboard.php"
        : "cashier/dashboard.php"
    ));
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
    --bg-dark:#0f0f12;
    --bg-light:#f4f4f4;
    --card-dark:rgba(255,255,255,.12);
    --card-light:#ffffff;
    --accent:#facc15;
    --text-dark:#ffffff;
    --text-light:#111;
}

/* RESET */
*{box-sizing:border-box;font-family:Poppins,sans-serif}

/* BODY */
body{
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#000,#1a1a1a);
    transition:.4s;
}
body.light{
    background:#e5e7eb;
}

/* LOGIN CARD */
.container{
    width:100%;
    max-width:380px;
    padding:35px 30px;
    border-radius:22px;
    background:var(--card-dark);
    backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,.15);
    box-shadow:0 30px 60px rgba(0,0,0,.55);
    text-align:center;
    color:#fff;
    position:relative;
}

body.light .container{
    background:var(--card-light);
    color:#111;
}

/* TOGGLE */
.theme-toggle{
    position:absolute;
    top:18px;
    right:18px;
    cursor:pointer;
    font-size:18px;
}

/* LOGO */
.container img{
    width:78px;
    margin-bottom:14px;
}

/* INPUT */
.input-group{
    position:relative;
    margin-bottom:18px;
}
.input-group i{
    position:absolute;
    top:50%;
    left:14px;
    transform:translateY(-50%);
    color:#aaa;
}
.input-group .toggle-pass{
    left:auto;
    right:14px;
    cursor:pointer;
}
.input-group input{
    width:100%;
    padding:14px 44px;
    border-radius:14px;
    border:1px solid #ccc;
    background:#f4f4f4;
    font-size:15px;
}

/* CAPS WARNING */
.caps{
    display:none;
    color:#ef4444;
    font-size:12px;
    margin-bottom:8px;
}

/* BUTTON */
button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:16px;
    background:linear-gradient(135deg,#facc15,#f59e0b);
    font-size:17px;
    font-weight:900;
    cursor:pointer;
}
button.loading{
    pointer-events:none;
    opacity:.8;
}

/* SPINNER */
.spinner{
    display:none;
    margin-left:8px;
}
button.loading .spinner{
    display:inline-block;
}

/* TOAST */
.toast{
    position:fixed;
    top:30px;
    right:30px;
    padding:14px 20px;
    border-radius:14px;
    font-weight:700;
    color:#fff;
    animation:slideIn .4s ease, fadeOut .4s ease 2.6s forwards;
    z-index:9999;
}
.toast.success{background:#22c55e}
.toast.error{background:#ef4444}

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

<div class="container">
    <div class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </div>

    <img src="https://cdn-icons-png.flaticon.com/512/126/126083.png">
    <h1>OC Brand</h1>
    <p>POS & Inventory System</p>

    <form method="POST" id="loginForm">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="account_id" placeholder="Account ID" required>
        </div>

        <div class="caps" id="capsWarn">⚠ Caps Lock is ON</div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <i class="fas fa-eye toggle-pass" id="togglePass"></i>
        </div>

        <button type="submit" id="loginBtn">
            Login
            <i class="fas fa-circle-notch fa-spin spinner"></i>
        </button>
    </form>

    <div style="margin-top:20px;font-size:12px;">Authorized Personnel Only</div>
</div>

<audio id="loginSound">
    <source src="https://assets.mixkit.co/sfx/preview/mixkit-positive-interface-beep-221.mp3">
</audio>

<script>
// SHOW/HIDE PASSWORD
togglePass.onclick = () => {
    password.type = password.type === "password" ? "text" : "password";
    togglePass.classList.toggle("fa-eye-slash");
};

// CAPS LOCK
password.addEventListener("keyup", e=>{
    document.getElementById("capsWarn").style.display =
        e.getModifierState("CapsLock") ? "block" : "none";
});

// LOADING
loginForm.addEventListener("submit", ()=>{
    loginBtn.classList.add("loading");
});

// THEME
const toggle = document.getElementById("themeToggle");
if(localStorage.theme==="light"){
    document.body.classList.add("light");
    toggle.innerHTML='<i class="fas fa-sun"></i>';
}
toggle.onclick = ()=>{
    document.body.classList.toggle("light");
    localStorage.theme = document.body.classList.contains("light") ? "light" : "dark";
    toggle.innerHTML = document.body.classList.contains("light")
        ? '<i class="fas fa-sun"></i>'
        : '<i class="fas fa-moon"></i>';
};

// SUCCESS SOUND
<?php if (isset($_SESSION["toast"]) && $_SESSION["toast"]["type"] === "success"): ?>
document.getElementById("loginSound").play();
<?php endif; ?>
</script>

</body>
</html>