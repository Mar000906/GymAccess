<?php
include '../db/db.php';
include '../includes/header.php';

$today = date('Y-m-d');

// 🔥 Récupérer tous les abonnements actifs
$subs = $conn->query("
    SELECT s.id, u.name AS user_name, p.name AS pack_name, s.start_date, s.end_date
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    JOIN packs p ON s.pack_id = p.id
    WHERE s.start_date <= '$today' AND s.end_date >= '$today'
    ORDER BY s.end_date DESC
");
?>

<style>
body {
    margin: 0;
    font-family: 'Inter', system-ui, sans-serif;
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

.table-wrap {
    margin-top: 28px;
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
    text-align: left;
    padding: 12px 10px;
    font-weight: 800;
    font-size: 13px;
}

tbody td {
    padding: 10px;
    font-size: 13px;
    border-bottom: 1px solid rgba(255,255,255,.08);
}

tbody tr:hover {
    background: rgba(34,197,94,.12);
}

.empty {
    text-align: center;
    padding: 18px;
    color: #94a3b8;
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-form">
        <h2 class="title">🟢 Abonnements actifs</h2>
        <div class="subtitle">Liste de tous les membres avec un abonnement en cours</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Membre</th>
                        <th>Pack</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($subs->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" class="empty">Aucun abonnement actif.</td>
                        </tr>
                    <?php endif; ?>
                    <?php while($s = $subs->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$s['id'] ?></td>
                            <td><?= htmlspecialchars($s['user_name']) ?></td>
                            <td><?= htmlspecialchars($s['pack_name']) ?></td>
                            <td><?= htmlspecialchars($s['start_date']) ?></td>
                            <td><?= htmlspecialchars($s['end_date']) ?></td>
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
