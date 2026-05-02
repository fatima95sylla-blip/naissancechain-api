<?php

namespace App\Repositories;

use App\Models\Agent;
use App\Repositories\Contracts\BaseRepositoryInterface;

class AgentRepository extends BaseRepository implements BaseRepositoryInterface
{
    public function __construct(Agent $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?Agent
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByRole(string $role)
    {
        return $this->model->where('role', $role)->get();
    }

    public function findActifs()
    {
        return $this->model->where('actif', true)->get();
    }

    public function findByPrefecture(string $prefecture)
    {
        return $this->model->where('prefecture', $prefecture)->get();
    }

    public function findByZone(string $zone)
    {
        return $this->model->where('zone', $zone)->get();
    }

    public function searchByName(string $nom)
    {
        return $this->model->where('nom', 'like', "%{$nom}%")
                           ->orWhere('prenom', 'like', "%{$nom}%")
                           ->get();
    }

    public function withNaissances()
    {
        return $this->model->with('naissances');
    }

    public function findAdmins()
    {
        return $this->model->where('role', 'admin')->get();
    }

    public function updateLastLogin($agentId)
    {
        return $this->model->where('id', $agentId)
                          ->update(['derniere_connexion' => now()]);
    }

    public function activate($agentId)
    {
        return $this->model->where('id', $agentId)->update(['actif' => true]);
    }

    public function deactivate($agentId)
    {
        return $this->model->where('id', $agentId)->update(['actif' => false]);
    }
}
