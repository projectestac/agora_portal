<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpdateRequest extends Mailable {
    use Queueable, SerializesModels;

    /**
     * Create a new message instance. Constructor uses "Constructor Property Promotion" (PHP 8).
     */
    public function __construct(private $status, private $adminComments) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: __('email.update_request_subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'email.update-request',
            with: [
                'status' => __( 'request.status_' . $this->status),
                'adminComments' => $this->adminComments,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
