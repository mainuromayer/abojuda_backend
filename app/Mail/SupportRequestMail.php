<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $senderEmail;
    public $subjectLine;
    public $describeIssue;
    public $attachmentPath;

    public function __construct($senderEmail, $subjectLine, $describeIssue, $attachmentPath = null)
    {
        $this->senderEmail = $senderEmail;
        $this->subjectLine = $subjectLine;
        $this->describeIssue = $describeIssue;
        $this->attachmentPath = $attachmentPath;
    }

    public function build()
    {
        $email = $this->subject('New Support Request: ' . $this->subjectLine)
            ->html('
                <h3>New Support Request Received</h3>
                <p><strong>From:</strong> ' . e($this->senderEmail) . '</p>
                <p><strong>Subject:</strong> ' . e($this->subjectLine) . '</p>
                <p><strong>Description:</strong></p>
                <p>' . nl2br(e($this->describeIssue)) . '</p>
            ');

        if ($this->attachmentPath) {
            $email->attachFromStorageDisk(config('app.file_system'), $this->attachmentPath);
        }

        return $email;
    }
}
