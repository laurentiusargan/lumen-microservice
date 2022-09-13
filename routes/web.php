<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthenticationController;
use Laravel\Lumen\Routing\Router;

/** @var Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**
 * Posts endpoints
 */
$router->group(['prefix' => 'posts'], function () use ($router) {
    /**
     * endpoints requiring authorization
     */
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('create', PostController::class . '@create');
        $router->put('/{postId}', PostController::class . '@update');
        $router->delete('/{postId}', PostController::class . '@delete');
        $router->delete('/{postId}/comments', CommentController::class . '@deleteCommentsPost');
    });

    /**
     * endpoints not requiring authorization
     */
    $router->get('', PostController::class . '@index');
    $router->get('/{postId}', PostController::class . '@details');
});

/**
 * Comments endpoints
 */
$router->group(['prefix' => 'comments'], function () use ($router) {
    /**
     * endpoints requiring authorization
     */
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('create', CommentController::class . '@create');
        $router->put('{commentId}', CommentController::class . '@update');
        $router->delete('{commentId}', CommentController::class . '@deleteComment');
        $router->delete('', CommentController::class . '@deleteUserComments');
    });

    /**
     * endpoints not requiring authorization
     */
    $router->get('{comment_id}', CommentController::class . '@details');
    $router->get('', CommentController::class . '@getPostComments');
    $router->get('{userId}/comments', CommentController::class . '@getUserComments');
});

/**
 * Users endpoints
 */
$router->group(['prefix' => 'users'], function () use ($router) {
    /**
     * endpoints requiring authentication
     */
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->put('{userId}', UserController::class . '@update');
        $router->delete('{userId}', UserController::class . '@delete');
        $router->delete('{userId}/posts', PostController::class . '@deleteUserPosts');
    });

    /**
     * endpoints not requiring authentication
     */
    $router->get('', UserController::class . '@index');
    $router->get('{userId}', UserController::class . '@details');
    $router->get('{userId}/posts', PostController::class . '@getUserPosts');
    $router->post('create', UserController::class . '@create');

});

/**
 * Authentication endpoints
 */
$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login', AuthenticationController::class . '@login');
    $router->post('logout', AuthenticationController::class . '@logout');
    $router->post('refresh', AuthenticationController::class . '@refreshToken');
    $router->get('currentUser', AuthenticationController::class . '@currentUser');
});
