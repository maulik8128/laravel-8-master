<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommentPosted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $comment;

    /**
     * Create a new message instance.
     *php artisan make:mail CommentPosted
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Testing Mail Comment was posted on your {$this->comment->commentable->title} blog post";
        return $this->subject($subject)
        ->view('emails.posts.commented');
    }
}
