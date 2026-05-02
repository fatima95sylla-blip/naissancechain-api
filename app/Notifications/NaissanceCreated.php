<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NaissanceCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $naissance;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function constructor($naissance, $user)
    {
        $this->naissance = $naissance;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database', 'sms'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvel Acte de Naissance Enregistré - NaissanceChain')
            ->greeting('Bonjour ' . $this->user->name . ',')
            ->line('Un nouvel acte de naissance a été enregistré avec succès dans le système NaissanceChain.')
            ->line('Détails de l\'acte:')
            ->line('• Nom de l\'enfant: ' . $this->naissance->nom_enfant . ' ' . $this->naissance->prenom_enfant)
            ->line('• Date de naissance: ' . $this->naissance->date_naissance->format('d/m/Y'))
            ->line('• Lieu de naissance: ' . $this->naissance->lieu_naissance)
            ->line('• Numéro d\'acte: ' . $this->naissance->numero_acte)
            ->line('• Date d\'enregistrement: ' . $this->naissance->date_enregistrement->format('d/m/Y H:i'))
            ->line('• Enregistré par: ' . $this->naissance->agent->nom . ' ' . $this->naissance->agent->prenom)
            ->action('Voir l\'acte', url('/api/v1/naissances/' . $this->naissance->id))
            ->line('Cet acte est maintenant disponible dans la blockchain NaissanceChain pour garantir son intégrité.')
            ->salutation('Cordialement, l\'équipe NaissanceChain');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'naissance_id' => $this->naissance->id,
            'type' => 'naissance_created',
            'title' => 'Nouvel Acte de Naissance',
            'message' => 'Acte de ' . $this->naissance->nom_enfant . ' ' . $this->naissance->prenom_enfant . ' enregistré',
            'data' => [
                'naissance_numero' => $this->naissance->numero_acte,
                'enfant_nom' => $this->naissance->nom_enfant,
                'enfant_prenom' => $this->naissance->prenom_enfant,
                'date_naissance' => $this->naissance->date_naissance->format('d/m/Y'),
                'agent_nom' => $this->naissance->agent->nom,
                'agent_prenom' => $this->naissance->agent->prenom,
                'created_at' => $this->naissance->created_at->format('d/m/Y H:i'),
                'qr_code_url' => $this->naissance->qr_code_url,
                'blockchain_hash' => $this->naissance->hash_sha256,
            ]
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms($notifiable): string
    {
        return "NaissanceChain: Nouvel acte enregistre - " . 
               $this->naissance->nom_enfant . " " . $this->naissance->prenom_enfant .
               " (" . $this->naissance->numero_acte . ") - " .
               $this->naissance->date_naissance->format('d/m/Y');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'naissance_id' => $this->naissance->id,
            'user_id' => $this->user->id,
            'type' => 'naissance_created',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
