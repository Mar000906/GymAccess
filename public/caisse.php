<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_mouvement = $_POST['type_mouvement'];
    $montant = floatval($_POST['montant']);
    $mode_paiement = $_POST['mode_paiement'] ?? 'especes';
    $description = $_POST['description'] ?? '';
    $adherent_id = !empty($_POST['adherent_id']) ? $_POST['adherent_id'] : null;

    $stmt = $conn->prepare("
        INSERT INTO caisse 
        (type_mouvement, montant, mode_paiement, description, adherent_id, date_operation)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("sdssi", $type_mouvement, $montant, $mode_paiement, $description, $adherent_id);

    if ($stmt->execute()) {
        $success = "✅ Mouvement de caisse enregistré avec succès !";
    } else {
        $error = "❌ Erreur : " . $conn->error;
    }
}

// 🔥 Calcul du total dans la caisse
$total_entrees = $conn->query("SELECT COALESCE(SUM(montant),0) AS total FROM caisse WHERE type_mouvement = 'entree'")->fetch_assoc()['total'];
$total_sorties = $conn->query("SELECT COALESCE(SUM(montant),0) AS total FROM caisse WHERE type_mouvement = 'sortie'")->fetch_assoc()['total'];
$total_caisse = $total_entrees - $total_sorties;
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
    max-width: 600px;
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
    margin-bottom: 10px;
}

/* 🔥 TOTAL CAISSE */
.total-box {
    background: rgba(2,6,23,.85);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 18px;
    text-align: center;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.03);
}

.total-box .label {
    font-size: 12px;
    letter-spacing: .5px;
    color: #a5b4fc;
    margin-bottom: 4px;
}

.total-box .amount {
    font-size: 26px;
    font-weight: 900;
    color: #22c55e;
}

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

.field { margin-bottom: 14px; }

label {
    display: block;
    margin-bottom: 6px;
    color: #c7d2fe;
    font-size: 12px;
    letter-spacing: .5px;
    text-transform: uppercase;
}

input, select, textarea {
    width: 100%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.7);
    color: #e5e7eb;
    outline: none;
    transition: .2s ease;
}

textarea { resize: vertical; min-height: 80px; }

input:focus, select:focus, textarea:focus {
    border-color: rgba(34,197,94,.7);
    box-shadow: 0 0 0 4px rgba(34,197,94,.15);
    transform: translateY(-1px);
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.btn {
    width: 100%;
    margin-top: 12px;
    padding: 13px 16px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 900;
    letter-spacing: .6px;
    color: #020617;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    box-shadow: 0 12px 35px rgba(0,0,0,.6);
    transition: transform .12s ease, box-shadow .2s ease, filter .2s ease;
}

.btn:hover {
    transform: translateY(-2px) scale(1.01);
    filter: brightness(1.1);
    box-shadow: 0 20px 60px rgba(0,0,0,.8);
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-form">
        <h2 class="title">💰 Caisse</h2>
        <div class="subtitle">Ajouter un mouvement de caisse (entrée / sortie)</div>

        <!-- 🔥 AFFICHAGE TOTAL CAISSE -->
        <div class="total-box">
            <div class="label">Total actuel dans la caisse</div>
            <div class="amount"><?= number_format($total_caisse, 2, '.', ' ') ?> DH</div>
        </div>

        <?php if($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="field">
                    <label>Type de mouvement</label>
                    <select name="type_mouvement" required>
                        <option value="entree">➕ Entrée</option>
                        <option value="sortie">➖ Sortie</option>
                    </select>
                </div>

                <div class="field">
                    <label>Montant (DH)</label>
                    <input type="number" step="0.01" name="montant" required>
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label>Mode de paiement</label>
                    <select name="mode_paiement">
                        <option value="especes">💵 Espèces</option>
                        <option value="carte">💳 Carte</option>
                        <option value="cheque">📄 Chèque</option>
                    </select>
                </div>

                <div class="field">
                    <label>Adhérent (optionnel)</label>
                    <select name="adherent_id">
                        <option value="">-- Aucun --</option>
                        <?php
                        $users = $conn->query("SELECT id, name FROM users");
                        while($u = $users->fetch_assoc()){
                            echo "<option value='{$u['id']}'>{$u['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>Description</label>
                <textarea name="description" placeholder="Ex: Abonnement mensuel..."></textarea>
            </div>

            <button class="btn" type="submit">Enregistrer le mouvement</button>
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

<?php include '../includes/footer.php'; ?>





