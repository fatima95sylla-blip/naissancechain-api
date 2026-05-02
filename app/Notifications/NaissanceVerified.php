<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NaissanceVerified extends Notification implements ShouldQueue
{
    use Queueable;

    public $naissance;
    public $user;
    public $verificationResult;

    /**
     * Create a new notification instance.
     */
    public function constructor($naissance, $user, $verificationResult = null)
    {
        $this->naissance = $naissance;
        $this->user = $user;
        $this->verificationResult = $verificationResult;
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
        $isVerified = $this->verificationResult && $this->verificationResult['verified'] ?? true;
        
        $mail = (new MailMessage)
            ->subject($isVerified ? 'Acte de Naissance Vérifié - NaissanceChain' : 'Alerte Intégrité Acte - NaissanceChain')
            ->greeting('Bonjour ' . $this->user->name . ',');

        if ($isVerified) {
            $mail->line('L\'acte de naissance suivant a été vérifié avec succès sur la blockchain NaissanceChain.')
                ->line('Détails de l\'acte vérifié:')
                ->line('• Nom de l\'enfant: ' . $this->naissance->nom_enfant . ' ' . $this->naissance->prenom_enfant)
                ->line('• Date de naissance: ' . $this->naissance->date_naissance->format('d/m/Y'))
                ->line('• Numéro d\'acte: ' . $this->naissance->numero_acte)
                ->line('• Hash blockchain: ' . substr($this->naissance->hash_sha256, 0, 20) . '...')
                ->line('• Date de vérification: ' . now()->format('d/m/Y H:i'))
                ->line('✅ L\'intégrité de cet acte est garantie par la blockchain NaissanceChain.')
                ->action('Voir l\'acte', url('/api/v1/naissances/' . $this->naissance->id));
        } else {
            $mail->line('⚠️ Une anomalie a été détectée lors de la vérification de l\'acte de naissance suivant:')
                ->line('• Nom de l\'enfant: ' . $this->naissance->nom_enfant . ' ' . $this->naissance->prenom_enfant)
                ->line('• Numéro d\'acte: ' . $this->naissance->numero_acte)
                ->line('• Statut: ' . ($this->verificationResult['message'] ?? 'Intégrité compromise'))
                ->line('Merci d\'investiguer cette anomalie dès que possible.')
                ->action('Investiguer l\'acte', url('/api/v1/naissances/' . $this->naissance->id));
        }

        return $mail->salutation('Cordialement, l\'équipe NaissanceChain');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        $isVerified = $this->verificationResult && $this->verificationResult['verified'] ?? true;
        
        return [
            'naissance_id' => $this->naissance->id,
            'type' => 'naissance_verified',
            'title' => $isVerified ? 'Acte Vérifié' : 'Alerte Intégrité',
            'message' => 'Acte ' . ($isVerified ? 'vérifié' : 'altéré') . ': ' . 
                        $this->naissance->nom_enfant . ' ' . $this->naissance->prenom_enfant,
            'data' => [
                'naissance_numero' => $this->naissance->numero_acte,
                'enfant_nom' => $this->naissance->nom_enfant,
                'enfant_prenom' => $this->naissance->prenom_enfant,
                'verified' => $isVerified,
                'verification_message' => $this->verificationResult['message'] ?? null,
                'blockchain_hash' => $this->naissance->hash_sha256,
                'verified_at' => now()->format('d/m/Y H:i'),
                'agent_nom' => $this->naissance->agent->nom,
                'agent_prenom' => $this->naissance->agent->prenom,
            ]
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms($notifiable): string
    {
        $isVerified = $this->verificationResult && $this->verificationResult['verified'] ?? true;
        
        $status = $isVerified ? 'VERIFIE' : 'ALERTE';
        
        return "NaissanceChain: Acte " . $status . " - " . 
               $this->naissance->nom_enfant . " " . $this->naissance->prenom_enfant .
               " (" . $this->naissance->numero_acte . ") - " .
               now()->format('d/m H:i');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'naissance_id' => $this->naissance->id,
            'user_id' => $this->user->id,
            'type' => 'naissance_verified',
            'verification_result' => $this->verificationResult,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
