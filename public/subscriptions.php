<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

// Récupérer tous les abonnements avec utilisateur et pack
$result = $conn->query("
    SELECT subscriptions.id, users.name AS user_name, packs.name AS pack_name, start_date, end_date 
    FROM subscriptions 
    JOIN users ON subscriptions.user_id = users.id
    JOIN packs ON subscriptions.pack_id = packs.id
    ORDER BY start_date DESC
");
?>

<style>
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
        url("https://images.unsplash.com/photo-1571902943202-507ec2618e8f?q=80&w=1920");
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

.page-wrap {
    min-height: calc(100vh - 120px);
    display: grid;
    place-items: center;
    padding: 50px 16px;
}

.card-table {
    width: 100%;
    max-width: 1000px;
    background: rgba(2,6,23,.75);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 30px 60px rgba(0,0,0,.7);
    border: 1px solid rgba(255,255,255,.08);
    position: relative;
    overflow: hidden;
    animation: fadeIn .8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px) scale(.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.card-table::after {
    content: '';
    position: absolute;
    inset: -40%;
    background: conic-gradient(from 180deg, transparent, rgba(34,197,94,.2), transparent 30%, rgba(239,68,68,.2), transparent 60%);
    filter: blur(50px);
    opacity: .6;
    pointer-events: none;
}

.title {
    text-align: center;
    margin-bottom: 6px;
    font-size: 30px;
    font-weight: 900;
    letter-spacing: .5px;
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

.actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 14px;
}

.btn-add {
    padding: 10px 18px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 900;
    color: #020617;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    text-decoration: none;
    box-shadow: 0 12px 25px rgba(0,0,0,.4);
    transition: transform .12s ease, filter .2s ease;
}

.btn-add:hover {
    transform: translateY(-2px) scale(1.02);
    filter: brightness(1.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(2,6,23,.6);
    border-radius: 12px;
    overflow: hidden;
}

thead {
    background: linear-gradient(135deg, #22c55e, #ef4444);
}

thead th {
    color: #020617;
    padding: 12px 10px;
    text-align: left;
    font-weight: 800;
    font-size: 14px;
}

tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.1);
    transition: all .2s ease;
}

tbody tr:hover {
    background: rgba(34,197,94,.12);
    transform: scale(1.01);
}

tbody td {
    padding: 11px 10px;
    font-size: 13px;
    color: #e5e7eb;
}

.hint {
    text-align: center;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 14px;
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-table">
        <h2 class="title">🏋️ Abonnements GymAccess</h2>
        <div class="subtitle">Gestion des packs & accès des membres</div>

        <div class="actions">
            <a href="abonnements_ajouter.php" class="btn-add">+ Ajouter un abonnement</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilisateur</th>
                    <th>Pack</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">
                            Aucun abonnement pour le moment
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                            <td><?= htmlspecialchars($row['pack_name']) ?></td>
                            <td><?= $row['start_date'] ?></td>
                            <td><?= $row['end_date'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="hint">💡 Survolez une ligne pour voir l’effet “gym glow”</div>
        
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
