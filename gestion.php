<!DOCTYPE html>
<?php 
require_once('./config.php');
require_once('./src/Planning/FormEvent.php');

// Redirection vers le login si l'usager n'est pas connecté.
if(!isOnline()) {
    header('Location: ./login');
}
// Redirection vers l'index si l'usager n'est ni un enseignant, ni un administrateur.
if($_SESSION['rang'] < 2) {
    header('Location: ./');
}

$last_search = isset($_GET['search']) ? $_GET['search'] : ' ';

// Ajout d'un cours.
if(isset($_POST['add_cours'])) {

    // On crée et vérifie si il n'y a aucune erreur dans le formulaire.
    $form = new Planning\FormEvent($bdd, $_POST);
    $errors = $form->checkAddEvent();

    // Si il n'y a aucune erreurs, on ajout le cours.
    if(empty($errors)) {
        $form->insertEvent();
    }
}

// Suppression d'un cours.
if(isset($_GET['removeEventID'])){
    $form = new Planning\FormEvent($bdd, $_GET);
    $form->deleteEvent($_GET['removeEventID']);
    header('Location: ./gestion');
}
?>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerer mes cours : Université d'Artois</title>
    <link type="image/x-icon" rel="shortcut icon" href="./assets/img/favicon.ico"/>
    <meta property="og:title" content="Gerer mes cours : Université d'Artois">
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

        <h4>Gestion des cours</h4>

        <div class="row">
            <div class="col-md-7">
                <div class="box-content">                    
                    <?php 
                    $where = 'WHERE NomMatiere LIKE \'%'. $last_search.'%\'';
                    if($_SESSION['rang'] == 2)  $where = 'WHERE UsagerID ="'. $_SESSION['id'] . '" AND NomMatiere LIKE \'%'. $last_search.'%\'';
                    $sCours = $bdd->query('SELECT * FROM Cours INNER JOIN Matieres USING(MatiereID), TypeCours USING(TypeID), Usagers USING(UsagerID), Promotions USING(PromotionID), Salles USING(SalleID) '.$where.' ORDER BY DateDebut DESC, HeureDebut DESC');
                    while($aCours = $sCours->fetch()) { ?>
                        <div class="list-items d-flex flex-row align-items-center justify-content-between">
                            <div class="item-info">
                                <p><?= $aCours['NomType'] ?> <?= $aCours['NomMatiere'] ?></p>
                                <span>Par <?= $aCours['Prenom'] ?> <?= $aCours['Nom'] ?>, en <?= $aCours['NomSalle'] ?></span>
                            </div>
                            
                            <div class="item-info">
                                <p>du <?= date('d-m-Y', $aCours['DateDebut']) ?> au <?= date('d-m-Y', $aCours['DateFin']) ?></p>
                                <span>de  <?= $aCours['HeureDebut'] ?> à <?= $aCours['HeureFin'] ?></span>
                            </div>
                        
                            <button class="btn btn-primary"><i class="mdi mdi-pencil-outline"></i></button>
                            <a href="?removeEventID=<?= $aCours['CourID'] ?>" class="btn btn-danger"><i class="mdi mdi-close"></i></a>
                                                    
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="col-md-5">    
                <div class="box-content">
                    <div class="content-title">Ajouter un cours</div>
                    <?php if(isset($errors['global'])) {
                        echo '<div class="alert alert-danger">'.$errors['global'].'</div>';
                    } else if(isset($_POST['add_cours']) && empty($errors)) {
                        echo '<div class="alert alert-success">Le cours a bien été ajouté !</div>';
                    } ?>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">Promotion</label>
                                    <select name="promotion" class="form-control" id="promo">
                                        <?php                                    
                                        $option = '';
                                        if($_SESSION['rang'] == 2) {
                                            $option = 'INNER JOIN Appartient ON Promotions.PromotionID = Appartient.PromotionID AND UsagerID = "'.$_SESSION['id'].'"';
                                        }
                                        $sPromo = $bdd->query('SELECT * FROM Promotions '.$option.' ORDER BY PromotionID');
                                        while($aPromo = $sPromo->fetch()) {
                                            echo '<option value="'.$aPromo['PromotionID'].'"'. ((isset($_POST['promotion']) && $_POST['promotion'] == $aPromo['PromotionID']) ? ' selected' : '') .'>'.$aPromo['NomPromotion'].'</option>';
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="">Matière</label>
                                    <select name="matiere" id="" class="form-control" id="matiere">
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
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="date" name="dateCour" class="form-control <?= (isset($errors['dateCour'])) ? 'is-invalid' : '' ?>" value="<?= (isset($_POST['dateCour'])) ? $_POST['dateCour'] : '' ?>">
                                    <?php if(isset($errors['dateCour'])) {
                                        echo '<small class="invalid-feedback">' . $errors['dateCour'] . '</small>';
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date">Heure de début</label>
                                    <input type="time" name="heureDebut" class="form-control <?= (isset($errors['heureDebut'])) ? 'is-invalid' : '' ?>" value="<?= (isset($_POST['heureDebut'])) ? $_POST['heureDebut'] : '' ?>">
                                    <?php if(isset($errors['heureDebut'])) {
                                        echo '<small class="invalid-feedback">' . $errors['heureDebut'] . '</small>';
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date">Heure de fin</label>
                                    <input type="time" name="heureFin" class="form-control <?= (isset($errors['heureFin'])) ? 'is-invalid' : '' ?>" value="<?= (isset($_POST['heureFin'])) ? $_POST['heureFin'] : '' ?>">
                                    <?php if(isset($errors['heureFin'])) {
                                        echo '<small class="invalid-feedback">' . $errors['heureFin'] . '</small>';
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date">Type de cours</label>
                                    <select name="type" class="form-control">
                                        <?php $query = $bdd->query('SELECT * FROM TypeCours');
                                            while ($row = $query->fetch()){
                                                echo '<option value="' . $row['TypeID'].'">' . $row['NomType'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Enseignant</label>
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
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="salle">Salle</label>
                                    <select name="salle" class="form-control">
                                        <?php $query = $bdd->query('SELECT * FROM Salles');
                                            while ($row = $query->fetch()){
                                                echo '<option value="' . $row['SalleID'].'">' . $row['NomSalle'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="add_cours" value="Programmer ce cours" class="btn btn-success">
                    </form>
                </div>


            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php require_once('./views/footer.php') ?>
    
	<!-- JS -->
	<script type="text/javascript" src="./assets/js/jquery.min.js"></script>
</body>
</html>
