<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;

class PostController extends BaseController
{
    /**
     * get all posts
     * @return Collection
     */
    public function index(): Collection
    {
        return Post::all();
    }

    /**
     * get post given its id
     *
     * comments_count is by default included
     * if $comment=0, comments_count is not included
     *
     * @param Request $request
     * @param int $postId
     * @return mixed
     */
    public function details(Request $request, int $postId)
    {
        $data = $request->all();
        return Post::when($data['comments'] == 1, function ($query) {
            $query->with('comments');
        })->findOrFail($postId);
    }

    /**
     * create a new post
     * @param Request $request
     * @return Post
     * @throws ValidationException
     */
    public function create(Request $request): Post
    {
        $this->validate($request, ['title' => 'required', 'content' => 'required']);

        $user = Auth::user();

        $data = $request->all();

        $post = new Post;
        $post->fill($data);
        $post->user_id = $user->id;
        $post->save();

        return $post;
    }

    /**
     * edit a post
     * @param Request $request
     * @param int $postId
     * @return Post
     * @throws ValidationException
     */
    public function update(Request $request, int $postId): Post
    {
        $this->validate($request, ['title' => 'filled', 'content' => 'filled']);
        Auth::user();

        $post = Post::findOrFail($postId);

        Gate::authorize('editPost', $post);  // check if editting post is permitted

        $post->fill($request->all());
        $post->save();

        return $post;
    }

    /**
     * @param int $postId
     */
    public function delete(int $postId): void
    {
        $post = Post::findOrFail($postId);

        Gate::authorize('deletePost', $post);  // check if deleting post is permitted

        $post->delete();
    }

    /**
     * getter of all posts of a given user
     * @param int $userId
     * @return Collection
     */
    public function getUserPosts(int $userId): Collection
    {
        $user = User::findOrFail($userId);
        return Post::where('user_id', $user->id)->get();
    }

    /**
     * delete all posts of a given user
     * @param int $userId
     */
    public function deleteUserPosts(int $userId): void
    {
        Post::where('user_id', $userId)->delete();
    }
}
