<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create test agent
$agent = new \App\Models\Agent();
$agent->nom = 'BARRY';
$agent->prenom = 'Mamadou';
$agent->email = 'barry.mamadou@example.com';
$agent->password = bcrypt('password123');
$agent->telephone = '+224620123456';
$agent->role = 'agent';
$agent->prefecture = 'Conakry';
$agent->zone = 'Dixinn';
$agent->actif = true;
$agent->save();

echo "Agent created with ID: " . $agent->id . "\n";
