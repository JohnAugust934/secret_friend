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

class DrawResult extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $santaName;

    public string $gifteeName;

    public string $groupName;

    public float $budget;

    public string $eventDate;

    public ?string $wishlist;

    public function __construct(Group $group, User $santa, User $giftee, ?string $wishlist = null)
    {
        $this->santaName  = $santa->name;
        $this->gifteeName = $giftee->name;
        $this->groupName  = $group->name;
        $this->budget     = (float) ($group->budget ?? 0);
        $this->eventDate  = (string) ($group->event_date?->format('Y-m-d') ?? now()->toDateString());
        $this->wishlist   = $wishlist;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resultado do Sorteio - '.$this->groupName,
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
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
