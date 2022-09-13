<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;

class UserController extends BaseController
{
    /**
     * getter of all users
     * @return User[]|Collection
     */
    public function index(): Collection
    {
        return User::all();
    }

    /**
     * getter of a user given its id
     * @param int $userId
     * @return User
     */
    public function details(int $userId): User
    {
        return User::findOrFail($userId);
    }

    /**
     * create a new user
     * @param Request $request
     * @return User
     * @throws ValidationException
     */
    public function create(Request $request): User
    {
        /**
         * Validate request data before new user creation
         */
        $this->validate($request, [
            'first_name' => 'filled|string',
            'last_name' => 'filled|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|max:30',
            'picture' => 'filled|url'
        ]);

        $data = $request->all();

        $user = new User;
        $user->fill($data);
        $user->password = Hash::make($data["password"]);
        $user->save();

        return $user;
    }

    /**
     * delete a user given its id
     * @param int $userId
     * @return void
     */
    public function delete(int $userId): void
    {
        User::where('id', $userId)->delete();
    }
}
