<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

// Assigner pack
if(isset($_POST['submit_assign'])){
    $user_id = $_POST['user_id'];
    $pack_id = $_POST['pack_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, pack_id, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $pack_id, $start_date, $end_date);

    if($stmt->execute()){
        $success = "✅ Pack assigné avec succès !";
    } else {
        $error = "❌ Erreur lors de l’assignation : " . $conn->error;
    }
}

// Ajouter pack depuis popup
if(isset($_POST['submit_pack'])){
    $name = $_POST['pack_name'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO packs (name, duration_days, price) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $name, $duration, $price);

    if($stmt->execute()){
        $success = "✅ Pack ajouté avec succès ! Recharge la page.";
    } else {
        $error = "❌ Erreur ajout pack : " . $conn->error;
    }
}

// Supprimer pack
if(isset($_GET['delete_pack'])){
    $id = (int)$_GET['delete_pack'];
    $conn->query("DELETE FROM packs WHERE id=$id");
    header("Location: assign_pack.php");
    exit;
}

$users = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
$packs = $conn->query("SELECT id, name FROM packs ORDER BY name ASC");
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
        url("https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=1920");
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

/* Wrapper */
.page-wrap{
    min-height: calc(100vh - 120px);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:50px 16px;
}

/* Card principal */
.card{
    width:100%;
    max-width:560px;
    background: rgba(2,6,23,.8);
    backdrop-filter: blur(12px);
    border-radius:22px;
    padding:30px 26px 30px;
    border:1px solid rgba(255,255,255,.08);
    box-shadow: 0 30px 80px rgba(0,0,0,.75);
    position:relative;
    overflow:hidden;
    animation: fadeIn .8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.card::after{
    content:'';
    position:absolute;
    inset:-40%;
    background: conic-gradient(from 180deg, transparent, rgba(34,197,94,.2), transparent 30%, rgba(239,68,68,.2), transparent 60%);
    filter: blur(50px);
    opacity:.6;
    pointer-events:none;
}

/* Titres */
.title{
    text-align:center;
    font-size:28px;
    font-weight:900;
    letter-spacing:.5px;
    margin:0 0 4px;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.subtitle{
    text-align:center;
    font-size:13px;
    color:#a5b4fc;
    margin-bottom:18px;
}

/* Alertes */
.alert{
    padding:12px 14px;
    border-radius:14px;
    margin-bottom:14px;
    font-weight:600;
    border:1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(6px);
}
.alert.success{ background: rgba(34,197,94,.15); color:#bbf7d0; border-color: rgba(34,197,94,.35); }
.alert.error{ background: rgba(239,68,68,.15); color:#fecaca; border-color: rgba(239,68,68,.35); }

/* Champs */
.field{ margin-bottom:14px; }
label{
    display:block;
    font-size:12px;
    color:#cbd5f5;
    margin-bottom:6px;
    letter-spacing:.3px;
}

input, select{
    width:100%;
    padding:12px 14px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.75);
    color:#fff;
    outline:none;
    transition:.2s;
}

input:focus, select:focus{
    border-color:#22c55e;
    box-shadow:0 0 0 3px rgba(34,197,94,.15);
    transform: translateY(-1px);
}

/* Dates côte à côte */
.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
    margin-top:6px;
}

/* Boutons */
.btn{
    width:100%;
    margin-top:12px;
    padding:12px;
    border-radius:999px;
    border:none;
    cursor:pointer;
    font-weight:900;
    letter-spacing:.4px;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    color:#020617;
    box-shadow:0 12px 35px rgba(0,0,0,.6);
    transition:.2s;
}
.btn:hover{
    transform: translateY(-2px) scale(1.01);
    filter: brightness(1.05);
    box-shadow:0 20px 60px rgba(0,0,0,.8);
}

.add-pack-btn{
    margin-top:8px;
    width:100%;
    background: transparent;
    border:1px dashed #22c55e;
    color:#22c55e;
    padding:10px;
    border-radius:999px;
    cursor:pointer;
    transition:.2s;
}
.add-pack-btn:hover{
    background: rgba(34,197,94,.1);
}

/* Modal */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background: rgba(0,0,0,.75);
    backdrop-filter: blur(4px);
    place-items:center;
    z-index:999;
}

.modal-content{
    background: rgba(2,6,23,.9);
    backdrop-filter: blur(12px);
    padding:24px 22px 26px;
    border-radius:18px;
    width:100%;
    max-width:420px;
    border:1px solid rgba(255,255,255,.08);
    box-shadow:0 25px 60px rgba(0,0,0,.8);
    animation: fadeIn .4s ease-out;
}

.modal-content h3{
    margin:0 0 12px;
    text-align:center;
    font-size:20px;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card">
        <h2 class="title">🏋️ Gym Access</h2>
        <div class="subtitle">Assigner un pack d’abonnement à un membre</div>

        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Utilisateur</label>
                <select name="user_id" required>
                    <option value="">— Choisir —</option>
                    <?php while($u = $users->fetch_assoc()): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="field">
                <label>Pack</label>
                <select name="pack_id" required>
                    <option value="">— Choisir —</option>
                    <?php while($p = $packs->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="button" class="add-pack-btn" onclick="openModal()">➕ Ajouter un pack</button>
            </div>

            <div class="row">
                <input type="date" name="start_date" required>
                <input type="date" name="end_date" required>
            </div>

            <button class="btn" name="submit_assign">🔥 Assigner le pack</button>
        </form>
        
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

<!-- MODAL AJOUT PACK -->
<div class="modal" id="packModal">
    <div class="modal-content">
        <h3>➕ Nouveau pack</h3>
        <form method="POST">
            <div class="field">
                <label>Nom du pack</label>
                <input type="text" name="pack_name" required>
            </div>
            <div class="field">
                <label>Durée (jours)</label>
                <input type="number" name="duration" required>
            </div>
            <div class="field">
                <label>Prix</label>
                <input type="number" step="0.01" name="price" required>
            </div>

            <button class="btn" name="submit_pack">Ajouter le pack</button>
            <button type="button" class="add-pack-btn" onclick="closeModal()">Fermer</button>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('packModal').style.display = 'grid';
}
function closeModal() {
    document.getElementById('packModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>




