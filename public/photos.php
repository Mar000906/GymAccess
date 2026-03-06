<?php 
include '../db/db.php'; 
include '../includes/header.php'; 

$message = "";

/* ============================= */
/* ===== SUPPRESSION PHOTO ===== */
/* ============================= */
if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT filename FROM photos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $photo = $result->fetch_assoc();
        $filePath = "../uploads/" . $photo['filename'];

        // Supprimer fichier physique
        if(file_exists($filePath)){
            unlink($filePath);
        }

        // Supprimer base de données
        $stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $message = "🗑 Photo supprimée avec succès !";

    } else {
        $message = "❌ Photo introuvable.";
    }
}

/* ============================= */
/* ===== UPLOAD PHOTO ===== */
/* ============================= */
if(isset($_POST['upload'])){

    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){

        $allowed = ['jpg','jpeg','png','gif'];
        $fileName = $_FILES['photo']['name'];
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileSize = $_FILES['photo']['size'];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){

            if($fileSize < 5*1024*1024){

                $newName = time().'_'.uniqid().'.'.$ext;
                $uploadPath = "../uploads/".$newName;

                if(move_uploaded_file($fileTmp, $uploadPath)){

                    $stmt = $conn->prepare("INSERT INTO photos (filename) VALUES (?)");
                    $stmt->bind_param("s", $newName);
                    $stmt->execute();

                    $message = "✅ Photo uploadée avec succès !";

                } else {
                    $message = "❌ Erreur lors du déplacement du fichier.";
                }

            } else {
                $message = "❌ Fichier trop volumineux (max 5MB).";
            }

        } else {
            $message = "❌ Format non autorisé.";
        }

    } else {
        $message = "❌ Aucun fichier sélectionné.";
    }
}
?>

<style>
body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    color: #e5e7eb;
    background: #05070c;
}

.gym-bg {
    position: fixed;
    inset: 0;
    background: linear-gradient(rgba(5,7,12,.85), rgba(5,7,12,.95)),
                url("https://images.unsplash.com/photo-1554284126-aa88f22d8b74?q=80&w=1920") no-repeat center center;
    background-size: cover;
    filter: blur(4px) brightness(0.7);
    transform: scale(1.05);
    z-index: -2;
}

.overlay-glow {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at top, rgba(34,197,94,.25), transparent 40%),
                radial-gradient(circle at bottom right, rgba(239,68,68,.25), transparent 45%);
    z-index: -1;
}

.page-wrap {
    padding: 50px 16px;
}

.card-form {
    max-width: 600px;
    margin: auto;
    padding: 40px 30px;
    border-radius: 24px;
    background: rgba(2,6,23,.85);
    backdrop-filter: blur(16px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
}

.title {
    text-align: center;
    font-size: 30px;
    font-weight: 900;
    margin-bottom: 20px;
}

input[type=file] {
    width: 100%;
    padding: 14px;
    border-radius: 16px;
    background: rgba(2,6,23,.7);
    color: #fff;
    border: 1px solid rgba(255,255,255,.12);
}

.btn {
    width: 100%;
    margin-top: 16px;
    padding: 14px;
    border-radius: 999px;
    border: none;
    font-weight: 800;
    background: linear-gradient(135deg, #22c55e, #ef4444);
    color: #fff;
    cursor: pointer;
}

.message {
    text-align: center;
    margin-bottom: 15px;
    font-weight: bold;
}

.gallery {
    margin-top: 40px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px,1fr));
    gap: 20px;
}

.photo-card {
    position: relative;
}

.photo-card img {
    width: 100%;
    border-radius: 12px;
    height: 180px;
    object-fit: cover;
}

/* Delete Button */
.delete-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(239,68,68,.9);
    border: none;
    padding: 6px 10px;
    border-radius: 20px;
    color: #fff;
    font-size: 12px;
    cursor: pointer;
    transition: .3s;
}

.delete-btn:hover {
    background: #dc2626;
}
</style>

<div class="gym-bg"></div>
<div class="overlay-glow"></div>

<div class="page-wrap">

    <div class="card-form">
        <h2 class="title">📸 Photos Membres</h2>

        <?php if($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="photo" required>
            <button class="btn" name="upload">Uploader la photo</button>
        </form>
    </div>

    <div class="gallery">
        <?php
        $result = $conn->query("SELECT * FROM photos ORDER BY id DESC");
        while($row = $result->fetch_assoc()):
        ?>
            <div class="photo-card">
                <img src="../uploads/<?= $row['filename'] ?>" alt="photo">

                <a href="?delete=<?= $row['id'] ?>" 
                   onclick="return confirm('Supprimer cette photo ?')">
                    <button class="delete-btn">🗑</button>
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <div style="margin-top:40px;">
        <a href="dashboard.php" 
           style="display:inline-block;padding:10px 18px;border-radius:12px;
           background: linear-gradient(135deg, #22c55e, #ef4444);
           color:#fff;font-weight:700;text-decoration:none;">
           ⬅ Retour au Dashboard
        </a>
    </div>

</div>

<?php include '../includes/footer.php'; ?>




