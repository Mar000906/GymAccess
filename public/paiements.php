<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$info = "";
$error = "";

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_POST['user_id'])
    && !empty($_POST['facture_id'])
    && !empty($_POST['montant'])
) {
    $user_id = (int) $_POST['user_id'];
    $facture_id = (int) $_POST['facture_id'];
    $montant = (float) $_POST['montant'];
    $mode_paiement = $_POST['mode_paiement'] ?? 'especes';

    $conn->begin_transaction();

    try {
        // Enregistrer le paiement
        $stmt = $conn->prepare("
            INSERT INTO paiements (user_id, facture_id, montant, date_paiement)
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt->bind_param("iid", $user_id, $facture_id, $montant);
        $stmt->execute();

        // Enregistrer le mouvement caisse (ENTRÉE)
        $description = "Paiement facture #".$facture_id;
        $stmt = $conn->prepare("
            INSERT INTO caisse (type_mouvement, montant, mode_paiement, description, user_id, date_operation)
            VALUES ('entree', ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("dssi", $montant, $mode_paiement, $description, $user_id);
        $stmt->execute();

        // Total payé
        $stmt = $conn->prepare("SELECT SUM(montant) AS total FROM paiements WHERE facture_id = ?");
        $stmt->bind_param("i", $facture_id);
        $stmt->execute();
        $total_paye = $stmt->get_result()->fetch_assoc()['total'];

        // Montant facture
        $stmt = $conn->prepare("SELECT montant FROM factures WHERE id = ?");
        $stmt->bind_param("i", $facture_id);
        $stmt->execute();
        $montant_facture = $stmt->get_result()->fetch_assoc()['montant'];

        // Statut facture
        if ($total_paye >= $montant_facture) {
            $stmt = $conn->prepare("UPDATE factures SET statut = 'reglee' WHERE id = ?");
            $stmt->bind_param("i", $facture_id);
            $stmt->execute();
            $success = "✅ Paiement enregistré – Facture réglée !";
        } else {
            $info = "ℹ️ Paiement enregistré – Facture partiellement réglée.";
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "❌ Erreur lors de l’enregistrement : " . $e->getMessage();
    }
}

$user_id_selected = $_POST['user_id'] ?? '';
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
    max-width: 560px;
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
    content: '';X                                                                                                                                                     
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

.alert { padding: 12px 14px; border-radius: 14px; margin-bottom: 14px; font-weight: 600; }
.alert.success { background: rgba(34,197,94,.15); color: #bbf7d0; border: 1px solid rgba(34,197,94,.35); }
.alert.info { background: rgba(59,130,246,.15); color: #bfdbfe; border: 1px solid rgba(59,130,246,.35); }
.alert.error { background: rgba(239,68,68,.15); color: #fecaca; border: 1px solid rgba(239,68,68,.35); }

label { display:block; margin-bottom:6px; color:#c7d2fe; font-size:12px; text-transform:uppercase; }
input, select {
    width:100%; padding:12px 14px; border-radius:14px;
    border:1px solid rgba(255,255,255,.12);
    background: rgba(2,6,23,.7); color:#e5e7eb;
}
input:focus, select:focus { border-color: rgba(34,197,94,.7); box-shadow:0 0 0 4px rgba(34,197,94,.15); }

.btn {
    width:100%; margin-top:12px; padding:13px 16px;
    border-radius:999px; border:none; cursor:pointer;
    font-weight:900; letter-spacing:.6px;
    color:#020617;
    background: linear-gradient(135deg, #22c55e, #ef4444);
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-form">
        <h2 class="title">💵 Enregistrer un paiement</h2>
        <div class="subtitle">Encaissement d’un paiement membre</div>

        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        <?php if($info): ?><div class="alert info"><?= $info ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

        <form method="POST">
            <label>Membre</label>
            <select name="user_id" required onchange="this.form.submit()">
                <option value="">Sélectionner un membre...</option>
                <?php
                $users = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
                while($u = $users->fetch_assoc()):
                ?>
                    <option value="<?= $u['id'] ?>" <?= ($user_id_selected == $u['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <?php if(!empty($user_id_selected)): ?>
                <label style="margin-top:14px;">Facture à régler</label>
                <select name="facture_id" required>
                    <option value="">Choisir une facture...</option>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT id, montant, date_emission, date_echeance
                        FROM factures
                        WHERE user_id = ? AND statut = 'attente'
                    ");
                    $stmt->bind_param("i", $user_id_selected);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while($f = $res->fetch_assoc()):
                    ?>
                        <option value="<?= $f['id'] ?>">
                            Facture #<?= $f['id'] ?> – <?= $f['montant'] ?> DH (<?= $f['date_emission'] ?> → <?= $f['date_echeance'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            <?php endif; ?>

            <label style="margin-top:14px;">Montant (DH)</label>
            <input type="number" step="0.01" name="montant" required>

            <label style="margin-top:14px;">Mode de paiement</label>
            <select name="mode_paiement">
                <option value="especes">Espèces</option>
                <option value="carte">Carte bancaire</option>
                <option value="cheque">Chèque</option>
            </select>

            <button class="btn" type="submit">Enregistrer le paiement</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
