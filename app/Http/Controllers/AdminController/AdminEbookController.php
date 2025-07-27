<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Imports\EBooksImport;
use App\Models\CategoryEbook;
use App\Models\EBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AdminEbookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function get_cat_ebook()
    {

        $category_book = CategoryEbook::withCount('ebooks')->get(); //->toQuery();
        //$category_book = $category_book->paginate(6);

        return view('category-ebook', compact('category_book'));
    }


    public function get_cat_book_ajax()
    {
        $category_book = CategoryEbook::withCount('ebooks')->get();//->toQuery();
        //$category_book = $category_book->paginate(6);
        return view('sections.category-ebook-card', compact('category_book'));
    }


    public function addcategory(Request $request)
    {
        $attrs = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string'
        ]);
        if ($attrs->fails()) {
            return response()->json(['error' => $attrs->errors()->all()]);
        }
        CategoryEbook::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['success' => 'category has been created successfully.']);
    }

    public function update_category(Request $request)
    {


        $category = CategoryEbook::find($request->id);
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
    public function delete_categorys(Request $request)
    {
        if ($request->ajax()) {

            $category = CategoryEbook::find($request->category_id);

            if (!$category) {
                return response()->json(['error' => __('app.not_found')]);
            } else {
                $books = EBook::whereCategoryEbookId($request->category_id)->get();
                if ($books) {
                    foreach ($books as $book) {
                        $this->deleteFile($book->path);
                    }
                }

                $category->delete();
                return response()->json(['success' => 'category has been deleted successfully.']);
            }
        }
    }



    public function get_e($cat_id)
    {
        $books = EBook::select('id', 'name', 'author', 'publisher', 'path')->with('category_ebook:id,name');
        $books = $books->whereCategoryEbookId($cat_id)->get();
        //$books=$books->get();
        $catid = $cat_id;
        $bo = $books;
        foreach ($bo as $b) {
            $b->path = URL::to('/') . $b->path;
        }


        return $bo;
    }

    public function get_ebooks($cat_id)
    {
        $books = EBook::with('category_ebook:id,name');
        $books = $books->whereCategoryEbookId($cat_id)->paginate(6);
        //$books=$books->get();
        $catid = $cat_id;


        return view('ebooks', compact('books', 'catid'));
    }

    public function get_ebook_ajax($cat_id)
    {
        $books = EBook::with('category_ebook:id,name');
        $books = $books->whereCategoryEbookId($cat_id)->paginate(6);
        //$books=$books->get();
        $catid = $cat_id;

        return view('sections.card-ebooks', compact('books'))->render();
    }

    public function add_ebook(Request $request)
    {
        if ($request->ajax()) {

            $validation = Validator::make($request->all(), [
                'bname' => 'required|string',
                'issn' => 'string|unique:e_books',
                'author' => 'required|string',
                'publisher' => 'required|string',
                'bookfile' => 'required|mimes:pdf',

            ]);
            if ($validation->fails()) {
                return response()->json(['error' => $validation->errors()->all()]);
            }

            $file = $request->file('bookfile');
            $path = $this->saveFile($file, 'book');

            EBook::create([
                'issn' => $request->issn,
                'name' => $request->bname,
                'author' => $request->author,
                'publisher' => $request->publisher,
                'language' => $request->lang,
                'status' => $request->status,
                'year' => $request->date,
                'path' => $path,
                'category_ebook_id' => $request->category,
            ]);
            return response()->json(['success' => 'book has been created successfully.']);
        }
    }

    public function update_ebooks(Request $request)
    {
        if ($request->ajax()) {

            $book = EBook::find($request->cat_id);
            if (!$book) {

                return response()->json(['error' => __('app.not_found')]);
            } else {

                $validation = Validator::make($request->all(), [
                    'bname' => 'required|string',
                    'author' => 'required|string',
                    'publisher' => 'required|string',

                ]);
                if ($validation->fails()) {
                    return response()->json(['error' => $validation->errors()->all()]);
                }

                if ($request->hasFile('bookfile')) {

                    $this->deleteFile($request->oldpath);

                    $file = $request->file('bookfile');
                    $path = $this->saveFile($file, 'book');

                    $book->update([
                        'issn' => $request->issn,
                        'name' => $request->bname,
                        'author' => $request->author,
                        'publisher' => $request->publisher,
                        'language' => $request->lang,
                        'status' => $request->status,
                        'path' => $path,
                        'year' => $request->date_of_publish,
                    ]);
                } else {
                    $book->update([
                        'issn' => $request->issn,
                        'name' => $request->bname,
                        'author' => $request->author,
                        'publisher' => $request->publisher,
                        'language' => $request->lang,
                        'status' => $request->status,
                        'year' => $request->date_of_publish,
                    ]);
                }
                return response()->json(['success' => 'book has been updated successfully.']);
            }
        }
    }

    public function delete_book(Request $request)
    {
        if ($request->ajax()) {
            $book = EBook::find($request->id);

            if (!$book) {
                return response()->json(['error' => __('app.not_found')]);
            }
            $this->deleteFile($book->path);
            $book->delete();
            return response()->json(['success' => 'book has been deleted successfully.']);
        }
    }


    public function advanced_search_ebook(Request $request)
    {
        $search = $request->input('search');

        if($request->has('cate_id')){
            $catid = $request->input('cate_id');
            $books = EBook::with('category_ebook:id,name')->whereCategoryEbookId($catid);
            $books=$books->where(function ($qurey) use ($search) {
                return $qurey->where('issn', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('author', 'LIKE', "%{$search}%")
                    ->orWhere('publisher', 'LIKE', "%{$search}%")
                    ->orWhere('year', 'LIKE', "%{$search}%")->get();
            })->paginate(6);

        }

        else{
            
         
           $books = EBook::with('category_ebook:id,name');
           $books = $books->where('name', 'LIKE', "%{$search}%")
            ->orWhere('author', 'LIKE', "%{$search}%")
            ->orWhere('publisher', 'LIKE', "%{$search}%")
            ->orWhere('issn', 'LIKE', "%{$search}%")
            ->orWhere('year', 'LIKE', "%{$search}%")->paginate(6);
        }
        return view('search-ebooks', compact('books'));
    }

    public function search_ebook(Request $request)
    {
        $search = $request->input('search');
        $catid = $request->input('cate_id');
        $books = EBook::with('category_ebook:id,name')->whereCategoryEbookId($catid);
        $books = $books->where('name', 'LIKE', "%{$search}%")
            ->orWhere('author', 'LIKE', "%{$search}%")
            ->orWhere('publisher', 'LIKE', "%{$search}%")
            ->orWhere('issn', 'LIKE', "%{$search}%")
            ->orWhere('year', 'LIKE', "%{$search}%")->paginate(6);
        return view('search-ebooks', compact('books'));
    }

    function autocomplete(Request $request)
    {

        if ($request->ajax()) {
            $search = $request->search;

            if ($request->has('cate_id')) {
                $catid = $request->cate_id;

                $data = EBook::select('name')->where('category_ebook_id', $catid)->where(function ($qurey) use ($search) {
                    return $qurey->where('issn', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%")
                        ->orWhere('author', 'LIKE', "%{$search}%")
                        ->orWhere('publisher', 'LIKE', "%{$search}%")
                        ->orWhere('year', 'LIKE', "%{$search}%")->get();
                })->get();
            } else {
                $data = EBook::select('name')->where('issn', 'LIKE', "%{$search}%")
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
                }

                else{
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

    public function import_ebook(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'attachment' => 'required|mimes:xlsx,xls',
        ]);
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $file = $request->file('attachment');

        Excel::queueImport(new EBooksImport(), $file);

        return redirect()->back()->with('message', 'importing started successfully');
    }
}