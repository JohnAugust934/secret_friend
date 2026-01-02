<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DrawResult extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $santaName;
    public $gifteeName;
    public $groupName;
    public $budget;
    public $eventDate;
    public $wishlist;

    /**
     * Create a new message instance.
     *
     * @param string $santaName
     * @param string $gifteeName
     * @param string $groupName
     * @param float $budget
     * @param string $eventDate
     * @param string|null $wishlist
     */
    public function __construct(
        $santaName,
        $gifteeName,
        $groupName,
        $budget,
        $eventDate,
        $wishlist = null
    ) {
        $this->santaName = $santaName;
        $this->gifteeName = $gifteeName;
        $this->groupName = $groupName;
        $this->budget = $budget;
        $this->eventDate = $eventDate;
        $this->wishlist = $wishlist;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resultado do Sorteio - ' . $this->groupName,
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
