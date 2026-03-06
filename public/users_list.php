<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

// 🔥 Suppression utilisateur
if(isset($_GET['delete'])){
    $id = (int) $_GET['delete'];
    if($conn->query("DELETE FROM users WHERE id = $id")){
        $success = "✅ Utilisateur supprimé avec succès !";
    } else {
        $error = "❌ Erreur lors de la suppression : " . $conn->error;
    }
}

// 🔎 Recherche
$search = $_GET['search'] ?? '';
$search_sql = "";
if($search !== ""){
    $s = $conn->real_escape_string($search);
    $search_sql = "WHERE name LIKE '%$s%' OR username LIKE '%$s%'";
}

// 🔥 Liste des utilisateurs
$users = $conn->query("
    SELECT id, name, username, role 
    FROM users 
    $search_sql
    ORDER BY id DESC
");
?>

<style>
/* ====== BACKGROUND GYM BLUR ====== */
body {
    margin: 0;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: #e5e7eb;
    background: #05070c;
}

.gym-bg {
    position: fixed;
    inset: 0;
    background:
        linear-gradient(rgba(5,7,12,.85), rgba(5,7,12,.95)),
        url("https://images.unsplash.com/photo-1554284126-aa88f22d8b74?q=80&w=1920");
    background-size: cover;
    background-position: center;
    filter: blur(4px);
    transform: scale(1.05);
    z-index: -2;
}

.overlay-glow {
    position: fixed;
    inset: 0;
    background:
        radial-gradient(circle at top, rgba(34,197,94,.25), transparent 40%),
        radial-gradient(circle at bottom right, rgba(239,68,68,.25), transparent 45%);
    z-index: -1;
}

/* ---------- CONTAINER ---------- */
.page-wrap {
    min-height: calc(100vh - 120px);
    display: grid;
    place-items: center;
    padding: 50px 16px;
}

/* ---------- CARD ---------- */
.card-form {
    width: 100%;
    max-width: 900px;
    padding: 30px 26px 26px;
    border-radius: 22px;
    background: rgba(2,6,23,.75);
    border: 1px solid rgba(255,255,255,.08);
    backdrop-filter: blur(14px);
    box-shadow: 0 30px 80px rgba(0,0,0,.75);
    position: relative;
    overflow: hidden;
    animation: fadeIn .8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px) scale(.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.card-form::after {
    content: '';
    position: absolute;
    inset: -40%;
    background: conic-gradient(from 180deg, transparent, rgba(34,197,94,.2), transparent 30%, rgba(239,68,68,.2), transparent 60%);
    filter: blur(50px);
    opacity: .6;
    pointer-events: none;
}

/* ---------- TITRES ---------- */
.title {
    text-align: center;
    margin-bottom: 6px;
    font-size: 30px;
    font-weight: 900;
    letter-spacing: .6px;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.subtitle {
    text-align: center;
    color: #a5b4fc;
    font-size: 13px;
    margin-bottom: 22px;
}

/* ---------- ALERTES ---------- */
.alert {
    padding: 12px 14px;
    border-radius: 14px;
    margin-bottom: 14px;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(6px);
}

.alert.success {
    background: rgba(34,197,94,.15);
    color: #bbf7d0;
    border-color: rgba(34,197,94,.35);
}

.alert.error {
    background: rgba(239,68,68,.15);
    color: #fecaca;
    border-color: rgba(239,68,68,.35);
}

/* ---------- SEARCH ---------- */
.search-box {
    margin-bottom: 16px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
}

.search-box input {
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.7);
    color: #e5e7eb;
}

.search-box button {
    padding: 12px 18px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 800;
    color: #020617;
    background: linear-gradient(135deg, #22c55e, #ef4444);
}

/* ---------- TABLE ---------- */
.table-wrap {
    margin-top: 10px;
    border-radius: 14px;
    overflow: hidden;
    background: rgba(2,6,23,.5);
    border: 1px solid rgba(255,255,255,.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    background: linear-gradient(135deg, #22c55e, #ef4444);
    color: #020617;
    padding: 12px 10px;
    text-align: left;
    font-size: 13px;
    font-weight: 900;
}

tbody td {
    padding: 10px;
    font-size: 13px;
    border-bottom: 1px solid rgba(255,255,255,.08);
}

tbody tr:hover {
    background: rgba(34,197,94,.12);
}

.badge {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}

.badge.member {
    background: rgba(34,197,94,.15);
    color: #bbf7d0;
}

.badge.staff {
    background: rgba(239,68,68,.15);
    color: #fecaca;
}

.delete-btn {
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(239,68,68,.2);
    color: #fecaca;
    text-decoration: none;
    font-weight: 800;
    font-size: 11px;
}

.delete-btn:hover {
    background: rgba(239,68,68,.35);
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-form">
        <h2 class="title">👤 Liste des utilisateurs</h2>
        <div class="subtitle">Tous les membres & personnels inscrits</div>

        <?php if($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <!-- 🔎 Recherche -->
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="🔎 Rechercher par nom ou identifiant..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Rechercher</button>
        </form>

        <!-- 📋 Tableau -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Identifiant</th>
                        <th>Rôle</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($users->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;color:#94a3b8;padding:16px;">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] ?>">
                                <?= $u['role'] == 'member' ? '🏋️‍♂️ Membre' : '🛡️ Staff' ?>
                            </span>
                        </td>
                        <td>
                            <a class="delete-btn" href="?delete=<?= (int)$u['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">
                                ❌ Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <div style="margin-bottom:16px; text-align:left;">
    <a href="dashboard.php" 
       style="
           display:inline-block;
           padding:10px 18px;
           border-radius:12px;
           background: linear-gradient(135deg, #22c55e, #ef4444);
           color:#fff;
           font-weight:700;
           text-decoration:none;
           transition:0.2s;
       "
       onmouseover="this.style.opacity='0.8';"
       onmouseout="this.style.opacity='1';"
    >
        ⬅ Retour au Dashboard
    </a>
</div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
