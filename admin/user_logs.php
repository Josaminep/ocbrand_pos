<?php
session_start();
require_once "../db.php";

/* Protect page */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../home.php");
    exit;
}
if (($_SESSION["role"] ?? "") !== "admin") {
    die("Access denied");
}

$search   = trim($_GET['search'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to'] ?? '');

/*
  Query with JOIN accounts
  Supports:
  - search
  - date range
*/
$sql = "
  SELECT
    DATE_FORMAT(l.login_at, '%m-%d-%Y') AS log_date,
    DATE_FORMAT(l.login_at, '%k:%i') AS time_in,
    IF(l.logout_at IS NULL, '', DATE_FORMAT(l.logout_at, '%k:%i')) AS time_out,

    IF(l.session_seconds IS NULL, '',
      CONCAT(
        FLOOR(l.session_seconds/3600), ':',
        LPAD(FLOOR((l.session_seconds%3600)/60), 2, '0')
      )
    ) AS session_time,

    l.account_id,

    TRIM(
      COALESCE(
        NULLIF(CONCAT(a.fname, ' ', a.lname), ' '),
        NULLIF(l.name, ''),
        l.account_id
      )
    ) AS display_name,

    l.status
  FROM user_logs l
  LEFT JOIN accounts a ON a.id = l.user_id
";

$where  = [];
$params = [];
$types  = "";

/* Search filter */
if ($search !== '') {
    $where[] = "(l.account_id LIKE ? OR l.status LIKE ? OR CONCAT(IFNULL(a.fname,''),' ',IFNULL(a.lname,'')) LIKE ? OR l.name LIKE ?)";
    $like = "%{$search}%";
    array_push($params, $like, $like, $like, $like);
    $types .= "ssss";
}

/* Date range filter */
if ($dateFrom !== '') {
    $where[] = "DATE(l.login_at) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if ($dateTo !== '') {
    $where[] = "DATE(l.login_at) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY l.login_at DESC LIMIT 200";

if ($types !== "") {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $q = $stmt->get_result();
} else {
    $q = $conn->query($sql);
}

if (!$q) {
    die("Query failed: " . $conn->error);
}

/* Page info */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle   = ucfirst(str_replace('_', ' ', $currentPage));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title>User Logs</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins,sans-serif;
}

body{
    background:#f5f6fa;
    display:flex;
    color:#222;
}

.content{
    flex:1;
    margin-left:250px;
    padding:32px;
}

.card{
    background:#fff;
    border-radius:16px;
    padding:22px;
    box-shadow:0 10px 25px rgba(0,0,0,.06);
}

.page-title{
    text-align:center;
    font-size:28px;
    font-weight:800;
    margin:0 0 16px;
}

.topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    margin:10px 0 18px;
    flex-wrap:wrap;
}

.filter-form{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.search-box,
.date-box{
    padding:10px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    outline:none;
    font-size:15px;
    background:#fff;
}

.search-box{
    width:320px;
}

.date-box{
    width:170px;
}

.search-box:focus,
.date-box:focus{
    border-color:#16a34a;
    box-shadow:0 0 0 3px rgba(22,163,74,.12);
}

.btn{
    border:none;
    border-radius:10px;
    padding:12px 16px;
    font-weight:700;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
    text-decoration:none;
}

.btn-green{
    background:#16a34a;
    color:#fff;
}

.btn-gray{
    background:#6b7280;
    color:#fff;
}

table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
}

thead th{
    background:#2f2f2f;
    color:#fff;
    text-align:left;
    padding:12px 12px;
    font-weight:500;
}

tbody td{
    padding:14px 12px;
    border-bottom:1px solid #eee;
    vertical-align:middle;
    font-size:15px;
}

.badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:999px;
    font-weight:700;
    font-size:13px;
    border:1px solid #16a34a;
    color:#166534;
    background:#eafff1;
}

.t-center{ text-align:center; }
.w-date{ width:120px; }
.w-time{ width:90px; }
.w-sess{ width:130px; }
.w-id{ width:110px; }
.w-name{ width:220px; }
.w-status{ width:180px; }

@media (max-width: 900px){
    .content{
        padding:20px;
    }

    .filter-form{
        flex-direction:column;
        align-items:stretch;
        width:100%;
    }

    .search-box,
    .date-box{
        width:100%;
    }
}
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
  <div class="card">
    <h2 class="page-title">User Logs</h2>

    <div class="topbar">
      <form method="GET" class="filter-form">
        <input
          class="search-box"
          type="text"
          name="search"
          placeholder="Search logs..."
          value="<?= htmlspecialchars($search) ?>"
        />

        <input
          class="date-box"
          type="date"
          name="date_from"
          value="<?= htmlspecialchars($dateFrom) ?>"
        />

        <input
          class="date-box"
          type="date"
          name="date_to"
          value="<?= htmlspecialchars($dateTo) ?>"
        />

        <button type="submit" class="btn btn-green">
          <i class="fa-solid fa-magnifying-glass"></i> Filter
        </button>

        <a href="user_logs.php" class="btn btn-gray">
          <i class="fa-solid fa-rotate-left"></i> Reset
        </a>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th class="w-date">Date</th>
          <th class="w-time t-center">Time In</th>
          <th class="w-time t-center">Time Out</th>
          <th class="w-sess t-center">Session Time</th>
          <th class="w-id">ID</th>
          <th class="w-name">Name</th>
          <th class="w-status">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $q->fetch_assoc()):
          $timeOut = ($row["time_out"] !== "") ? $row["time_out"] : "-";
          $session = ($row["session_time"] !== "") ? $row["session_time"] : "-";
          $name    = ($row["display_name"] !== "") ? $row["display_name"] : $row["account_id"];
        ?>
        <tr>
          <td><?= htmlspecialchars($row["log_date"]) ?></td>
          <td class="t-center"><?= htmlspecialchars($row["time_in"]) ?></td>
          <td class="t-center"><?= htmlspecialchars($timeOut) ?></td>
          <td class="t-center"><?= htmlspecialchars($session) ?></td>
          <td><?= htmlspecialchars($row["account_id"]) ?></td>
          <td><?= htmlspecialchars($name) ?></td>
          <td>
            <span class="badge"><?= htmlspecialchars($row["status"]) ?></span>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </div>
</div>

</body>
</html>