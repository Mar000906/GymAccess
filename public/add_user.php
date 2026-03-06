<?php
session_start();
include '../db/db.php';
include '../includes/header.php';


$success = "";
$error = "";

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $username, $password, $role);

    if($stmt->execute()){
        $success = "✅ Utilisateur ajouté avec succès !";
    } else {
        $error = "❌ Erreur lors de l’ajout : " . $conn->error;
    }
}
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

/* ---------- CONTAINER ---------- */
.page-wrap {
    min-height: calc(100vh - 120px);
    display: grid;
    place-items: center;
    padding: 50px 16px;
}

/* ---------- CARD ---------- */
.card-form {
    width: 100%;
    max-width: 520px;
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

/* ---------- TITRES ---------- */
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

/* ---------- ALERTES ---------- */
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

/* ---------- FORM ---------- */
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

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* ---------- BUTTON ---------- */
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

.btn:active { transform: translateY(0); }

.hint {
    text-align: center;
    font-size: 11px;
    color: #94a3b8;
    margin-top: 16px;
}

/* ---------- RESPONSIVE ---------- */
@media (max-width: 520px) {
    .row { grid-template-columns: 1fr; }
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">
    <div class="card-form">
        <h2 class="title">➕ Ajouter un utilisateur</h2>
        <div class="subtitle">Crée un membre ou un staff pour la salle de sport</div>

        <?php if($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="field">
                <label>Nom complet</label>
                <input type="text" name="name" placeholder="Ex: Sarah Benali" required>
            </div>

            <div class="row">
                <div class="field">
                    <label>Identifiant</label>
                    <input type="text" name="username" placeholder="ex: sarah.b" required>
                </div>

                <div class="field">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>


             






            <div class="field">
                <label>Rôle</label>
                <select name="role" required>
                    <option value="member">🏋️‍♂️ Membre</option>
                    <option value="staff">🛡️ Personnel</option>
                </select>
            </div>

            <button class="btn" type="submit" name="submit">Créer l’utilisateur</button>
        </form>

        <div class="hint">🔒 Les mots de passe sont chiffrés automatiquement</div>
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













