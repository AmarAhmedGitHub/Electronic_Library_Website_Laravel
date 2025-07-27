<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * show dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('dashboard');
    }

    public function Profile()
    {
        return view('profile');
    }
    public function Setting()
    {
        return view('setting');
    }

    public function editProfile(Request $request)
    {
        ini_set("memory_limit", "512M");
        ini_set('post_max_size', '256M');
        ini_set('upload_max_filesize', '128M');
        if ($request->ajax()) {
            $userid = Auth::guard('admin')->user()->id;
            $admin = Admin::find($userid);
            if ($request->im_file) {
                
                $validation = Validator::make($request->all(), [
                   // 'im_file' => 'required|mimes:jpeg,jpg,png,gif|required|max:2048',
                    'im_file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4072',
                ]);
                if ($validation->fails()) {
                    return response()->json(['error' => $validation->errors()->first()]);
                } else {
                    $file = $request->file('im_file');
                    if ($request->has('name')==null || $request->has('username')==null) {
                        $validation = Validator::make($request->all(), [
                            'name' => 'required|string',
                            'username' => 'required|string',
                        ]);
                        if ($validation->fails()) {
                            return response()->json(['error' => $validation->errors()->all()]);
                        } else {
                            $this->deleteFile($admin->avatar);
                            $path = $this->saveFile($file, 'adminprofile');
                            Admin::where('id', $userid)->update(['name' => $request->name, 'username' => $request->username, 'avatar' => $path]);
                            return response()->json(['success' => "Updated successfully."]);
                        }
                    } else {
                        $this->deleteFile($admin->avatar);
                        $path = $this->saveFile($file, 'adminprofile');
                        Admin::where('id', $userid)->update(['avatar' => $path]);
                        return response()->json(['success' => "Updated successfully."]);
                    }
                }
            } else {
                $validation = Validator::make($request->all(), [
                    'name' => 'required|string',
                    'username' => 'required|string',
                ]);
                if ($validation->fails()) {
                    return response()->json(['error' => $validation->errors()->first()]);
                }

                Admin::where('id', $userid)->update(['name' => $request->name, 'username' => $request->username]);
                return response()->json(['success' => "Updated successfully."]);
            }
        }
    }


    public function change_password(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $userid = Auth::guard('admin')->user()->id;
            $rules = array(
                'old_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password',
            );
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                $arr = response()->json(['error' => $validator->errors()->all()]);
            } else {
                try {
                    if ((Hash::check(request('old_password'), Auth::guard('admin')->user()->password)) == false) {
                        $arr = response()->json(['error' => "Check your old password."]);
                    } else if ((Hash::check(request('new_password'), Auth::guard('admin')->user()->password)) == true) {
                        $arr = response()->json(['error' => "Please enter a password which is not similar then current password."]);
                    } else {
                        Admin::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                        $arr = response()->json(['success' => "Password updated successfully."]);
                    }
                } catch (Exception $ex) {
                    if (isset($ex->errorInfo[2])) {
                        $msg = $ex->errorInfo[2];
                    } else {
                        $msg = $ex->getMessage();
                    }
                    $arr = response()->json(['error' => $msg]);
                }
            }
            return $arr;
        }
    }
}
