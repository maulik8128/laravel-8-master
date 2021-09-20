<?php

namespace App\Models;

use App\Models\Comment;
use App\Scopes\LatestScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\DeletedAdminScope;
use Illuminate\Support\Facades\Cache;

class BlogPost extends Model
{
    use SoftDeletes;
    protected $fillable = ['title', 'content', 'user_id'];
    protected $table = 'blog_posts';
    use HasFactory;


    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }
    public function image()
    {
        return $this->hasOne(Image::class);
    }

    public function scopeLatest(Builder $query)
    {
        return $query->orderBy(static::CREATED_AT, 'desc');
    }
    public function scopeMostCommented(Builder $query)
    {
        // comments_count
        return $query->withCount('comments')->orderBy('comments_count', 'desc');
    }
    public function scopeLatestWithRelations(Builder $query)
    {
        return $query->latest()
        ->withCount('comments')
        ->with('user','tags');

    }
    // delete relational comments in blog post
    public static function boot()
    {
        static::addGlobalScope(new DeletedAdminScope);
        parent::boot();
        // static::addGlobalScope(new LatestScope);
        static::deleting(function(BlogPost $blogPost){
            $blogPost->comments()->delete();
            Cache::forget("blog-post-{$blogPost->id}");
        });
        static::updating(function(BlogPost $blogPost){
            Cache::forget("blog-post-{$blogPost->id}");
        });
        static::restoring(function(BlogPost $blogPost){
            $blogPost->comments()->restore();
        });
    }

}
