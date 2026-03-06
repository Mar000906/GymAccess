<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

// Suppression d’un log
if (isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];
    $del = $conn->prepare("DELETE FROM access_log WHERE id = ?");
    $del->bind_param("i", $delete_id);
    $del->execute();
}

// Recherche
$search = $_GET['q'] ?? "";

// Récupérer les logs (avec recherche)
$stmt = $conn->prepare("
    SELECT access_log.id, users.name, access_log.access_type, access_log.access_time 
    FROM access_log 
    JOIN users ON access_log.user_id = users.id
    WHERE users.name LIKE ?
    ORDER BY access_time DESC
");
$like = "%$search%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();
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
        url("https://images.unsplash.com/photo-1558611848-73f7eb4001a1?q=80&w=1920");
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
    padding: 40px 16px;
}

/* ---------- CARD ---------- */
.card-table {
    width: 100%;
    max-width: 1100px;
    background: rgba(2,6,23,.8);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 30px 80px rgba(0,0,0,.75);
    border: 1px solid rgba(255,255,255,.08);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(12px);
    animation: fadeIn .8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.card-table::after {
    content: '';
    position: absolute;
    inset: -40%;
    background: conic-gradient(from 180deg, transparent, rgba(34,197,94,.2), transparent 30%, rgba(239,68,68,.2), transparent 60%);
    filter: blur(50px);
    opacity: .6;
    pointer-events: none;
    z-index: -1;
}

/* ---------- TITRES ---------- */
.title {
    text-align: center;
    margin-bottom: 6px;
    font-size: 28px;
    font-weight: 900;
    letter-spacing: .5px;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.subtitle {
    text-align: center;
    color: #cbd5f5;
    font-size: 14px;
    margin-bottom: 18px;
}

/* ---------- TOOLBAR ---------- */
.toolbar {
    display: flex;
    gap: 10px;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.search {
    display: flex;
    gap: 8px;
    width: 100%;
    max-width: 360px;
}

.search input {
    flex: 1;
    padding: 12px 14px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.15);
    background: rgba(2,6,23,.7);
    color: #e5e7eb;
    outline: none;
    font-size: 14px;
    transition: .2s ease;
}

.search input:focus {
    border-color: rgba(34,197,94,.7);
    box-shadow: 0 0 0 4px rgba(34,197,94,.15);
    transform: translateY(-1px);
}

.hint {
    font-size: 12px;
    color: #94a3b8;
}

/* ---------- TABLE ---------- */
.table-wrap {
    border-radius: 14px;
    overflow: hidden;
    background: rgba(2,6,23,.5);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    background: linear-gradient(135deg, #22c55e, #ef4444);
    color: #020617;
    text-align: left;
    padding: 12px 10px;
    font-weight: 700;
    font-size: 14px;
    position: sticky;
    top: 0;
    z-index: 1;
}

tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.08);
    transition: background .2s ease;
}

tbody tr:hover {
    background: rgba(34,197,94,.12);
}

tbody td {
    padding: 10px;
    font-size: 13px;
    color: #e5e7eb;
}

.badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 12px;
    letter-spacing: .3px;
}

.entry {
    background: rgba(30,126,52,.25);
    color: #bbf7d0;
    border: 1px solid rgba(30,126,52,.45);
}

.exit {
    background: rgba(239,68,68,.25);
    color: #fecaca;
    border: 1px solid rgba(239,68,68,.45);
}

.btn-delete {
    background: rgba(239,68,68,.2);
    border: 1px solid rgba(239,68,68,.4);
    color: #fecaca;
    padding: 6px 10px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 12px;
}

.btn-delete:hover {
    background: rgba(239,68,68,.35);
}

.empty {
    text-align: center;
    padding: 20px;
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 768px){
    .table-wrap {
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-table">
        <h2 class="title">📋 Logs d’accès</h2>
        <div class="subtitle">Historique des entrées & sorties</div>

        <div class="toolbar">
            <form class="search" method="GET">
                <input type="text" name="q" placeholder="Rechercher par nom..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <div class="hint">🔄 Actualisation auto toutes les 20 secondes</div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Date / Heure</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" class="empty">Aucun log trouvé.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td>
                            <?php if($row['access_type'] === 'entry'): ?>
                                <span class="badge entry">Entrée</span>
                            <?php else: ?>
                                <span class="badge exit">Sortie</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['access_time']) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Tu es sûr de vouloir supprimer ce log ?');">
                                <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="btn-delete">🗑️ Supprimer</button>
                            </form>
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

<script>
    setInterval(() => {
        location.reload();
    }, 20000);
</script>

<?php include '../includes/footer.php'; ?>








