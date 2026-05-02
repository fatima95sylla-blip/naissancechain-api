<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create test naissance
$naissance = new \App\Models\Naissance();
$naissance->numero_unique = 'TEST-001';
$naissance->nom_enfant = 'TOURE';
$naissance->prenom_enfant = 'Ibrahim';
$naissance->date_naissance = '2026-03-20';
$naissance->lieu_naissance = 'Hôpital Donka, Conakry';
$naissance->sexe = 'M';
$naissance->nom_pere = 'TOURE';
$naissance->prenom_pere = 'Mamadou';
$naissance->nom_mere = 'DIALLO';
$naissance->prenom_mere = 'Aicha';
$naissance->adresse_parents = '123 Rue du Peuple, Conakry';
$naissance->telephone_parents = '+224625123456';
$naissance->declarant_nom = 'TOURE';
$naissance->declarant_prenom = 'Mamadou';
$naissance->declarant_lien = 'Père';
$naissance->officier_etat_civil = 'M. Barry';
$naissance->numero_acte = 'ACTE-2026-002';
$naissance->date_enregistrement = '2026-04-30';
$naissance->latitude = 9.6412;
$naissance->longitude = -13.5784;
$naissance->agent_id = 1;
$naissance->hash_sha256 = 'test_hash_123456';
$naissance->statut = 'valide';
$naissance->hors_ligne = false;
$naissance->save();

echo "Naissance created with ID: " . $naissance->id . "\n";
