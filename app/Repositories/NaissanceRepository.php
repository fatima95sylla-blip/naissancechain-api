<?php

namespace App\Repositories;

use App\Models\Naissance;
use App\Repositories\Contracts\BaseRepositoryInterface;

class NaissanceRepository extends BaseRepository implements BaseRepositoryInterface
{
    public function __construct(Naissance $model)
    {
        parent::__construct($model);
    }

    public function findByNumeroUnique(string $numero): ?Naissance
    {
        return $this->model->where('numero_unique', $numero)->first();
    }

    public function findByAgentId(int $agentId)
    {
        return $this->model->where('agent_id', $agentId)->get();
    }

    public function findByStatut(string $statut)
    {
        return $this->model->where('statut', $statut)->get();
    }

    public function findHorsLigne()
    {
        return $this->model->where('hors_ligne', true)->get();
    }

    public function findEnAttenteSync()
    {
        return $this->model->where('statut', 'en_attente')->get();
    }

    public function findValides()
    {
        return $this->model->where('statut', 'valide')->get();
    }

    public function findByDateRange($startDate, $endDate)
    {
        return $this->model->whereBetween('date_naissance', [$startDate, $endDate])->get();
    }

    public function searchByNomEnfant(string $nom)
    {
        return $this->model->where('nom_enfant', 'like', "%{$nom}%")
                           ->orWhere('prenom_enfant', 'like', "%{$nom}%")
                           ->get();
    }

    public function withAgent()
    {
        return $this->model->with('agent');
    }

    public function paginateByAgent($agentId, $perPage = 15)
    {
        return $this->model->where('agent_id', $agentId)
                          ->with('agent')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage);
    }
}
