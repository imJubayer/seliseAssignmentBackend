<?php

namespace App\Http\Controllers;
use App\Models\User;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(){
        $users = User::whereHas(
            'roles', function($q){
                $q->where('name', 'member');
            }
        )->latest()->get();

        $response = apiResponse(true, 'Users returned successfully', $users);
        return $response;
    }

    public function addUser(Request $request){
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|unique:users,email',
            'password' => 'required',
        ]);
        if($validator->fails()){
            $messages = $validator->messages();
            $response = apiResponse(false, 'validation failed', $messages->first(), 401);
        } else {
            try {
                $user =  User::create([
                    'firstname' => $request->input('firstname'),
                    'lastname' => $request->input('lastname'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password'))
                ]);
                $user->assignRole('member');
                $response = apiResponse(true, 'Users created successfully', $user);
            } catch (\Throwable $th) {
                $response = apiResponse(false, $th->getMessage(), (object)[], 409);
            }
        }
        return $response;
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            $response = apiResponse(false, 'validation failed', $validator->errors(), 400);
        } else {
            if(!Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1])){
                $msg = 'Invalid credential';
                if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                    $msg = 'User is not verified';
                }
                $response = apiResponse(false, $msg, (object)[], 401);
            } else {
                // $userProfile = getProfileInfo();
                /** @var \App\Models\MyUserModel $user **/
                $user = Auth::user();
                $token = $user->createToken('token')->plainTextToken;
                $response = apiResponse(true, 'Logged in successfully', (object)['token' => $token, 'user' => $user]);
            }
        }
        return $response;
    }

    public function getProfile()
    {
        $response = apiResponse(true, 'Profile returned successfully', Auth::user());
        return $response;
    }

    public function logout(){
        try {
            Auth::user()->tokens()->delete();
            $msg = 'Logged out and token deleted';
            $response = apiResponse(true, $msg, $user, 200);
        } catch (\Throwable $th) {
            $response = apiResponse(false, $th->getMessage(), (object)[], 409);
        }
        return $response;
    }
}
