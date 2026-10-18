<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ListingRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Post $post;
    public string $postTitle;
    public string $notificationTitle;
    public string $notificationMessage;
    public ?string $userName;
    public $locale;
    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Post $post,
        string $postTitle,
        string $notificationTitle,
        string $notificationMessage,
        ?string $userName = null,
        string $locale = 'en',
        ?string $reason = null
    ) {
        $this->post = $post;
        $this->postTitle = $postTitle;
        $this->notificationTitle = $notificationTitle;
        $this->notificationMessage = $notificationMessage;
        $this->userName = $userName;
        $this->locale = $locale;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->notificationTitle . ': ' . $this->postTitle;

        $categoryName = null;
        if ($this->post->category) {
            $categoryName = method_exists($this->post->category, 'getTranslation')
                ? ($this->post->category->getTranslation('name', $this->locale, false) ?: $this->post->category->name)
                : $this->post->category->name;
        }

        return $this->subject($subject)
            ->view('email.listing_rejected_mail')
            ->with([
                'post' => $this->post,
                'postTitle' => $this->postTitle,
                'title' => $this->notificationTitle,
                'notificationMessage' => $this->notificationMessage,
                'userName' => $this->userName,
                'categoryName' => $categoryName,
                'locale' => $this->locale,
                'isArabic' => ($this->locale === 'ar'),
                'reason' => $this->reason,
            ]);
    }
}
