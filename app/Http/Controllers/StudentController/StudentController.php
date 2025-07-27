<?php

namespace App\Http\Controllers\StudentController;

use App\Events\NewOrder;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Book;
use App\Models\CategoryBook;
use App\Models\CategoryEbook;
use App\Models\CategoryProject;
use App\Models\Order;
use App\Models\User;
use App\Notifications\RealTimeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function getBook()
    {
        $books = CategoryBook::select('id', 'name')->with(['books' => function ($books) {
            return $books->select('id', 'name', 'issn', 'author', 'publisher', 'quantity', 'image', 'language', 'category_book_id')->whereStatus(1)->orderBy('created_at', 'desc')->get();
        }])->get();
        foreach ($books as $p) {
            foreach ($p->books as $a) {
                $a->image = URL::to('/') . 'storage' . $a->image;
            }
        }
        return response([
            'category' => $books
        ], 200);
    }
    public function getEBook()
    {
        $books = CategoryEbook::select('id', 'name')->with(['ebooks' => function ($books) {
            return $books->select('id', 'name', 'issn', 'author', 'publisher', 'path', 'language', 'category_ebook_id')->whereStatus(1)->get();
        }])->get();

        foreach ($books as $p) {
            foreach ($p->projects as $a) {
                $a->path = URL::to('/') . 'storage' . $a->path;
            }
        }
        return response([
            'category' => $books
        ], 200);
    }
    public function getProject()
    {
        $project = CategoryProject::select('id', 'name')->with(['projects' => function ($projects) {
            return $projects->select('id', 'name', 'supervisor', 'quantity', 'language', 'file_path', 'category_project_id')->whereStatus(1)->orderBy('created_at', 'desc')->get();
        }])->get();

        foreach ($project as $p) {
            foreach ($p->projects as $a) {
                $a->file_path = URL::to('/') . 'storage' . $a->file_path;
            }
        }

        return response([
            'category' => $project
        ], 200);
    }

    public function downloadFile(Request $request)
    {
        $headers = array(
            'Content-Type: application/pdf',
        );
        $pa = public_path() . '/storage/ebook/' . $request->path;
        /** @var \Illuminate\Filesystem\FilesystemManager $disk */
        // $disk =Storage::disk('ebook');
        // return response(
        //     $disk->download($request->path, $request->name.'pdf', $headers)
        // );

        return Response::download($pa, 'file.pdf', $headers);
    }


    public function order_borrwn(Request $request)
    {

        $attrs = $request->validate([
            'id_docus' => 'required|string',
            'name_docus' => 'required|string',
            'category_docus' => 'required|string',
            'type_order' => 'required|integer',
        ]);

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'id_docus' => $attrs['id_docus'],
            'name_docus' => $attrs['name_docus'],
            'category_docus' => $attrs['category_docus'],
            'type_order' => $attrs['type_order'],
        ]);

        $user = Admin::first();
        //$order->put('student', auth()->user()->name)->all();

        $user->notify(new RealTimeNotification($order));

        event(new NewOrder($order));

        return response([
            'message' => 'Successfully requested.',
        ], 200);
    }
    public function getOrder()
    {
        Order::with('user:id,name')->whereStatus(0)->where('user_id', auth()->user()->id);
        return response([
            'order' => Order::with('user:id,name')->whereStatus(0)->where('user_id', auth()->user()->id)->get()
        ], 200);
    }

    public function searchBook(Request $request)
    {
        //$search="";
        $request->validate([
            'query' => 'required|string',

        ]);
        $search = $request->get('query');

        $books = Book::select('name', 'image')->where('issn', 'LIKE', '%' . $search . '%')
            ->orWhere('name', 'LIKE', '%' . $search . '%')
            ->orWhere('author', 'LIKE', '%' . $search . '%')
            ->orWhere('publisher', 'LIKE', '%' . $search . '%')
            ->orWhere('year', 'LIKE', '%' . $search . '%')->get();

        return response($books, 200);
    }

    public function getAllBook()
    {


        $books = Book::select('name')->get();
        return response($books, 200);
    }


    public function editProfile(Request $request)
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();

        if ($request->has('image')) {
            $this->deleteFile($user->avatar);
            $image = $this->saveImage($request->image, 'studentProfiles');
            $user->update([
                'avatar' => $image
            ]);
        }
        if ($request->has('username') && $request->username != null) {

            if (($request->has('old_password') && $request->old_password != null) || ($request->has('new_password') && $request->new_password != null)) {
                $input = $request->all();
                $rules = array(
                    'old_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                );
                $validator = Validator::make($input, $rules);

                if ($validator->fails()) {
                    return response([
                        'message' => $validator->errors()->all(),
                    ]);
                } else {
                    try {
                        if ((Hash::check(request('old_password'), auth()->user()->password)) == false) {
                            return response(['message' => "Check your old password. 1"]);
                        } else if ((Hash::check(request('new_password'), auth()->user()->password)) == true) {
                            return response(['message' => "Please enter a password which is not similar then current password."]);
                        } else {
                            User::where('id', $user->id)->update(['password' => Hash::make($input['new_password']), 'username' => $request->username]);
                            return response(['message' => "updated successfully."]);
                        }
                    } catch (Exception $ex) {
                        if (isset($ex->errorInfo[2])) {
                            $msg = $ex->errorInfo[2];
                        } else {
                            $msg = $ex->getMessage();
                        }
                        return response(['message' => $msg]);
                    }
                }
            }


            $validation = Validator::make($request->all(), [
                'username' => 'required|string|unique:users'
            ]);
            if ($validation->fails()) {
                return response([
                    'message' => $validation->errors()->all(),
                ]);
            } else {
                $user->update([
                    'username' => $request->username
                ]);
            }
        } else if (($request->has('old_password') && $request->old_password != null) || ($request->has('new_password') && $request->new_password != null)) {
            $input = $request->all();
            $rules = array(
                'old_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password',
            );
            $validator = Validator::make($input, $rules);

            if ($validator->fails()) {
                return response([
                    'message' => $validator->errors()->all(),
                ]);
            } else {
                try {
                    if ((Hash::check(request('old_password'), auth()->user()->password)) == false) {
                        return response(['message' => "Check your old password. 2"]);
                    } else if ((Hash::check(request('new_password'), auth()->user()->password)) == true) {
                        return response(['message' => "Please enter a password which is not similar then current password."]);
                    } else {
                        User::where('id', $user->id)->update(['password' => Hash::make($input['new_password'])]);
                        return response(['message' => "updated successfully."]);
                    }
                } catch (Exception $ex) {
                    if (isset($ex->errorInfo[2])) {
                        $msg = $ex->errorInfo[2];
                    } else {
                        $msg = $ex->getMessage();
                    }
                    return response(['message' => $msg]);
                }
            }
        }

        return response([
            'message' => 'User updated.'
        ], 200);
    }
}
