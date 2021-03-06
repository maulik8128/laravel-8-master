<?php

namespace App\Http\Controllers;

use App\Contracts\CounterContract;
use App\Events\BlogPostPosted;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use App\Http\Requests\StorePost;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;
use App\Facedes\CounterFacede;

// use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    // private $counter;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function __construct(CounterContract $counter)
    // {
    //     // $this->counter = $counter;
    // }
    public function index()
    {
        // DB::connection()->enableQueryLog();

        // // $posts = BlogPost::all();
        // $posts = BlogPost::with('comments')->get();


        // foreach ($posts as $post) {
        //     foreach($post->comments as $comment ){
        //         echo $comment->content;
        //     }
        // }

        // dd(DB::getQueryLog());

        // return view('posts.index', ['posts' => BlogPost::orderBy('created_at', 'desc')->take(5)->get()]);
        // return view('posts.index', ['posts' => BlogPost::all()]);

        /// comments_count
        // return view('posts.index', ['posts' => BlogPost::withCount('comments')->orderBy('created_at','desc')->get()]);
            // add Global Scope
        // return view('posts.index',
        // [
        //     'posts' => BlogPost::latest()->withCount('comments')->with('user')->get(),
        //     'mostCommented' => BlogPost::mostCommented()->take(5)->get(),
        //     'mostActive' => User::withMostBlogPosts()->take(5)->get(),
        //     'mostActiveLastMonth' => User::withMostBlogPostsLastMonth()->take(5)->get(),
        // ]);

        // catch data

        // $mostCommented=Cache::remember('blog-post-commented', now()->addSecond(10), function () {
        //     return  BlogPost::mostCommented()->take(5)->get();
        // });
        // $mostActive=Cache::remember('user-most-active', now()->addSecond(10), function () {
        //     return  User::withMostBlogPosts()->take(5)->get();
        // });
        // $mostActiveLastMonth=Cache::remember('users-most-active-last-month', now()->addSecond(10), function () {
        //     return  User::withMostBlogPostsLastMonth()->take(5)->get();
        // });

        return view('posts.index',
        [
            'posts' => BlogPost::LatestWithRelations()->get(),
            // 'mostCommented' => $mostCommented,
            // 'mostActive' => $mostActive,
            // 'mostActiveLastMonth' => $mostActiveLastMonth,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $this->authorize('create',[BlogPost::class]);
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePost $request)
    {

        $request->validate([
            'title' => 'required|min:2|max:100',
            'content' => 'required|min:2|max:10000'
        ]);

        // $post= new BlogPost();
        // $post->title = $request->input('title');
        // $post->content = $request->input('content');
        // $post->user_id = $request->user()->id;
        // $post->save();

        $validated = $request->validated();
        $validated['user_id']=$request->user()->id;
        // $post= new BlogPost();
        // $post->title = $validated['title'];
        // $post->content = $validated['content'];
        // $post->save();
        $post = BlogPost::create($validated);

        if($request->hasFile('thumbnail')){
            $path = $request->file('thumbnail')->store('thumbnails');
            $post->image()->save(
                Image::make(['path'=>$path])
            );
        }
        event(new BlogPostPosted($post));
        $request->session()->flash('status', 'The blog post was created!');
        return redirect()->route('posts.show', ['post'=> $post->id]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // abort_if(!isset($posts[$id]),404);
        // $posts = BlogPost::find($id);
        // return view('posts.show',[
        //     // 'post'=>BlogPost::with('comments')->findOrFail($id)
        //     'post'=>BlogPost::with(['comments'=> function($query){
        //         return $query->latest();
        //     }])->findOrFail($id)

        // ]);
        // return view('posts.show',
        // [
        //     'post'=>BlogPost::with('comments')->findOrFail($id)
        // ]);

        // cache data

        $blogPost = Cache::remember("blog-post-{$id}",60, function () use($id) {
            return BlogPost::with('comments', 'comments.user')
            ///nested comment relation
                ->findOrFail($id);
        });



        // $counter = new Counter();
        return view('posts.show',
        [
            'post'=>$blogPost,
            'counter'=>CounterFacede::increment("blog-post-{$id}"),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = BlogPost::findOrFail($id);
        // if(Gate::denies('update-post', $post)){
        //     abort(403, "You can't edit this blog post!");
        // }
        //others way
        $this->authorize('update',$post);
        return view('posts.edit', ['post'=> $post]) ;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StorePost $request, $id)
    {
        $post = BlogPost::findOrFail($id);
        // if(Gate::denies('update-post', $post)){
        //     abort(403, "You can't edit this blog post!");
        // }
        $this->authorize('update', $post);
        $validated = $request->validated();
        $post->fill($validated);
        if($request->hasFile('thumbnail')){
            $path = $request->file('thumbnail')->store('thumbnails');

            if($post->image){
                Storage::delete($post->image->path);
                $post->image->path = $path;
                $post->image->save();
            }else{
                $post->image()->save(
                    Image::make(['path' => $path ])
                );
            }
        }
        $post->save();
        $request->session()->flash('status', 'Blog post was Updated!');
        return redirect()->route('posts.show',['post'=> $post->id]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = BlogPost::findOrFail($id);
        // if(Gate::denies('delete-post', $post)){
        //     abort(403, "You can't Delete this blog post!");
        // }
        $this->authorize($post);
        $post->delete();
        session()->flash('status', 'BlogPost was delete!');
        return redirect()->route('posts.index');
    }
}
