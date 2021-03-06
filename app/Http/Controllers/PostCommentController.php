<?php

namespace App\Http\Controllers;

use App\Events\CommentPosted as EventsCommentPosted;
use App\Http\Requests\StoreComment;
use App\Mail\CommentPosted;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Mail;
// use App\Mail\CommentPostedMarkdown;
// use App\Jobs\NotifyUsersPostWasCommented;
// use App\Jobs\ThrottledMail;
use App\Http\Resources\Comment as CommentResource;

class PostCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['store']);
    }
    public function index(BlogPost $post)
    {
        return CommentResource::collection($post->comments()->with('user')->get());
        return $post->comments()->with('user')->get();
    }
    public function store(BlogPost $post, StoreComment $request)
    {
        //Comment::create()
        $comment = $post->comments()->create([
            'content' => $request->input('content'),
            'user_id' => $request->user()->id
        ]);
        event(new EventsCommentPosted($comment));
        // Mail::to($post->user)->send(
        //     new CommentPosted($comment)
        //     // new CommentPostedMarkdown($comment)
        // );
        // Mail::to($post->user)->queue(
        //     new CommentPosted($comment)
        // );

        // $when = now()->addMinutes(1);

        // Mail::to($post->user)->later(
        //     $when,
        //     new CommentPosted($comment)
        //     // new CommentPostedMarkdown($comment)
        // );
        // // ThrottledMail::dispatch(new CommentPostedMarkdown($comment), $post->user);
        // NotifyUsersPostWasCommented::dispatch($comment);
        // move to NotifyUsersAboutComment


        return redirect()->back()->withStatus('Comment was created!');
    }
}
