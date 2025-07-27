<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AdminAuthController extends Controller
{
       //
       public function __construct()
       {
         $this->middleware('guest:admin', ['except' => ['logout']]);
       }
       
       public function showLoginForm()
       {
         return view('auth-login');
       }
       public function showRegisterForm()
       {
         return view('auth-register');
   
   
       }
   
       public function register(Request $request)
       {
           //validate fields
           $attrs = $request->validate([
               'name' => 'required|string',
               'username' => 'required|string|unique:admins',
               'password' => 'required|min:6|confirmed'
           ]);
   
           //create user
           $user = Admin::create([
               'name' => $attrs['name'],
               'username' => $attrs['username'],
               'password' => bcrypt($attrs['password'])
           ]);
           $use = auth()->user();
           return redirect()->intended(route('login'));
       }
       
       public function login(Request $request)
       {
         // Validate the form data
         $this->validate($request, [
           'username'   => 'required|string',
           'password' => 'required|min:6'
         ]);
         // Attempt to log the user in
         if (Auth::guard('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
           // if successful, then redirect to their intended location
           return redirect()->intended(route('admin.dashboard'));
         } 
         // if unsuccessful, then redirect back to the login with the form data
         return redirect()->back()->withInput($request->only('username', 'remember'));
       }

       
       
       public function logout(Request $request)
       {
           Auth::guard('admin')->logout();
           $request->session()->invalidate();
           Artisan::call('cache:clear');
        Session::flush();
           return redirect()->intended(route('login'));
       }

     
}
