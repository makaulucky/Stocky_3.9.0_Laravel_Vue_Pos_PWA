<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryExpenseController extends BaseController
{

    //-------------- Get All Expense Categories ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ExpenseCategory::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        // Check If User Has Permission View  All Records
        $ExpenseCategory = ExpenseCategory::where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('description', 'LIKE', "%{$request->search}%");
                });
            });

        $totalRows = $ExpenseCategory->count();
        $ExpenseCategory = $ExpenseCategory->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'Expenses_category' => $ExpenseCategory,
            'totalRows' => $totalRows,
        ]);

    }

    //-------------- Store New Category ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', ExpenseCategory::class);

        request()->validate([
            'name' => 'required',
        ]);

        ExpenseCategory::create([
            'user_id' => Auth::user()->id,
            'description' => $request['description'],
            'name' => $request['name'],
        ]);

        return response()->json(['success' => true], 200);
    }

    //------------ function show -----------\\

    public function show($id){
    //
    
    }

    //-------------- Update Category ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', ExpenseCategory::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $ExpenseCategory = ExpenseCategory::findOrFail($id);

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === ExpenseCategory->id
            $this->authorizeForUser($request->user('api'), 'check_record', $ExpenseCategory);
        }

        request()->validate([
            'name' => 'required',
        ]);

        $ExpenseCategory->update([
            'name' => $request['name'],
            'description' => $request['description'],
        ]);

        return response()->json(['success' => true], 200);

    }

    //-------------- Delete Category ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', ExpenseCategory::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $ExpenseCategory = ExpenseCategory::findOrFail($id);

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === ExpenseCategory->id
            $this->authorizeForUser($request->user('api'), 'check_record', $ExpenseCategory);
        }
        $ExpenseCategory->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true], 200);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', ExpenseCategory::class);
        $selectedIds = $request->selectedIds;
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        foreach ($selectedIds as $category_id) {
            $ExpenseCategory = ExpenseCategory::findOrFail($category_id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === ExpenseCategory->id
                $this->authorizeForUser($request->user('api'), 'check_record', $ExpenseCategory);
            }
            $ExpenseCategory->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true], 200);
    }

}
