<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\User;
use App\Imports\UsersImport;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(){
        $users = User::with('accounts')->whereHas(
            'roles', function($q){
                $q->where('name', 'member');
            }
        )->latest()->get();

        $response = apiResponse(true, 'Users returned successfully', $users);
        return $response;
    }

    public function addUser(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);
        if($validator->fails()){
            $messages = $validator->messages();
            $response = apiResponse(false, 'validation failed', $messages->first(), 401);
        } else {
            try {
                $image_path = null;
                if($request->file('profile_image')){
                    $fileName = pathinfo($request->file('profile_image')->getClientOriginalName(), PATHINFO_FILENAME) .time();
                    $extension = $request->file('profile_image')->getClientOriginalExtension();
                    $image_path = $request->file('profile_image')->storeAs(
                        'image',
                        $fileName . '.' .$extension,
                        'public'
                    );
                }
                $user =  User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                    'profile_image' => $image_path,
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
            'phone' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            $response = apiResponse(false, 'validation failed', $validator->errors(), 400);
        } else {
            if(!Auth::attempt(['phone' => $request->phone, 'password' => $request->password, 'status' => 1])){
                $msg = 'Invalid credential';
                if(Auth::attempt(['phone' => $request->phone, 'password' => $request->password])){
                    $msg = 'User is not verified';
                }
                $response = apiResponse(false, $msg, (object)[], 401);
            } else {
                $userProfile = getProfileInfo();
                /** @var \App\Models\MyUserModel $user **/
                $user = Auth::user();
                $token = $user->createToken('token')->plainTextToken;
                $response = apiResponse(true, 'Logged in successfully', (object)['token' => $token, 'user' => $userProfile]);
            }
        }
        return $response;
    }

    public function changePassword(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            $response = apiResponse(false, 'validation failed', $validator->errors(), 400);
        } else {
            $oldPasswordValid = Hash::check($request->oldPassword, $user->password);
            if($oldPasswordValid){
                $newPassword = Hash::make($request->input('password'));
                $user->update([
                    'password' => $newPassword
                ]);
                $response = apiResponse(true, 'Password updated', (object)[], 200);
            } else {
                $response = apiResponse(false, 'Old Password did not match', (object)[], 200);
            }
        }
        return $response;
    }

    public function getProfile()
    {
        $response = apiResponse(true, 'Profile returned successfully', getProfileInfo());
        return $response;
    }

    public function getUserById($id)
    {
        $response = apiResponse(true, 'Profile returned successfully', getProfileInfo($id));
        return $response;
    }

    public function editUser(Request $request, User $user)
    {
        $userProfile = getProfileInfo();
        if(Auth::user()->id === $user->id || $userProfile->role === 'superadmin'){
            $user->name = $request->name;
            $user->gender = $request->gender;
            $user->address = $request->address;
            $user->father_name = $request->father_name;
            $user->mother_name = $request->mother_name;
            $user->national_id = $request->national_id;
    
            $user->save();
            
            $response = apiResponse(true, 'Profile edited successfully', $user);
        } else {
            $response = apiResponse(false, 'User can not change other profile', (object)[], 403);
        }
        return $response;
    }

    public function changeStatus(User $user)
    {
        try {
            $user->update([
                'status'=> !$user->status
            ]);
            $msg = $user->status == 1 ? 'User approved' : 'User disapproved';
            $response = apiResponse(true, $msg, $user, 200);
        } catch (\Throwable $th) {
            $response = apiResponse(false, $th->getMessage(), (object)[], 409);
        }
        return $response;
    }
}
