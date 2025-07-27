<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BorrwnBook;
use App\Models\BorrwnProject;
use App\Models\Order;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminOrderBorrwnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getOrders()
    {
        return view('orders');
    }
    public function getOrder()
    {
        $orders = Order::with('user:id,name');
        $orders = $orders->whereStatus(0)->paginate(20);
        return view('orders', compact('orders'));
    }

    public function getOrderAjax()
    {
        $orders = Order::with('user:id,name');
        $orders = $orders->whereStatus(0)->paginate(20);
        return view('sections.card-order', compact('orders'))->render();
    }

    public function accept_order(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'docu_id' => 'required|integer',
            'docu_type' => 'required|integer',
            'request_id' => 'required|integer',
            'due_date' => 'required|date',

        ]);
        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()->all()]);
        }

        if ($request->docu_type == 0) {
            $book = Book::find($request->docu_id);
            if (!$book) {
                return response()->json(['error' => __('app.not_found')]);
            } else {
                if ($book->quantity <= 3) {
                    return response()->json(['error' => 'This book has minimam quantity']);
                } else {
                    $borrwn = BorrwnBook::create([
                        'user_id' => $request->user_id,
                        'book_id' => $request->docu_id,
                        'borrwn_date' => Carbon::now(),
                        'due_date' => $request->due_date,
                    ]);
                    Book::where('id', $request->docu_id)->decrement('quantity');
                    Order::where('id', $request->request_id)->update(['status' => 1]);
                    return response()->json(['success' => "Borrwn successfully."]);
                }
            }
        } else if ($request->docu_type == 1) {

            $project = Project::find($request->docu_id);
            if (!$project) {
                return response()->json(['error' => __('app.not_found')]);
            } else {
                $borrwn = BorrwnProject::create([
                    'user_id' => $request->user_id,
                    'project_id' => $request->docu_id,
                    'borrwn_date' => Carbon::now(),
                    'due_date' => $request->due_date,
                ]);
                Order::where('id', $request->request_id)->update(['status' => 1]);
                return response()->json(['success' => "Borrwn successfully."]);
            }
        }
    }

    public function reject_order(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'request_id' => 'required|integer',
        ]);
        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()->all()]);
        }

        Order::where('id', $request->request_id)->update(['status' => 2]);
        return response()->json(['success' => "Rejected."]);
    }

    public function search_order(Request $request)
    {
        $search = $request->input('search');
        $orders = Order::where('name', 'LIKE', "%{$search}%")
            ->orWhere('username', 'LIKE', "%{$search}%")->paginate(20);
        return view('sections.card-order', compact('orders'))->render();
    }

    public function getBookBorrwn()
    {
        $borrwns = DB::table('borrwn_books')
            ->join('users', 'users.id', '=', 'borrwn_books.user_id')
            ->join('books', 'books.id', '=', 'borrwn_books.book_id')
            ->join('category_books', 'books.category_book_id', '=', 'category_books.id')
            ->select(
                'borrwn_books.*',
                'users.name',
                'users.username',
                'users.specialty',
                'users.level',
                'users.avatar',
                'books.name as book_name',
                'category_books.name as category'
            )
            ->where('borrwn_books.status', 0)->paginate(20);
        return view('borrwn-book', compact('borrwns'));
    }

    public function getBookBorrwnAjax()
    {
        $borrwns = DB::table('borrwn_books')
            ->join('users', 'users.id', '=', 'borrwn_books.user_id')
            ->join('books', 'books.id', '=', 'borrwn_books.book_id')
            ->join('category_books', 'books.category_book_id', '=', 'category_books.id')
            ->select(
                'borrwn_books.*',
                'users.name',
                'users.username',
                'users.specialty',
                'users.level',
                'users.avatar',
                'books.name as book_name',
                'category_books.name as category'
            )
            ->where('borrwn_books.status', 0)->paginate(20);
        return view('sections.card-borrwn-books', compact('borrwns'))->render();
    }
    // public function getBookBorrwnsAjax()
    // {
    //     $borrwns = BorrwnBook::with('user:id,name,username,specialty,level,avatar')->with((['book' => function ($books) {
    //         return $books->select('id', 'name', 'category_book_id')->with('category_book:id,name')->get();
    //     }]));
    //     $borrwns = $borrwns->whereStatus(0)->paginate(20);
    //     return view('sections.card-borrwn-book', compact('borrwns'))->render();
    // }

    public function searchBookBorrwn(Request $request)
    {
        $search = $request->search;

        /* $borrwns = BorrwnBook::whereStatus(0)->with(['user' => function ($users) use ($search) {
            return $users->select('id', 'name', 'username', 'specialty', 'level','avatar');
            ->where('name', 'LIKE', "%{$search}%")->orWhere('username', 'LIKE', "%{$search}%");
         },'book' => function ($books) use ($search){
            return $books->select('id', 'name', 'category_book_id')->with('category_book:id,name');
            ->where('name', 'LIKE', "%{$search}%")->orWhere('issn', 'LIKE', "%{$search}%");
         }])->where('id', 'LIKE', "%{$search}%");


         $borrwns = BorrwnBook::with((['user:id,name,username,specialty,level,avatar','book:id,name,category_book_id' => function ($users) use ($search) {
            return $users->select('id', 'name', 'username', 'specialty', 'level','avatar')
            ->where('name', 'LIKE', "%{$search}%")->orWhere('username', 'LIKE', "%{$search}%");
         }]))->with((['book' => function ($books) use ($search) {
            return $books->select('id', 'name', 'category_book_id')->with('category_book:id,name')
            ->where('name', 'LIKE', "%{$search}%")->orWhere('issn', 'LIKE', "%{$search}%");
         }]));
        */
        $borrwns = DB::table('borrwn_books')
            ->join('users', 'users.id', '=', 'borrwn_books.user_id')
            ->join('books', 'books.id', '=', 'borrwn_books.book_id')
            ->join('category_books', 'books.category_book_id', '=', 'category_books.id')
            ->select(
                'borrwn_books.*',
                'users.name',
                'users.username',
                'users.specialty',
                'users.level',
                'users.avatar',
                'books.name as book_name',
                'category_books.name as category'
            )
            ->where('borrwn_books.status', 0)->where(function ($query) use ($search) {
                return $query->where('borrwn_books.id', 'LIKE', "%{$search}%")
                    ->orWhere('users.name', 'LIKE', "%{$search}%")
                    ->orWhere('users.username', 'LIKE', "%{$search}%")
                    ->orWhere('books.name', 'LIKE', "%{$search}%")
                    ->orWhere('books.issn', 'LIKE', "%{$search}%");
            });
        //$borrwns = BorrwnBook::with('book.category_book:id,name');
        // $borrwns = $borrwns->get();
        //return $borrwns;
        $borrwns = $borrwns->paginate(20);
        return view('sections.card-borrwn-books', compact('borrwns'))->render();
    }
    public function searchProjectBorrwn(Request $request)
    {
        $search = $request->search;

        $borrwns = DB::table('borrwn_projects')
        ->join('users', 'users.id', '=', 'borrwn_projects.user_id')
        ->join('projects', 'projects.id', '=', 'borrwn_projects.project_id')
        ->join('category_projects', 'projects.category_project_id', '=', 'category_projects.id')
        ->select(
            'borrwn_projects.*',
            'users.name',
            'users.username',
            'users.specialty',
            'users.level',
            'users.avatar',
            'projects.name as project_name',
            'category_projects.name as category'
        )->where('borrwn_projects.status', 0)->where(function ($query) use ($search) {
            return $query->where('borrwn_projects.id', 'LIKE', "%{$search}%")
                ->orWhere('users.name', 'LIKE', "%{$search}%")
                ->orWhere('users.username', 'LIKE', "%{$search}%")
                ->orWhere('projects.name', 'LIKE', "%{$search}%");
        });


        $borrwns = $borrwns->whereStatus(0)->paginate(20);
        return view('sections.card-borrwn-project', compact('borrwns'))->render();
    }

    public function getProjectBorrwn()
    {
        // $borrwns = BorrwnProject::with('user:id,name,username,specialty,level,avatar')->with((['project' => function ($projects) {
        //     return $projects->select('id', 'name', 'category_project_id')->with('category_project:id,name')->get();
        // }]));
        // $borrwns = $borrwns->whereStatus(0)->paginate(20);

        $borrwns = DB::table('borrwn_projects')
        ->join('users', 'users.id', '=', 'borrwn_projects.user_id')
        ->join('projects', 'projects.id', '=', 'borrwn_projects.project_id')
        ->join('category_projects', 'projects.category_project_id', '=', 'category_projects.id')
        ->select(
            'borrwn_projects.*',
            'users.name',
            'users.username',
            'users.specialty',
            'users.level',
            'users.avatar',
            'projects.name as project_name',
            'category_projects.name as category'
        )
        ->where('borrwn_projects.status', 0)->paginate(20);
        return view('borrwn-project', compact('borrwns'));
    }

    public function getProjectBorrwnAjax()
    {
        $borrwns = DB::table('borrwn_projects')
        ->join('users', 'users.id', '=', 'borrwn_projects.user_id')
        ->join('projects', 'projects.id', '=', 'borrwn_projects.project_id')
        ->join('category_projects', 'projects.category_project_id', '=', 'category_projects.id')
        ->select(
            'borrwn_projects.*',
            'users.name',
            'users.username',
            'users.specialty',
            'users.level',
            'users.avatar',
            'projects.name as project_name',
            'category_projects.name as category'
        )
        ->where('borrwn_projects.status', 0)->paginate(20);
        return view('sections.card-borrwn-project', compact('borrwns'))->render();
    }
}
