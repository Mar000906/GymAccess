<?php
session_start();
include '../db/db.php';
include '../includes/header.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_mifare = trim($_POST['numero_mifare']);
    $user_id = (int) $_POST['user_id'];

    // Vérifier si l'utilisateur a déjà une carte
    $check = $conn->prepare("SELECT id FROM cartes_acces WHERE user_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $error = "❌ Cet utilisateur a déjà une carte !";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO cartes_acces (user_id, numero_mifare, date_emission, date_expiration)
            VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))
        ");
        $stmt->bind_param("is", $user_id, $numero_mifare);

        if ($stmt->execute()) {
            $success = "✅ Carte MIFARE associée avec succès !";
        } else {
            $error = "❌ Erreur : " . $conn->error;
        }
    }
}

// 🔥 Récupérer les cartes existantes
$cartes = $conn->query("
    SELECT c.id, c.numero_mifare, c.date_emission, c.date_expiration, u.name
    FROM cartes_acces c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.id DESC
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
    max-width: 900px; /* un peu plus large pour le tableau */
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

input::placeholder { color: #94a3b8; }

input:focus, select:focus {
    border-color: rgba(34,197,94,.7);
    box-shadow: 0 0 0 4px rgba(34,197,94,.15);
    transform: translateY(-1px);
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

/* 🔥 TABLE DES CARTES */
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
        <h2 class="title">💳 Associer une carte</h2>
        <div class="subtitle">Associer une carte MIFARE à un membre</div>

        <?php if($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="field">
                <label>Membre</label>
                <select name="user_id" required>
                    <option value="">Choisir un membre...</option>
                    <?php
                    $users = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
                    while($u = $users->fetch_assoc()):
                    ?>
                        <option value="<?= $u['id'] ?>">
                            <?= htmlspecialchars($u['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="field">
                <label>UID Carte MIFARE</label>
                <input type="text" name="numero_mifare" placeholder="Ex: A1B2C3D4" required>
            </div>

            <button class="btn" type="submit">Associer la carte</button>
        </form>

        <!-- 🔥 LISTE DES CARTES DISPONIBLES -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Membre</th>
                        <th>UID Carte</th>
                        <th>Date émission</th>
                        <th>Date expiration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($cartes->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" class="empty">Aucune carte enregistrée.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while($c = $cartes->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$c['id'] ?></td>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['numero_mifare']) ?></td>
                            <td><?= htmlspecialchars($c['date_emission']) ?></td>
                            <td><?= htmlspecialchars($c['date_expiration']) ?></td>
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
