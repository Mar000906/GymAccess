<?php
session_start();
include '../db/db.php';

$success = "";
$error = "";

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if(empty($name) || empty($username) || empty($password) || empty($confirm)){
        $error = "Tous les champs sont obligatoires.";
    } elseif($password !== $confirm){
        $error = "Les mots de passe ne correspondent pas.";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0){
            $error = "Cet identifiant est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'member')");
            $stmt->bind_param("sss", $name, $username, $hash);

            if($stmt->execute()){
                $success = "Compte créé avec succès !";
            } else {
                $error = "Erreur : " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription - GymAccess</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
    linear-gradient(rgba(0,0,0,.75),rgba(0,0,0,.85)),
    url('https://images.unsplash.com/photo-1571902943202-507ec2618e8f') center/cover no-repeat;
    overflow:hidden;
}

/* Glass Card */
.card{
    width:100%;
    max-width:450px;
    padding:45px;
    border-radius:25px;
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(25px);
    border:1px solid rgba(255,255,255,0.15);
    box-shadow:0 25px 60px rgba(0,0,0,0.8);
    animation:fadeUp .8s ease;
}

.card h2{
    text-align:center;
    font-size:30px;
    font-weight:900;
    margin-bottom:25px;
    background:linear-gradient(135deg,#22c55e,#ef4444);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

/* Inputs */
input{
    width:100%;
    padding:14px;
    margin-bottom:18px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.2);
    background:rgba(0,0,0,.5);
    color:#fff;
    font-size:15px;
    transition:.3s;
}

input:focus{
    outline:none;
    border:1px solid #22c55e;
    box-shadow:0 0 10px rgba(34,197,94,.6);
}

/* Button */
button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:50px;
    font-size:16px;
    font-weight:800;
    cursor:pointer;
    background:linear-gradient(135deg,#22c55e,#ef4444);
    color:#fff;
    transition:.4s;
}

button:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 25px rgba(34,197,94,.5),
               0 10px 25px rgba(239,68,68,.5);
}

/* Alerts */
.alert{
    padding:12px;
    margin-bottom:18px;
    border-radius:12px;
    text-align:center;
    font-weight:600;
}

.success{
    background:rgba(34,197,94,.2);
    color:#4ade80;
}

.error{
    background:rgba(239,68,68,.2);
    color:#f87171;
}

/* Footer Link */
.login-link{
    text-align:center;
    margin-top:20px;
}

.login-link a{
    color:#22c55e;
    text-decoration:none;
    font-weight:600;
}

.login-link a:hover{
    text-decoration:underline;
}

/* Animation */
@keyframes fadeUp{
    from{opacity:0; transform:translateY(40px);}
    to{opacity:1; transform:translateY(0);}
}

@media(max-width:500px){
    .card{
        padding:30px;
    }
}

</style>
</head>
<body>

<div class="card">

    <h2>Créer un compte</h2>

    <?php if($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Nom complet" required>
        <input type="text" name="username" placeholder="Identifiant (username)" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="password" name="confirm" placeholder="Confirmer le mot de passe" required>

        <button type="submit" name="register">Créer le compte</button>
    </form>

    <div class="login-link">
        Déjà un compte ?
        <a href="login.php">Se connecter</a>
    </div>

</div>

</body>
</html>