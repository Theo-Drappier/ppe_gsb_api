<?php

chdir('..');

session_start();

require './vendor/autoload.php';

use RedBeanPHP\R;

R::setup('mysql:host=localhost;dbname=ppe_gsb_v0', 'root', 'pwsio');
R::ext('xdispense', function ($type) {
    return R::getRedBean()->dispense($type);
});

$app = new \Slim\Slim();

//Fiche frais : GET
//Liste des fiches de frais
//Liste complète d'une table quelconque
$app->get('/find/:table', function ($table) {
    echo json_encode(R::findAll($table));
});
//enregistrement d'une liste d'un id donné
$app->get('/find/:table/:id', function ($table, $id) {
    echo json_encode(R::find($table,'id='.$id));
});
//Liste des fiches de frais validées
$app->get('/fichevalidee', function () {
    echo json_encode(R::find('ficheFrais', 'id_etat=1'));
});
//Liste des fiches de frais validées correspondant à l'idVisiteur
$app->get('/fichevalidee/:idVisiteur', function ($idVisiteur) {
    echo json_encode(R::find('ficheFrais', 'id_etat=1 AND id_visiteur=' . $idVisiteur));
});
//Liste des fiches de frais non validées
$app->get('/fichenonvalidee/', function () {
    echo json_encode(R::find('ficheFrais', 'id_etat=2'));
});
//Liste des fiches de frais non validées correspondant à l'idVisiteur
$app->get('/fichenonvalidee/:idVisiteur', function ($idVisiteur) {
    echo json_encode(R::find('ficheFrais', 'id_etat=2 AND id_visiteur=' . $idVisiteur));
});
//Fiche frais : POST
//Crée une fiche de frais
$app->get('/fichefrais/creation/fichefrais/:idVisiteur/:nbJustificatifs/:montantValide', function ($idVisiteur, $nbJustificatifs, $montantValide) {
    $fiche = R::xdispense('ficheFrais');
    $fiche->id_visiteur = $idVisiteur;
    $fiche->mois = date('Y-m-d');
    $fiche->nb_justificatifs = $nbJustificatifs;
    $fiche->montant_valide = $montantValide;
    $fiche->date_modif = '0000-01-01';
    $fiche->id_etat = 2;
    R::store($fiche);
    echo json_encode(R::getInsertID());
});
//Crée un frais forfait
$app->get('/fichefrais/creation/fraisforfait/:libelle/:montant', function ($libelle, $montant) {
    $frais = R::xdispense('fraisForfait');
    $frais->libelle = $libelle;
    $frais->montant = $montant;
    R::store($frais);
});
//Crée une nouvelle ligne de frais forfait
$app->get('/fichefrais/creation/ligneforfait/:idFicheFrais/:idFraisForfait/:quantite/:designation', function ($idFicheFrais, $idFraisForfait, $quantite, $designation) {
    $ligne = R::xdispense('ligneFraisForfait');
    $ligne->idFraisForfait = $idFraisForfait;
    $ligne->quantite = $quantite;
    $ligne->designation = $designation;
    $ligne->mois = date('Y-m-d');
    $ligne->idVisiteur = R::findOne('ficheFrais', 'id="' . $idFicheFrais . '" ')->id_visiteur;
    $ligne->idFicheFrais = $idFicheFrais;
    R::store($ligne);
});
//Crée une ligne hors forfait
$app->get('/fichefrais/creation/horsforfait/:idFicheFrais/:montant/:libelle', function ($idFicheFrais, $montant, $libelle) {
    $frais = R::xdispense('ligneFraisHorsForfait');
    $frais->idFicheFrais = $idFicheFrais;
    $frais->montant = $montant;
    $frais->libelle = $libelle;
    $frais->date = date('Y-m-d');
    R::store($frais);
});
//Compte rendus : GET
//Affichage de tous les rapports
$app->get('/rapports', function () {
    echo json_encode(R::findAll('rapportVisite'));
});
//Affichage des rapports d'un visiteur
$app->get('/rapports/:idVisiteur', function ($idVisiteur) {
    echo json_encode(R::find('rapportVisite', 'id_visiteur=' . $idVisiteur));
});
//Compte rendus : POST
//Crée un rapport
$app->get('/rapports/creation/:date/:bilan/:motif/:idVisiteur/:idPraticien', function ($date, $bilan, $motif,$idVisiteur,$idPraticien) {
    $rapport = R::xdispense('rapportVisite');
    $rapport->date = $date;
    $rapport->bilan = $bilan;
    $rapport->motif = $motif;
    $rapport->idVisiteur= $idVisiteur;
    $rapport->numPracticien = $idPraticien;
    R::store($rapport);
});
//Ajoute une relation de nombre de médicaments donnés pour un rapport
$app->get('/rapports/creation/:numRapport/:depotLegal/:quantite', function ($numRapport, $depotLegal, $quantite) {
    $offre = R::xdispense('offrir');
    $offre->numRappVisite = $numRapport;
    $offre->depotLegalMed = $depotLegal;
    $offre->quantite = $quantite;
    R::store($offre);
});


$app->run();