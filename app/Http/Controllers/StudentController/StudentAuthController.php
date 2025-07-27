<?php

namespace App\Http\Controllers\StudentController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class StudentAuthController extends Controller
{

   

    public function login(Request $request)
    {
        //validate fields
        $attrs = $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6'
        ]);

        // attempt login
       
        if(!Auth::attempt($attrs))
        {
            return response([
                'message' => 'Invalid credentials.'
            ], 403);
        }

        
        //return user & token in response
          /** @var \App\Models\User $user **/
          $user=auth()->user();
        
        return response([
            'user' => $user,
            'token' => $user->createToken('secret')->plainTextToken
        ], 200);
    }

    public function logout()
    {
        /** @var \App\Models\User $user **/
        $user=auth()->user();
        $user->tokens()->delete();
        return response([
            'message' => 'Logout success.'
        ], 200);
    }

    public function user(){
        return response([
            'user'=>auth()->user()
        ],200);
    }
}
