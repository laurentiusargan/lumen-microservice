<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\ResponseFactory;
use Laravel\Lumen\Routing\Controller as BaseController;

class CommentController extends BaseController
{

    /**
     * get comment given its id
     * @param $commentId
     * @return Comment
     */
    public function details($commentId): Comment
    {
        return Comment::findOrFail($commentId);
    }

    /**
     * @param Request $request
     * @return Comment|Response|ResponseFactory
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, ['content' => 'required']);

        $user = Auth::user();
        $data = $request->all();
        $post = Post::findOrFail($data["postId"]);

        if (!$post) {
            return response("Post doesn't exist", 404);
        }

        $comment = new Comment;
        $comment->fill($data);
        $comment->post_id = $post->id;
        $comment->user_id = $user->id;
        $comment->save();

        return $comment;
    }

    /**
     * edit a comment
     * @param Request $request
     * @param int $commentId
     * @return Comment
     * @throws ValidationException
     */
    public function update(Request $request, int $commentId): Comment
    {
        $this->validate($request, ['content' => 'filled']);

        $user = Auth::user();

        Gate::authorize(
            'isPremiumUser',
            $user
        ); // check if user has premium subscription, thus can edit own comments

        $comment = $this->details($commentId);

        Gate::authorize('editComment', $comment);  // check if editting comment is permitted

        $comment->fill($request->all());
        $comment->save();

        return $comment;
    }

    /**
     * delete a comment given its id
     * @param int $commentId
     */
    public function delete(int $commentId): void
    {
        $user = Auth::user();

        Gate::authorize(
            'isPremiumUser',
            $user
        );  // check if user has premium subscription, thus can delete own comments

        $comment = $this->details($commentId);

        Gate::authorize('deleteComment', $comment);  // check if deleting comment is permitted

        $comment->delete();
    }

    /**
     * get all comments of a given post
     * or if no post_id given, return all comments of all posts
     *
     * @param Request $request
     * @return array
     */
    public function getPostComments(Request $request): array
    {
        $data = $request->all();
        return Comment::when(isset($data['postId']), function ($query) use ($data) {
            Post::findOrFail($data['postId']);
            $query->where('post_id', $data['postId']);
        })->get();
    }

    /**
     * get all comments of a given user
     * @param int $userId
     * @return array
     */
    public function getUserComments(int $userId): array
    {
        $user = User::findOrFail($userId);
        return Comment::where('user_id', $user->id)->get();
    }

    /**
     * delete all comments of a given post
     * @param int $postId
     * @return Response
     */
    public function deletePostComments(int $postId): Response
    {
        $user = Auth::user();
        if ($user->subscription === "premium") {  // user has to be premium to edit their comments
            $post = Post::findOrFail($postId);
            Comment::where('post_id', $post->id)->where('user_id', $user->id)->delete();
        }

        return response("Unauthorized action, only premium users can delete their comments.", 401);
    }

    /**
     * delete all comments of a given user
     * @return Response
     */
    public function deleteUserComments(): Response
    {
        $user = Auth::user();
        if ($user->subscription === "premium") {  // user has to be premium to edit their comments
            Comment::where('user_id', $user->id)->delete();
        }

        return response("Unauthorized action, only premium users can delete their comments.", 401);
    }
}
