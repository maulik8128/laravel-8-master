@extends('layouts.app')
@section('title',$post->title)


@section('content')
<div class="row">
    <div class="col-8">
            {{-- @if ($posts['is_new'])
            <div>A new blog posts! Using if</div>
            @endif

            @unless ($posts['is_new'])

            <div>A blog posts is Old </div>

            @endunless

        <h1>{{ $posts['title'] }}</h1>
        <p>{{ $posts['content'] }}</p>

        @isset($posts['has_comments'])

            <div>The post has some comments... using isset</div>

        @endisset --}}

        @if($post->image)
        <div style="background-image: url('{{ $post->image->url() }}'); min-height: 500px; color: white; text-align: center; background-attachment: fixed;">
            <h1 style="padding-top: 100px; text-shadow: 1px 2px #000">
        @else
            <h1>
        @endif
            {{ $post->title }}
            @badge(['show' => now()->diffInMinutes($post->created_at) < 30])
                Brand new Post!
            @endbadge
        @if($post->image)
            </h1>
        </div>
        @else
            </h1>
        @endif
        <p>{{ $post->content }}</p>
        @updated(['date'=>$post->created_at, 'name'=> $post->user->name])
        @endupdated()
        @updated(['date'=>$post->updated_at,])
            updated
        @endupdated()
        @tags(['tags' => $post->tags])@endtags
        {{--  <p> Currently read by {{ $counter }} people</p>  --}}
        <p>{{ trans_choice('messages.people.reading', $counter) }}</p>

        <h4>Comments</h4>
        @commentForm(['route'=> route('posts.comments.store',['post'=>$post->id])])
        @endcommentForm()

        @commentList(['comments' => $post->comments])
        @endcommentList()
    </div>
    <div class="col-4">
        @include('posts._activity')
    </div>
</div>

@endsection
