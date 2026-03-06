<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

// Ajouter un pack
if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO packs (name, duration_days, price) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $name, $duration, $price);

    if($stmt->execute()){
        $success = "✅ Pack ajouté avec succès !";
    } else {
        $error = "❌ Erreur lors de l’ajout : " . $conn->error;
    }
}

$packs = $conn->query("SELECT * FROM packs ORDER BY id DESC");
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
    max-width: 1100px;
    background: rgba(2,6,23,.85);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 0;
    box-shadow: 0 30px 80px rgba(0,0,0,.75);
    border: 1px solid rgba(255,255,255,.08);
    overflow: hidden;
    position: relative;
}

/* Header gym */
.card-header {
    padding: 28px 20px;
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
    padding: 28px;
}

/* Alerts */
.alert {
    padding: 12px 14px;
    border-radius: 14px;
    margin-bottom: 14px;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,.12);
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

/* Form */
.field { margin-bottom: 14px; }

label {
    display: block;
    margin-bottom: 6px;
    color: #cbd5f5;
    font-size: 13px;
}

input {
    width: 100%;
    padding: 13px 14px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.15);
    background: rgba(2,6,23,.7);
    color: #e5e7eb;
    outline: none;
    font-size: 14px;
}

input:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 4px rgba(34,197,94,.15);
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.btn {
    width: 100%;
    margin-top: 12px;
    padding: 14px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 900;
    letter-spacing: .4px;
    color: #020617;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    box-shadow: 0 15px 40px rgba(0,0,0,.6);
    transition: .2s ease;
}

.btn:hover {
    transform: translateY(-2px) scale(1.01);
    filter: brightness(1.1);
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 24px;
    background: rgba(2,6,23,.5);
    border-radius: 12px;
    overflow: hidden;
}

thead th {
    background: linear-gradient(135deg, #22c55e, #ef4444);
    color: #020617;
    padding: 12px 10px;
    font-size: 14px;
}

tbody tr:hover {
    background: rgba(34,197,94,.1);
}

tbody td {
    padding: 10px;
    font-size: 13px;
}

/* Responsive */
@media (max-width: 768px){
    .row { grid-template-columns: 1fr; }
    table { display: block; overflow-x: auto; white-space: nowrap; }
}
</style>

<div class="page-wrap">
    <div class="card">
        <div class="card-header">
            <h2 class="title">🏋️‍♂️ Gestion des Packs</h2>
            <div class="subtitle">Crée et gère les abonnements de ton gym</div>
        </div>

        <div class="card-body">
            <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Nom du pack</label>
                    <input type="text" name="name" placeholder="Ex: Pack Gold" required>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Durée (jours)</label>
                        <input type="number" name="duration" placeholder="Ex: 30" required>
                    </div>
                    <div class="field">
                        <label>Prix</label>
                        <input type="number" step="0.01" name="price" placeholder="Ex: 199.99" required>
                    </div>
                </div>

                <button class="btn" type="submit" name="submit">🔥 Ajouter le pack</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Durée</th>
                        <th>Prix</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $packs->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= (int)$row['duration_days'] ?> jours</td>
                            <td><?= htmlspecialchars($row['price']) ?> DH</td>
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
