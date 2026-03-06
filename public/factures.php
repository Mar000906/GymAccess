<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

// Récupérer toutes les factures avec le nom du membre
$result = $conn->query("
    SELECT f.id, f.montant, f.date_emission, f.date_echeance, f.statut, u.name
    FROM factures f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.date_emission DESC
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

.page-wrap {
    min-height: calc(100vh - 120px);
    display: grid;
    place-items: center;
    padding: 50px 16px;
}

.card-form {
    width: 100%;
    max-width: 1000px;
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

.table-wrap {
    width: 100%;
    overflow-x: auto;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 14px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,.06);
}

th {
    color: #a5b4fc;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .6px;
    background: rgba(255,255,255,.03);
}

tr:hover td {
    background: rgba(255,255,255,.04);
}

.badge {
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
}

.badge.waiting {
    background: rgba(234,179,8,.15);
    color: #fde68a;
    border: 1px solid rgba(234,179,8,.35);
}

.badge.paid {
    background: rgba(34,197,94,.15);
    color: #86efac;
    border: 1px solid rgba(34,197,94,.35);
}

.badge.late {
    background: rgba(239,68,68,.15);
    color: #fecaca;
    border: 1px solid rgba(239,68,68,.35);
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-form">
        <h2 class="title">🧾 Factures</h2>
        <div class="subtitle">Liste des factures des membres</div>

        <div class="actions">
            <a href="factures_ajouter.php" class="btn-add">+ Ajouter une facture</a>
        </div>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Membre</th>
                    <th>Montant</th>
                    <th>Date émission</th>
                    <th>Date échéance</th>
                    <th>Statut</th>
                </tr>

                <?php if($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">
                            Aucune facture pour le moment
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php
                            $statusClass = 'waiting';
                            $statusText  = '⏳ En attente';

                            if ($row['statut'] === 'reglee') {
                                $statusClass = 'paid';
                                $statusText  = '✅ Réglée';
                            } elseif ($row['statut'] === 'retard') {
                                $statusClass = 'late';
                                $statusText  = '⏰ En retard';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= number_format($row['montant'], 2) ?> DH</td>
                            <td><?= $row['date_emission'] ?></td>
                            <td><?= $row['date_echeance'] ?></td>
                            <td>
                                <span class="badge <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
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
