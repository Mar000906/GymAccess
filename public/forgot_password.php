<?php
session_start();
include '../db/db.php';

$step = 1;
$message = "";
$error = "";
$user_id = null;

// Étape 1 : Vérifier utilisateur
if(isset($_POST['check_user'])){
    $username = trim($_POST['username']);

    if(empty($username)){
        $error = "Veuillez entrer votre nom d'utilisateur ou email.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR name = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1){
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $step = 2;
        } else {
            $error = "Utilisateur introuvable.";
        }

        $stmt->close();
    }
}

// Étape 2 : Changer mot de passe
if(isset($_POST['reset_password'])){
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($new_password) || empty($confirm_password)){
        $error = "Tous les champs sont obligatoires.";
        $step = 2;
    } elseif($new_password !== $confirm_password){
        $error = "Les mots de passe ne correspondent pas.";
        $step = 2;
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);

        if($stmt->execute()){
            $message = "Mot de passe modifié avec succès. Vous pouvez vous connecter.";
            $step = 3;
        } else {
            $error = "Erreur lors de la mise à jour.";
            $step = 2;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mot de passe oublié - GymAccess</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --green:#22c55e;
    --red:#ef4444;
    --glass: rgba(0,0,0,0.55);
    --text-light:#cbd5f5;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;}

body{
    display:flex;
    height:100vh;
    background:#0f172a;
    overflow:hidden;
}

/* ===== LEFT (Form) ===== */
.left{
    width:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px;
    position:relative;
    background: linear-gradient(135deg, rgba(28,20,0,.75), rgba(28,16,4,.95));
}

.left::before{
    content:'';
    position:absolute;
    width:400px;
    height:400px;
    background:var(--green);
    filter:blur(200px);
    top:-100px;
    left:-100px;
    opacity:.25;
    animation: pulse 6s ease-in-out infinite;
}

@keyframes pulse {
    0%,100%{ transform: scale(1) rotate(0deg); }
    50%{ transform: scale(1.2) rotate(45deg); }
}

.card{
    width:100%;
    max-width:400px;
    background: var(--glass);
    padding:45px;
    border-radius:22px;
    backdrop-filter: blur(18px) saturate(150%);
    box-shadow: 0 20px 50px rgba(0,0,0,.7);
    animation:fadeUp .8s ease both;
    position:relative;
}

h2{
    font-size:32px;
    font-weight:900;
    margin-bottom:25px;
    text-align:center;
    background: linear-gradient(135deg, var(--green), var(--red));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

input{
    width:100%;
    padding:14px 14px 14px 20px;
    margin-bottom:15px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,0.3);
    color:#fff;
    font-size:14px;
    transition:.3s;
}

input:focus{
    border:1px solid var(--green);
    box-shadow:0 0 15px rgba(34,197,94,.4);
    outline:none;
    transform: scale(1.02);
}

button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:30px;
    font-weight:600;
    font-size:15px;
    background:linear-gradient(135deg,var(--green),var(--red));
    color:#fff;
    cursor:pointer;
    transition:.5s;
    position:relative;
    overflow:hidden;
}

button::before{
    content:'';
    position:absolute;
    width:100%;
    height:100%;
    background:rgba(255,255,255,.2);
    top:0;
    left:-100%;
    transition:.5s;
}

button:hover::before{ left:100%; }
button:hover{ transform:translateY(-4px) scale(1.02); box-shadow:0 20px 30px rgba(0,0,0,.6); }

.alert{
    background:rgba(239,68,68,.15);
    color:#fecaca;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    font-size:14px;
    text-align:center;
}

.success{
    background:rgba(34,197,94,.15);
    color:#bbf7d0;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    font-size:14px;
    text-align:center;
}

a{
    color:var(--green);
    text-decoration:none;
    font-weight:600;
}
a:hover{ text-decoration:underline; }

/* ===== RIGHT (Image & overlay) ===== */
.right{
    width:50%;
    background: linear-gradient(rgba(0,0,0,.7), rgba(0,0,0,.7)),
                url("https://static.vecteezy.com/system/resources/thumbnails/056/157/720/small/dynamic-fitness-advertisement-showcases-unique-geolocation-pin-crafted-from-gym-weights-and-kettlebells-set-in-dark-industrial-style-gym-bright-lighting-highlights-equipment-s-details-photo.jpeg") center/cover no-repeat;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px;
}

.overlay-box{
    background: var(--glass);
    padding:45px;
    border-radius:22px;
    text-align:center;
    max-width:420px;
    backdrop-filter: blur(18px) saturate(150%);
    box-shadow:0 20px 60px rgba(0,0,0,.7);
    animation:fadeUp .8s ease both;
}

.overlay-box h1{
    font-size:28px;
    margin-bottom:15px;
    background: linear-gradient(135deg, var(--green), var(--red));
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    font-weight:700;
}

.overlay-box p{
    margin-bottom:20px;
    font-size:14px;
    color:var(--text-light);
}

.overlay-box a{
    display:inline-block;
    padding:12px 28px;
    border-radius:30px;
    background:var(--green);
    color:#fff;
    text-decoration:none;
    font-weight:600;
    transition:.3s;
}
.overlay-box a:hover{ background:#16a34a; transform:scale(1.05); }

@keyframes fadeUp { from { opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }

@media(max-width:900px){
    body{ flex-direction:column; }
    .left,.right{ width:100%; height:50%; }
}
</style>
</head>
<body>

<div class="left">
    <div class="card">
        <h2>🔐 Mot de passe oublié</h2>

        <?php if($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
            <a href="login.php">Retour à la connexion</a>
        <?php endif; ?>

        <?php if($step == 1): ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Email ou Nom" required>
                <button type="submit" name="check_user">Continuer</button>
            </form>
        <?php endif; ?>

        <?php if($step == 2): ?>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
                <input type="password" name="confirm_password" placeholder="Confirmer mot de passe" required>
                <button type="submit" name="reset_password">Changer mot de passe</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="right">
    <div class="overlay-box">
        <h1>GymAccess</h1>
        <p>Retrouvez l'accès à votre compte et gérez vos abonnements facilement.</p>
        <p>Vous n'avez pas encore de compte ?</p>
        <a href="register.php">S'inscrire maintenant</a>
    </div>
</div>

</body>
</html>