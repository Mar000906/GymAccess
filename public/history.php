<?php
include '../db/db.php';
include '../includes/header.php';

// Récupérer tout l’historique d’accès (sans login)
$result = $conn->query("
    SELECT u.name, a.access_type, a.access_time
    FROM access_log a
    JOIN users u ON u.id = a.user_id
    ORDER BY a.access_time DESC
");
?>

<style>
body {
    background: 
        linear-gradient(rgba(5,7,12,.88), rgba(5,7,12,.88)),
        url("https://images.unsplash.com/photo-1571902943202-507ec2618e8f?q=80&w=1920&auto=format&fit=crop");
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #e5e7eb;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    margin: 0;
}

/* Container */
.page-wrap {
    min-height: calc(100vh - 120px);
    display: grid;
    place-items: center;
    padding: 40px 16px;
}

.card {
    width: 100%;
    max-width: 1000px;
    background: rgba(2,6,23,.85);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 0;
    box-shadow: 0 30px 80px rgba(0,0,0,.75);
    border: 1px solid rgba(255,255,255,.08);
    overflow: hidden;
}

/* Header gym */
.card-header {
    padding: 26px 20px;
    background: linear-gradient(135deg, rgba(34,197,94,.35), rgba(239,68,68,.35));
    text-align: center;
}

.title {
    margin: 0;
    font-size: 30px;
    font-weight: 900;
    letter-spacing: .5px;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.subtitle {
    color: #e2e8f0;
    font-size: 14px;
    margin-top: 6px;
}

.card-body {
    padding: 26px;
}

/* Alerts */
.alert {
    padding: 12px 14px;
    border-radius: 14px;
    margin-bottom: 14px;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,.12);
}

.alert.error {
    background: rgba(239,68,68,.15);
    color: #fecaca;
    border-color: rgba(239,68,68,.35);
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
    background: rgba(2,6,23,.55);
    border-radius: 12px;
    overflow: hidden;
}

thead th {
    background: linear-gradient(135deg, #22c55e, #ef4444);
    color: #020617;
    padding: 12px 10px;
    font-size: 14px;
    text-align: left;
}

tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.08);
    transition: background .2s ease;
}

tbody tr:hover {
    background: rgba(34,197,94,.1);
}

tbody td {
    padding: 10px;
    font-size: 13px;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 12px;
    letter-spacing: .3px;
}

.entry {
    background: rgba(34,197,94,.15);
    color: #bbf7d0;
    border: 1px solid rgba(34,197,94,.35);
}

.exit {
    background: rgba(239,68,68,.15);
    color: #fecaca;
    border: 1px solid rgba(239,68,68,.35);
}

/* Responsive */
@media (max-width: 768px){
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>

<div class="page-wrap">
    <div class="card">
        <div class="card-header">
            <h2 class="title">🏋️‍♂️ Historique des accès</h2>
            <div class="subtitle">Toutes les entrées et sorties enregistrées au gym</div>
        </div>

        <div class="card-body">
            <?php if($result->num_rows === 0): ?>
                <div class="alert error">Aucun historique pour le moment.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Date & Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <?php if($row['access_type'] === 'entry'): ?>
                                    <span class="badge entry">Entrée</span>
                                <?php else: ?>
                                    <span class="badge exit">Sortie</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['access_time']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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
