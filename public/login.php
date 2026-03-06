<?php
session_start();
include '../db/db.php';

$error = "";

if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)){
        $error = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, username, password, role FROM users WHERE username = ? OR name = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1){
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])){
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Utilisateur introuvable.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion - GymAccess</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --green:#22c55e;
    --red:#ef4444;
    --glass: rgba(0,0,0,0.55);
    --text-light:#cbd5f5;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter', system-ui, sans-serif;
}

body{
    display:flex;
    height:100vh;
    overflow:hidden;
    background:#0f172a;
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
    overflow:hidden;
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

.login-box{
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

.login-box h2{
    font-size:32px;
    font-weight:900;
    margin-bottom:25px;
    text-align:center;
    background: linear-gradient(135deg, var(--green), var(--red));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: 1px;
}

.input-group{
    position:relative;
    margin-bottom:18px;
}

.input-group input{
    width:100%;
    padding:14px 14px 14px 45px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,0.3);
    color:#fff;
    font-size:14px;
    transition:.4s;
}

.input-group input:focus{
    border:1px solid var(--green);
    box-shadow:0 0 15px rgba(34,197,94,.4);
    outline:none;
    transform: scale(1.02);
}

.input-group span{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    font-size:16px;
    color:#94a3b8;
}

.forgot{
    text-align:right;
    margin-bottom:20px;
}

.forgot a{
    color:var(--green);
    text-decoration:none;
    font-size:13px;
}

.forgot a:hover{
    text-decoration:underline;
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

button:hover::before{
    left:100%;
}

button:hover{
    transform:translateY(-4px) scale(1.02);
    box-shadow:0 20px 30px rgba(0,0,0,.6);
}

.alert{
    background:rgba(239,68,68,.15);
    color:#fecaca;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    font-size:14px;
    text-align:center;
}

/* ===== RIGHT (Image & overlay) ===== */
.right{
    width:50%;
    background: linear-gradient(rgba(0,0,0,.7), rgba(0,0,0,.7)),
                url("https://media.istockphoto.com/id/1211886793/fr/photo/int%C3%A9rieur-moderne-de-gymnastique-avec-l%C3%A9quipement-de-sport-et-de-forme-physique-int%C3%A9rieur-de.jpg?s=612x612&w=0&k=20&c=WtEn645eeYQpuwjJG0gIClyyYAwh32QytsFJbPnTXpM=") center/cover no-repeat;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px;
    position:relative;
    overflow:hidden;
}

.right::before{
    content:'';
    position:absolute;
    inset:0;
    background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.05), transparent 70%);
    animation: swirl 8s linear infinite;
}

@keyframes swirl {
    0%{ transform: rotate(0deg); }
    100%{ transform: rotate(360deg); }
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

.overlay-box a:hover{
    background:#16a34a;
    transform:scale(1.05);
}

/* ===== ANIMATIONS ===== */
@keyframes fadeUp { 
    from { opacity:0; transform:translateY(20px);} 
    to {opacity:1; transform:translateY(0);} 
}

/* ===== RESPONSIVE ===== */
@media(max-width:900px){
    body{
        flex-direction:column;
    }
    .left,.right{
        width:100%;
        height:50%;
    }
}
</style>
</head>
<body>

<div class="left">
    <div class="login-box">
        <h2>Connexion</h2>

        <?php if($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <span>👤</span>
                <input type="text" name="username" placeholder="Email ou Nom" required>
            </div>

            <div class="input-group">
                <span>🔒</span>
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>

            <div class="forgot">
                <a href="forgot_password.php">Mot de passe oublié ?</a>
            </div>

            <button type="submit" name="login">Se connecter</button>
        </form>
    </div>
</div>

<div class="right">
    <div class="overlay-box">
        <h1>Bienvenue sur GymAccess</h1>
        <p>Accédez à votre espace personnel et gérez vos abonnements facilement.</p>
        <p>Vous n'avez pas encore de compte ?</p>
        <a href="register.php">S'inscrire maintenant</a>
    </div>
</div>

</body>
</html>