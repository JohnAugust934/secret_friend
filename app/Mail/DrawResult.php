<?php

namespace App\Mail;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DrawResult extends Mailable
{
    use Queueable, SerializesModels;

    public $group;
    public $santa;
    public $giftee;

    /**
     * Create a new message instance.
     */
    public function __construct(Group $group, User $santa, User $giftee)
    {
        $this->group = $group;
        $this->santa = $santa;   // Quem tira (o Papai Noel)
        $this->giftee = $giftee; // Quem foi tirado (o presenteado)
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎁 Sorteio Realizado: {$this->group->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.draw-result',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
