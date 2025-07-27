<?php

namespace App\Http\Controllers\AdminController;


use App\Exports\BooksExport;
use App\Http\Controllers\Controller;
use App\Imports\BooksImport;
use App\Models\Book;
use App\Models\CategoryBook;

use App\Models\User;
use Illuminate\Http\Request;
use \Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminBookController extends Controller
{//
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


    public function get_cat_book()
    {
        $category_book = CategoryBook::all(); //->toQuery();
        //$category_book = $category_book->paginate(6);
        return view('category-book', compact('category_book'));
    }
    public function get_cat_book_ajax()
    {
        $category_book = CategoryBook::all(); //->toQuery();
        //$category_book = $category_book->paginate(6);
        return view('sections.category-book-card', compact('category_book'));
    }


    public function get_cat_with_book()
    {
        $category_book = CategoryBook::select('id', 'name')->with(['books' => function ($books) {
            return $books->select('id', 'name', 'category_book_id')->orderBy('created_at', 'desc')->get();
        }])->withCount('books')->get(); //->toQuery();
        //$category_book = $category_book->paginate(6);
        return view('test', compact('category_book'));
    }




    public function get_books($cat_id)
    {
        $books = Book::with('category_book:id,name');
        $books = $books->whereCategoryBookId($cat_id)->paginate(6);
        //$books=$books->get();
        $catid = $cat_id;

        return view('books', compact('books', 'catid'));
    }
    public function get_book_ajax($cat_id)
    {
        $books = Book::with('category_book:id,name');
        $books = $books->whereCategoryBookId($cat_id)->paginate(6);
        //$books=$books->get();
        $catid = $cat_id;

        return view('sections.card-books', compact('books'))->render();
    }



    public function get_users()
    {
        $user = User::all();
        $user = $user->makeVisible('password');
        //$users=Crypt::decrypt('password');
        // $user=$user->get('name');

        //$url = Storage::disk('ebook')->url('89bf6de7-793b-31b7-adef-7819cc3e8b8f.pdf');
        $url = Storage::disk('ebook')->exists('book/TWAZ2muD4mP4lGRvs6yDTWs1aeuLrMKM2S2O0W3U.pdf');
        //  $file_s=Storage::disk('ebook');
        //  $file_s->putFile('','');
        //  Storage::disk('ebook')->putFile('test', 'D:\test\a.pdf');


        return view('a', compact('user', 'url'));
    }




    public function addcategory(Request $request)
    {
        if ($request->ajax()) {
            $attrs = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string'
            ]);
            if ($attrs->fails()) {
                return response()->json(['error' => $attrs->errors()->all()]);
            }

            CategoryBook::create([
                'name' => $request->name,
                'description' => $request->description
            ]);
            // return redirect()->back()->with('added', 'category has been created successfully');
            return response()->json(['success' => 'category has been created successfully.']);
        }
    }



    public function update_category(Request $request)
    {

        if ($request->ajax()) {


            $category = CategoryBook::find($request->id);
            if (!$category) {

                return response()->json(['error' => __('app.not_found')]);
            } else {

                $attrs = Validator::make($request->all(), [
                    'name' => 'required|string',
                    'description' => 'required|string'
                ]);
                if ($attrs->fails()) {
                    return response()->json(['error' => $attrs->errors()->all()]);
                }

                $category->update([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);

                return response()->json(['success' => 'category has been updated successfully.']);
            }
        }
    }

    public function delete_categorys(Request $request)
    {

        if ($request->ajax()) {
            $book = CategoryBook::find($request->id);
            if (!$book) {
                return response()->json(['error' => __('app.not_found')]);
            }
            $book->delete();
            // echo 'success,category has been deleted successfully';
            return response()->json(['success' => 'category has been deleted successfully.']);
        }
    }


    public function addBook(Request $request)
    {
        if ($request->ajax()) {

            $attrs = Validator::make($request->all(), [
                'issn' => 'string|unique:books',
                'bname' => 'required|string',
                'author' => 'required|string',
                'publisher' => 'required|string',
                'date' => 'required|date',
            ]);

            if ($attrs->fails()) {
                return response()->json(['error' => $attrs->errors()->all()]);
            }
            Book::create([
                'issn' => $request->issn,
                'name' => $request->bname,
                'author' => $request->author,
                'publisher' => $request->publisher,
                'language' => $request->lang,
                'quantity' => $request->quantity,
                'status' => $request->status,
                'year' => $request->date,
                'category_book_id' => $request->category,
            ]);
            // echo 'success,book has been created successfully';
            return response()->json(['success' => 'book has been created successfully.']);
        }
    }


    public function update_book(Request $request)
    {

        if ($request->ajax()) {
            $book = Book::find($request->book_id);
            if (!$book) {
                return response()->json(['error' => __('app.not_found')]);
            } else {


                $attrs = Validator::make($request->all(), [
                    'bname' => 'required|string',
                    'author' => 'required|string',
                    'publisher' => 'required|string',
                    'date' => 'required|date',
                ]);

                if ($attrs->fails()) {
                    return response()->json(['error' => $attrs->errors()->all()]);
                }

                $book->update([
                    'issn' => $request->issn,
                    'name' => $request->bname,
                    'author' => $request->author,
                    'publisher' => $request->publisher,
                    'language' => $request->lang,
                    'quantity' => $request->quantity,
                    'status' => $request->status,
                    'year' => $request->date,
                ]);

                return response()->json(['success' => 'book has been updated successfully.']);
            }
        }
    }


    public function delete_books(Request $request)
    {

        if ($request->ajax()) {
            $book = Book::find($request->id);
            if (!$book) {
                return response()->json(['error' => __('app.not_found')]);
            }
            $book->delete();
            return response()->json(['success' => 'book has been deleted successfully.']);
        }
    }


    public function advanced_search_book(Request $request)
    {
        $search = $request->search;

        if ($request->has('cate_id')) {
            $catid = $request->cate_id;
            $books = Book::with('category_book:id,name');
            $books = $books->where(function ($qurey) use ($search) {
                return $qurey->where('issn', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('author', 'LIKE', "%{$search}%")
                    ->orWhere('publisher', 'LIKE', "%{$search}%")
                    ->orWhere('year', 'LIKE', "%{$search}%");
            })->whereCategoryBookId($catid)->paginate(6);
        } else {


            $books = Book::with('category_book:id,name');
            $books = $books->where('name', 'LIKE', "%{$search}%")
                ->orWhere('author', 'LIKE', "%{$search}%")
                ->orWhere('publisher', 'LIKE', "%{$search}%")
                ->orWhere('issn', 'LIKE', "%{$search}%")
                ->orWhere('year', 'LIKE', "%{$search}%")->paginate(6);
        }
        //$books=$books->get();
        return view('search-books', compact('books'));
    }

    // public function search_book(Request $request)
    // {
    //     $search = $request->input('search');
    //     $catid = $request->input('cate_id');
    //     $books = Book::with('category_book:id,name')->whereCategoryBookId($catid);
    //     $books = $books->where('name', 'LIKE', "%{$search}%")
    //         ->orWhere('author', 'LIKE', "%{$search}%")
    //         ->orWhere('publisher', 'LIKE', "%{$search}%")
    //         ->orWhere('issn', 'LIKE', "%{$search}%")
    //         ->orWhere('year', 'LIKE', "%{$search}%")->paginate(6);
    //     return view('search-books', compact('books'));
    // }

    public function autocompletetypehead(Request $request)
    {
        $search = $request->search;
        $catid = $request->cate_id;
        $results = [];
        $books = Book::select('name')->where('category_book_id', $catid)->where(function ($qurey) use ($search) {
            return $qurey->where('issn', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->orWhere('author', 'LIKE', "%{$search}%")
                ->orWhere('publisher', 'LIKE', "%{$search}%")
                ->orWhere('year', 'LIKE', "%{$search}%")->get();
        })->get();
        foreach ($books as $query) {
            $results[] = [

                'id' => $query->id,
                'name' => $query->author,
                'name' => $query->name,
            ];
        }
        return response()->json($results);
    }

    function autocomplete(Request $request)
    {

        if ($request->ajax()) {
            $search = $request->search;

            if ($request->has('cate_id')) {
                $catid = $request->cate_id;

                $data = Book::select('name')->where('category_book_id', $catid)->where(function ($qurey) use ($search) {
                    return $qurey->where('issn', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%")
                        ->orWhere('author', 'LIKE', "%{$search}%")
                        ->orWhere('publisher', 'LIKE', "%{$search}%")
                        ->orWhere('year', 'LIKE', "%{$search}%")->get();
                })->get();
            } else {
                $data = Book::select('name')->where('issn', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('author', 'LIKE', "%{$search}%")
                    ->orWhere('publisher', 'LIKE', "%{$search}%")
                    ->orWhere('year', 'LIKE', "%{$search}%")->get();
            }




            if ($data->count() != 0) {
                $output = '<ul class="dropdown-menu" style="display:block; position:absolute;top: 42px; left: 4px;">';
                if ($data->count() >= 20) {

                    for ($i = 0; $i < 20; $i++) {

                        $output .= '
                        <li><a class="dropdown-item pointer-co" id="auto-complete">' . $data[$i]->name . '</a></li>';
                    }
                    $output .= '</ul>';
                    echo $output;
                } else {
                    foreach ($data as $row) {
                        $output .= '
                    <li><a class="dropdown-item pointer-co" id="auto-complete">'
                            . $row->name . '</a></li>';
                    }
                    $output .= '</ul>';
                    echo $output;
                }
            } else {
                echo '<ul class="dropdown-menu" 
                       style="display:block; position:absolute;top: 42px; left: 4px;">
                         <li>
                             <a class="dropdown-item pointer-co">' .
                    '<strong class="text-danger">' . $search . ' </strong>' . __('app.not_found') .
                    '</a>
                         </li>
                     </ul>';
            }
        }
    }





    public function update_books(Request $request)
    {


        $book = Book::find($request->id);
        if (!$book) {

            return redirect()->back()->withErrors('error', 'the is not found');
        } else {
            $book->update([
                'issn' => $request->issn,
                'name' => $request->name,
                'author' => $request->author,
                'publisher' => $request->publisher,
                'language' => $request->lang,
                'quantity' => $request->quantity,
                'status' => $request->status,
                'year' => $request->date_of_publish,
            ]);

            return redirect()->back()->with('success', 'book has been updated successfully');
        }
    }

    public function delete_book(Request $request)
    {
        $book = Book::find($request->book_id);

        if (!$book) {
            return redirect()->back()->withErrors('the is not found');
        }
        $book->delete();
        return redirect()->back()->with('deleted', 'book has been deleted successfully');
    }
    public function delete_category(Request $request)
    {
        $book = CategoryBook::find($request->book_id);

        if (!$book) {
            return redirect()->back()->withErrors('the is not found');
        }
        $book->delete();
        return redirect()->back()->with('deleted', 'category has been deleted successfully');
    }



    public function export_book(Request $request)
    {
        return  Excel::download(new BooksExport(), 'books.xlsx');
        // return (new BooksExport)->download('invoices.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    public function import_book(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'attachment' => 'required|mimes:xlsx,xls',
        ]);
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $file = $request->file('attachment');

        Excel::queueImport(new BooksImport(), $file);

        return redirect()->back()->with('message', 'importing started successfully');
    }

    public function test(Request $request)
    {
        if ($request->has('aa')) {
            Storage::putFile('test', new File($request->aa));
        }
        return back()->with('sss');
    }

}
