<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int) $_POST['user_id'];
    $montant = floatval($_POST['montant']);
    $date_emission = $_POST['date_emission'];
    $date_echeance = $_POST['date_echeance'];
    $statut = 'attente';

    $stmt = $conn->prepare("
        INSERT INTO factures (user_id, montant, date_emission, date_echeance, statut)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idsss", $user_id, $montant, $date_emission, $date_echeance, $statut);

    if ($stmt->execute()) {
        $success = "✅ Facture ajoutée avec succès !";
    } else {
        $error = "❌ Erreur : " . $conn->error;
    }
}
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
    margin-bottom: 22px;
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

input, select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.7);
    color: #e5e7eb;
    outline: none;
    transition: .2s ease;
}

input:focus, select:focus {
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
        <h2 class="title">🧾 Ajouter une facture</h2>
        <div class="subtitle">Créer une facture pour un membre</div>

        <?php if($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Membre</label>
                <select name="user_id" required>
                    <option value="">Choisir un membre...</option>
                    <?php
                    $users = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
                    while($u = $users->fetch_assoc()):
                    ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="row">
                <div class="field">
                    <label>Montant (DH)</label>
                    <input type="number" step="0.01" name="montant" required>
                </div>

                <div class="field">
                    <label>Date d’émission</label>
                    <input type="date" name="date_emission" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="field">
                <label>Date d’échéance</label>
                <input type="date" name="date_echeance" required>
            </div>

            <button class="btn" type="submit">Créer la facture</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
