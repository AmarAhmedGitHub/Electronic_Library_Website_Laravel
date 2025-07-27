<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminStudentController extends Controller
{

        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    
    public function get_users()
    {
        $students = User::get()->toQuery();
        $students = $students->paginate(15);

        return view('students', compact('students'));
    }
    public function get_users_ajax()
    {
        $students = User::get()->toQuery();
        $students = $students->paginate(15);
        return view('sections.card-students', compact('students'))->render();
    }


    public function search_student(Request $request)
    {
        $search = $request->input('search');
        $students = User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('username', 'LIKE', "%{$search}%")->paginate(15);

        return view('sections.card-students', compact('students'))->render();
    }
}
