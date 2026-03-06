
<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

include '../db/db.php';
include '../includes/header.php';

// Totaux
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$pack_count = $conn->query("SELECT COUNT(*) AS total FROM packs")->fetch_assoc()['total'];
$today = date('Y-m-d');
$subscription_count = $conn->query("
    SELECT COUNT(*) AS total 
    FROM subscriptions 
    WHERE start_date <= '$today' AND end_date >= '$today'
")->fetch_assoc()['total'];
$present_now = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM access_log WHERE DATE(access_time) = '$today' AND access_type = 'entry') - 
        (SELECT COUNT(*) FROM access_log WHERE DATE(access_time) = '$today' AND access_type = 'exit') 
        AS total
")->fetch_assoc()['total'];

$last_logs = $conn->query("
    SELECT users.name, access_log.access_type, access_log.access_time 
    FROM access_log 
    LEFT JOIN users ON access_log.user_id = users.id
    ORDER BY access_time DESC 
    LIMIT 5
");



$chart_data = $conn->query("
    SELECT HOUR(access_time) as h, COUNT(*) as total 
    FROM access_log 
    WHERE DATE(access_time) = '$today' AND access_type = 'entry' 
    GROUP BY HOUR(access_time)
");
$hours = [];
$counts = [];
while($row = $chart_data->fetch_assoc()){
    $hours[] = $row['h'] . "h";
    $counts[] = $row['total'];
}
?>

<style>
:root {
    --green:#22c55e;
    --red:#ef4444;
    --glass: rgb(0, 0, 0);
}

body {
    margin: 0;
    font-family: 'Inter', system-ui, sans-serif;
    color: #ffffff;
    background: linear-gradient(rgba(28, 20, 0, 0.75), rgba(28, 16, 4, 0.95)),
                url("https://static.vecteezy.com/system/resources/thumbnails/056/157/720/small/dynamic-fitness-advertisement-showcases-unique-geolocation-pin-crafted-from-gym-weights-and-kettlebells-set-in-dark-industrial-style-gym-bright-lighting-highlights-equipment-s-details-photo.jpeg") center/cover fixed;
                
}


/* ===== TOPBAR ===== */
.topbar {
     position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(18px) saturate(150%);
    background: rgba(8,8,12,.75);
    border-bottom: 1px solid rgba(255,255,255,.1);
    padding: 12px 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,.8);
}
.topbar-inner {
    display: grid;
    grid-template-columns: auto 1fr auto; /* logo | menu centré | burger */
    align-items: center;
}

.brand {
    font-size: 22px;
    font-weight: 900;
    background: linear-gradient(135deg, var(--green), var(--red));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: 1px;
}

.nav-links {
    display: grid;
    grid-template-columns: repeat(6, auto); /* 6 liens par ligne */
    gap: 8px 10px;
     justify-self: center; 
}

.nav-links a {
    color: #cbd5f5;
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 10px;
    font-size: 13px;
    transition: .3s ease, transform .2s ease;
    white-space: nowrap;
}





.nav-links a:hover {
    background: rgba(255,255,255,.15);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,.3);
}
.burger {
    display: none;
    font-size: 22px;
    cursor: pointer;
    color: #cbd5f5;
}


@media (max-width: 800px){
    .nav-links {
        display: none;
        grid-template-columns: 1fr; /* en colonne sur mobile */
        margin-top: 10px;
    }
    .nav-links.active {
        display: grid;
    }
    .burger {
        display: block;
    }
}



/* ===== CONTAINER ===== */
.dashboard-container {
    max-width: 1200px;
    margin: auto;
    padding: 30px 20px;
}

/* ===== HERO ===== */
.hero {
    display: grid;
    grid-template-columns: 1.5fr .5fr;
    gap: 16px;
    margin-bottom: 20px;
}
.hero-card {

    
    backdrop-filter: blur(14px);
    border-radius: 18px;
    padding: 12px 16px;
    min-height: 5px;
    border: 1px solid rgb(97, 73, 21);
    position: relative;
    overflow: hidden;
    animation: fadeUp .8s ease both;
    background:  rgba(0, 0, 0, 0.66);
    
}
.hero-card::after {
    content:'';
    position:absolute;
    inset:-60%;
    background: radial-gradient(circle, rgba(255,255,255,.12), transparent 60%);
    transform: rotate(25deg);
}
.hero h2 {
    margin: 0 0 6px;
    font-size: 22px;
}
.hero-card p {
    font-size: 12px;
    margin: 4px 0 0 0;
}

/* ===== CARDS ===== */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
    gap: 16px;
    margin: 25px 0;
}
.card {
    background: var(--glass);
    backdrop-filter: blur(14px);
    border-radius: 18px;
    padding: 18px;
    border: 1px solid rgba(0, 0, 0, 0.12);
    box-shadow: 0 15px 50px rgba(80, 58, 29, 0.7);
    position: relative;
    overflow: hidden;
    transform: translateY(30px) scale(.98);
    opacity: 0;
    animation: pop .9s ease forwards;
}
.card:nth-child(1){ animation-delay:.1s }
.card:nth-child(2){ animation-delay:.2s }
.card:nth-child(3){ animation-delay:.3s }
.card::before {
    content:'';
    position:absolute;
    inset:-50%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,.18), transparent);
    transform: rotate(20deg);
    transition:.6s;
}
.card:hover::before{ inset:-10%; }
.card:hover{ transform: translateY(-6px) scale(1.02); }
.card h3 { font-size: 14px; color: #cbd5f5; margin-bottom: 6px; }
.number {
    font-size: 34px;
    font-weight: 900;
    background: linear-gradient(135deg, var(--green), var(--red));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* ===== SECTIONS ===== */
.section {
    background: linear-gradient(135deg, rgba(30,30,40,0.75), rgba(20,20,30,0.85));
    backdrop-filter: blur(20px) saturate(180%);
    border-radius: 22px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid rgba(255,255,255,0.15);
    box-shadow: 0 8px 30px rgba(0,0,0,0.5), inset 0 0 12px rgba(255,255,255,0.05);
    animation: fadeUp .8s ease both;
    transition: transform .3s ease, box-shadow .3s ease, background .3s ease;
}

.section:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.6), inset 0 0 18px rgba(255,255,255,0.08);
    background: linear-gradient(135deg, rgba(35,35,50,0.8), rgba(20,20,30,0.9));
}





.logs li {
    padding: 8px 0;
    font-size: 14px;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.logs span { color: var(--green); font-weight: 700; }

/* ===== CHART ===== */

.chart-box {
    height: 250px; /* un peu plus grand pour “respirer” */
    border-radius: 16px;
    overflow: hidden;
    box-shadow: inset 0 0 10px rgb(0, 0, 0);
    background: rgba(131, 96, 68, 0.25);
}

.chart-box canvas { width: 100% !important; height: 100% !important; }

/* ===== ANIMATIONS ===== */
@keyframes pop { to { transform: translateY(0) scale(1); opacity:1; } }
@keyframes fadeUp { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }
</style>

<div class="topbar">
    <div class="topbar-inner">
        <div class="brand">🏋️ GymAccess</div>
        <div class="burger" onclick="toggleMenu()">☰</div>
        <div class="nav-links">
            <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>">🏠 Dashboard</a>
            <a href="add_user.php" class="<?= basename($_SERVER['PHP_SELF'])=='add_user.php'?'active':'' ?>">➕ Ajouter utilisateur</a>
            <a href="assign_pack.php" class="<?= basename($_SERVER['PHP_SELF'])=='assign_pack.php'?'active':'' ?>">📦 Assigner pack</a>
            <a href="subscriptions.php" class="<?= basename($_SERVER['PHP_SELF'])=='subscriptions.php'?'active':'' ?>">🟢 Abonnements</a>
            <a href="access_log.php" class="<?= basename($_SERVER['PHP_SELF'])=='access_log.php'?'active':'' ?>">📋 Logs</a>
            <a href="manage_packs.php" class="<?= basename($_SERVER['PHP_SELF'])=='manage_packs.php'?'active':'' ?>">🛠 Packs</a>
            <a href="history.php" class="<?= basename($_SERVER['PHP_SELF'])=='history.php'?'active':'' ?>">🕒 Historique</a>
            <a href="caisse.php">💰 Caisse</a>
<a href="cartes.php">💳 Cartes</a>
<a href="factures.php">📄 Factures</a>
<a href="photos.php">📸 Photos</a>
<a href="logout.php" style="color:#ef4444;font-weight:600;">🚪 Déconnexion</a>

        </div>
    </div>
</div>

<div class="dashboard-container">
    <div class="hero">
        <div class="hero-card">
            <h2>Bienvenue sur GymAccess</h2>
            <p>Tableau de bord premium de ta salle de sport.</p>
        </div>
        <div class="hero-card">
            <h2>Présents</h2>
            <p style="font-size:38px;font-weight:900;"><?= max(0, $present_now) ?></p>
        </div>
    </div>

    <div class="cards">
    <a href="users_list.php" style="text-decoration:none;color:inherit;">
        <div class="card">
            <h3>👤 Utilisateurs</h3>
            <div class="number"><?= $user_count ?></div>
        </div>
    </a>

    <a href="packs_list.php" style="text-decoration:none;color:inherit;">
        <div class="card">
            <h3>📦 Packs</h3>
            <div class="number"><?= $pack_count ?></div>
        </div>
    </a>

    <a href="subscriptions_list.php" style="text-decoration:none;color:inherit;">
        <div class="card">
            <h3>🟢 Abonnements actifs</h3>
            <div class="number"><?= $subscription_count ?></div>
        </div>
    </a>
</div>


    <div class="burger" onclick="toggleMenu()">☰</div>

    <div class="section">
        <h3>📊 Entrées aujourd’hui</h3>
        <div class="chart-box">
            <canvas id="entriesChart"></canvas>
        </div>
    </div>

    <div class="section">
        <h3>🕒 Dernières entrées / sorties</h3>
        <ul class="logs">
            <?php while($row = $last_logs->fetch_assoc()){ ?>
                <li>
                    <span><?= htmlspecialchars($row['name']) ?></span> — <?= $row['access_type'] == 'entry' ? 'Entrée' : 'Sortie' ?> à <?= $row['access_time'] ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('entriesChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($hours) ?>,
        datasets: [{
            label: 'Entrées',
            data: <?= json_encode($counts) ?>,
            tension: 0.4,
            fill: true,
            borderColor: '#000000',
            backgroundColor: 'rgba(237, 189, 32, 0.63)'
        }]
    },
    options: {
        plugins: { legend: { labels: { color: '#fff' } } },
        scales: { 
            x: { ticks: { color: '#fff' } },
            y: { ticks: { color: '#fff' } }
        }
    }
});

function toggleMenu() {
    document.querySelector('.nav-links').classList.toggle('active');
}

// Rafraîchissement auto toutes les 15s
setInterval(() => location.reload(), 15000);
</script>

<?php include '../includes/footer.php'; ?>
