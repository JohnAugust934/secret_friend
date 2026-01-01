<?php

namespace App\Mail;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- Importante
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Adicionamos "implements ShouldQueue" aqui
class DrawResult extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Group $group,
        public User $santa,
        public User $giftee,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎁 O Sorteio foi realizado! Veja quem você tirou no {$this->group->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.draw-result',
            with: [
                'santaName' => $this->santa->name,
                'gifteeName' => $this->giftee->name,
                'groupName' => $this->group->name,
                'budget' => $this->group->budget,
                'eventDate' => $this->group->event_date->format('d/m/Y'),
                'groupLink' => route('groups.show', $this->group->id),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
