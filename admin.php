<!DOCTYPE html>
<?php 
require_once('./config.php');
require_once('./src/Planning/FormRoom.php');
require_once('./src/Planning/FormMatter.php');

// Redirection vers le login si l'usager n'est pas connecté.
if(!isOnline()) {
    header('Location: ./login');
}

// Redirection vers l'index si l'usager n'est pas un administrateur.
if($_SESSION['rang'] < 3) {
    header('Location: ./');
}

// Ajout ou suppression d'une salle
if(isset($_POST['add_room']) || isset($_POST['delete_room'])) {
    // On crée et vérifie si il n'y a aucune erreur dans le formulaire.
    $formRoom = new Planning\FormRoom($bdd, $_POST);

    if(isset($_POST['add_room'])) {
        $errorsARoom = $formRoom->checkAddRoom();
        if(empty($errorsARoom)) $formRoom->insertRoom();
    }

    if(isset($_POST['delete_room'])) {
        $errorsDRoom = $formRoom->checkDeleteRoom();
        if(empty($errorsDRoom)) $formRoom->deleteRoom();
    }
}

// Ajout d'une matière.
if (isset($_POST['add_matter'])){
    
    // On crée et vérifie si il n'y a aucune erreur dans le formulaire.
    $formMatter = new Planning\FormMatter($bdd, $_POST);
    $errorsMatter = $formMatter->checkAddMatter();

    if(empty($errorsMatter)){
        $formMatter->insertMatter();
    }
}

// Suppression d'une matière.
if(isset($_GET['removeMatterID'])){
    $form = new Planning\FormMatter($bdd, $_GET);
    $form->deleteMatter($_GET['removeMatterID']);
}
?>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration : Université d'Artois</title>
    <link type="image/x-icon" rel="shortcut icon" href="./assets/img/favicon.ico"/>
    <meta property="og:title" content="Administration : Université d'Artois">
    <meta property="og:type" content="website">
    <meta name="author" content="Carpentier Quentin & Krogulec Paul-Joseph">
    <!-- CSS -->
    <link type="text/css" rel="stylesheet" href="./assets/css/icons.min.css">
    <link type="text/css" rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <!-- HEADER -->
    <?php require_once('./views/header.php') ?>

    <!-- PAGE -->
    <div class="container">

        <h4>Administration</h4>

        <p>Gestion des salles</p>

        <div class="row">
            <div class="col-md-6">
                <div class="box-content">
                    <div class="content-title">Ajouter une salle</div>

                    <?php if(isset($_POST['add_room']) && empty($errorsARoom)) {
                        echo '<div class="alert alert-success">La salle a bien été ajouté !</div>';
                    } ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="room">Nom de la salle</label>
                            <input type="text" name="room" class="form-control <?= (isset($errorsARoom['room'])) ? 'is-invalid' : '' ?>">
                            <?php if(isset($errorsARoom['room'])) {
                                echo '<small class="invalid-feedback">' . $errorsARoom['room'] . '</small>';
                            } ?>
                        </div>
                        <input type="submit" name="add_room" value="Ajouter" class="btn btn-success">
                       
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box-content">
                    <div class="content-title">Supprimer une salle</div>
                    <?php if(isset($_POST['delete_room']) && empty($errorsDRoom)) {
                        echo '<div class="alert alert-success">La salle a bien été supprimé !</div>';
                    } ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="room">Nom de la salle</label>
                            <input type="text" name="room" class="form-control <?= (isset($errorsDRoom['room'])) ? 'is-invalid' : '' ?>">
                            <?php if(isset($errorsDRoom['room'])) {
                                echo '<small class="invalid-feedback">' . $errorsDRoom['room'] . '</small>';
                            } ?>                        
                        </div>
                        <input type="submit" name="delete_room" value="Supprimer" class="btn btn-danger">
                    </form>
                </div>
            </div>
        </div>

        <p>Gestion des matières</p>

        <div class="row">
            <div class="col-md-4">
                <div class="box-content">
                    <div class="content-title">Ajouter une matière</div>
                    <?php if(isset($_POST['add_matter']) && empty($errorsMatter)) {
                        echo '<div class="alert alert-success">La salle a bien été ajouté !</div>';
                    } ?>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label for="room">Nom de la matière</label>
                                <input type="text" name="nomM" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label for="room">Couleur</label>
                                <input type="color" name="colorM" class="form-control">
                            </div>

                            <div class="col-md-12 form-group">
                                <label for="room">Nom de la promotion </label>
                                <select name="promoM" class="form-control">
                                <?php 
                                    $sPromo = $bdd->query('SELECT * FROM Promotions '.$option.' ORDER BY PromotionID');
                                    while($aPromo = $sPromo->fetch()) {
                                        echo '<option value="'.$aPromo['PromotionID'].'"'. ((isset($_POST['promotion']) && $_POST['promotion'] == $aPromo['PromotionID']) ? ' selected' : '') .'>'.$aPromo['NomPromotion'].'</option>';
                                    } ?>    
                                </select>
                            </div>
                        </div>
                        <input type="submit" name="add_matter" value="Ajouter" class="btn btn-success">
                    </form>
                </div>

                <div class="box-content">
                    <div class="content-title">Associer une matière et un enseignant</div>
                    <?php if(isset($_POST['add_matter']) && empty($errorsMatter)) {
                        echo '<div class="alert alert-success">La salle a bien été ajouté !</div>';
                    } ?>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="room">Nom de la matière</label>
                                <select name="matiere" class="form-control">
                                    <?php
                                    $option = '';
                                    if($_SESSION['rang'] == 2) {
                                        $option = 'INNER JOIN Enseigne ON Matieres.MatiereID = Enseigne.MatiereID AND UsagerID = "'.$_SESSION['id'].'"';   
                                    }
                                    $sMatieres = $bdd->query('SELECT * FROM Matieres '.$option. ' ORDER BY NomMatiere');
                                    while($aMatieres = $sMatieres->fetch()) {
                                        echo '<option value="'.$aMatieres['MatiereID'].'">'.$aMatieres['NomMatiere'].'</option>';
                                    } ?> 
                                </select>
                            </div>

                            <div class="col-md-12 form-group">
                                <label for="room">Enseignant</label>
                                <select name="enseignant" class="form-control">
                                    <?php if($_SESSION['rang'] == 2) {
                                            $sql = 'AND UsagerID = "'.$_SESSION['id'].'"';
                                        } else {
                                            $sql = '';
                                        }
                                        $query = $bdd->query('SELECT * FROM Usagers WHERE RangID = 2 ' . $sql);
                                        while ($row = $query->fetch()){
                                            echo '<option value="' .$row['UsagerID'] .'">' . $row['Prenom'] . ' ' .  $row['Nom'] . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <input type="submit" name="add_teachMatter" value="Associer" class="btn btn-success">
                        <input type="submit" name="remove_teachMatter" value="Dissocier" class="btn btn-danger" style="float: right">
                    </form>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="box-content">
                    <?php 
                    $sMatter = $bdd->query('SELECT * FROM Matieres m INNER JOIN Promotions p ON m.PromotionID = p.PromotionID ORDER BY NomMatiere ASC');
                    while($aMatter = $sMatter->fetch()) { ?>
                        <div class="list-items d-flex flex-row align-items-center justify-content-between">
                            <div class="item-info">
                                <p class="nameM"><?= $aMatter['NomMatiere'] ?></p>
                            </div>

                            <div class="item-info">
                                <p class="promoM"><?= $aMatter['NomPromotion'] ?></p>
                            </div>

                            <div class="item-info">
                                <p class="colorM" style="background-color: <?= $aMatter['CouleurMatiere'] ?>"></p>
                            </div>
                            
                            
                            <a href="?removeMatterID=<?= $aMatter['MatiereID'] ?>" class="btn btn-danger"><i class="mdi mdi-close"></i></a>
                            
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <p>Gestion des usagers</p>
        
            
    </div>

    <!-- FOOTER -->
    <?php require_once('./views/footer.php') ?>

	<!-- JS -->
	<script type="text/javascript" src="./assets/js/jquery.min.js"></script>
</body>
</html>
