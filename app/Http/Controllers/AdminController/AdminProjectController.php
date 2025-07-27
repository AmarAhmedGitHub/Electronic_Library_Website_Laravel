<?php

namespace App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;
use App\Models\CategoryProject;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class AdminProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function get_cat_project()
    {

        $category_project = CategoryProject::withCount('projects')->get(); //->toQuery();
        //$category_book = $category_book->paginate(6);

        return view('category-project', compact('category_project'));
        
    }
    public function get_cat_project_ajax()
    {

        $category_project = CategoryProject::withCount('projects')->get(); //->toQuery();
        //$category_book = $category_book->paginate(6);

        return view('sections.category-project-card', compact('category_project'));
        
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
        CategoryProject::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['success' => 'category has been created successfully.']);
    }

    public function update_category(Request $request)
    {


        $category = CategoryProject::find($request->id);

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

            $category = CategoryProject::find($request->category_id);

            if (!$category) {
                return response()->json(['error' => __('app.not_found')]);
            }
            else{
                $projects = Project::whereCategoryProjectId($request->category_id)->get();
                if($projects){
                    foreach($projects as $project){
                        $this->deleteFile($project->file_path);
                    }
                }
        

                $category->delete();
                return response()->json(['success' => 'category has been deleted successfully.']);
            }
        }
    }



    public function get_e($cat_id)
    {
        $books = Project::select('id','name','author','publisher','path')->with('category_project:id,name');
        $books = $books->whereCategoryProjectId($cat_id)->get();
        //$books=$books->get();
        $catid = $cat_id;
        $bo = $books;
        foreach($bo as $b){
            $b->path=URL::to('/') . $b->path;
        }


        return $bo;
    }

    public function get_projects($cat_id)
    {
        $projects = Project::with('category_project:id,name');
        $projects = $projects->whereCategoryProjectId($cat_id)->paginate(6);
        //$books=$books->get();
        $catid = $cat_id;


        return view('projects', compact('projects', 'catid'));
    }
    public function get_projects_ajax($cat_id)
    {
        $projects = Project::with('category_project:id,name');
        $projects = $projects->whereCategoryProjectId($cat_id)->paginate(6);
        //$books=$books->get();
        $catid = $cat_id;


        return view('sections.card-projects', compact('projects', 'catid'))->render();
    }




    public function add_project(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'bname' => 'required|string',
            'supervisor' => 'required|string',
            'date' => 'required|date',
            'bookfile' => 'required|mimes:pdf',

        ]);
        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()->all()]);
        }

        $file = $request->file('bookfile');

        $path = $this->saveFile($file, 'project');



         Project::create([
            'name' => $request->bname,
            'supervisor' => $request->supervisor,
            'language' => $request->lang,
            'status' => $request->status,
            'year' => $request->date,
            'file_path' => $path,
            'category_project_id' => $request->category,
        ]);
        return response()->json(['success' => 'project has been created successfully.']);
    }

    public function update_projects(Request $request)
    {

        if ($request->ajax()) {
        $project = Project::find($request->cat_id);
        if (!$project) {

            return response()->json(['error' => __('app.not_found')]);
        } else {

            $validation = Validator::make($request->all(), [
                'bname' => 'required|string',
                'supervisor' => 'required|string',
                'date_of_publish' => 'required|date',

            ]);
            if ($validation->fails()) {
                return response()->json(['error' => $validation->errors()->all()]);
            }
            if ($request->hasFile('bookfile')) {

                $this->deleteFile($request->oldpath);

                $file = $request->file('bookfile');
                $path = $this->saveFile($file, 'project');

                $project->update([
                    'name' => $request->bname,
                    'supervisor' => $request->supervisor,
                    'language' => $request->lang,
                    'status' => $request->status,
                    'file_path' => $path,
                    'year' => $request->date_of_publish,
                ]);
            } else {


                $project->update([
                    'name' => $request->bname,
                    'supervisor' => $request->supervisor,
                    'language' => $request->lang,
                    'status' => $request->status,
                    'year' => $request->date_of_publish,
                ]);
            }


            return response()->json(['success' => 'project has been updated successfully.']);
        }
    }
    }

    public function delete_project(Request $request)
    {
        if ($request->ajax()) {
        $project = Project::find($request->id);

        if (!$project) {
            return response()->json(['error' => __('app.not_found')]);
        }
        $this->deleteFile($project->file_path);
        $project->delete();
        return response()->json(['success' => 'project has been deleted successfully.']);
    }
    }


    public function advanced_search_project(Request $request)
    {
        $search = $request->input('search');

        if($request->has('cate_id')){
            $catid = $request->input('cate_id');
            $projects = Project::with('category_Project:id,name')->whereCategoryProjectId($catid);
            $projects = $projects->where(function ($qurey) use ($search){
                return  $qurey->where('name', 'LIKE', "%{$search}%")
                ->orWhere('supervisor', 'LIKE', "%{$search}%")
                ->orWhere('year', 'LIKE', "%{$search}%")->get();
            })->paginate(6);

        }
        else{
             $projects = Project::with('category_project:id,name');
             $projects = $projects->where('name', 'LIKE', "%{$search}%")
            ->orWhere('supervisor', 'LIKE', "%{$search}%")
            ->orWhere('year', 'LIKE', "%{$search}%")->paginate(6);
        }
        
        //$books=$books->get();
        return view('search-projects', compact('projects'));

    }

    public function search_project(Request $request)
    {
        $search = $request->input('search');
        $catid = $request->input('cate_id');
        $projects = Project::with('category_project:id,name')->whereCategoryProjectId($catid);
        $projects = $projects->where('name', 'LIKE', "%{$search}%")
            ->orWhere('supervisor', 'LIKE', "%{$search}%")
            ->orWhere('year', 'LIKE', "%{$search}%")->paginate(6);
        
        return view('search-projects', compact('projects'));

    }

    function autocomplete(Request $request)
    {

        if ($request->ajax()) {
            $search = $request->search;

            if ($request->has('cate_id')) {
                $catid = $request->cate_id;

                $data = Project::select('name')->whereCategoryProjectId($catid)->where(function ($qurey) use ($search) {
                    return $qurey->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('supervisor', 'LIKE', "%{$search}%")
                        ->orWhere('year', 'LIKE', "%{$search}%");
                })->get();
            } else {
                $data = Project::select('name')->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('supervisor', 'LIKE', "%{$search}%")
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
}
