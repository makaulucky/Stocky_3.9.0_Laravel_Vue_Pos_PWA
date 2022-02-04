<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\utils\helpers;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class PermissionsController extends BaseController
{

    //----------- GET ALL Roles --------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Role::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();

        $roles = Role::where('deleted_at', '=', null)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('description', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $roles->count();
        $roles = $roles->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'roles' => $roles,
            'totalRows' => $totalRows,
        ]);
    }

    //----------- Store new Role --------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Role::class);

        try {
            request()->validate([
                'role.name' => 'required',
            ]);

            \DB::transaction(function () use ($request) {

                //-- Create New Role
                $Role = new Role;
                $Role->name = $request['role']['name'];
                $Role->label = $request['role']['name'];
                $Role->status = 0;
                $Role->description = $request['role']['description'];
                $Role->save();

                $role = Role::findOrFail($Role->id);
                $role->permissions()->detach();
                $permissions = $request->permissions;

                foreach ($permissions as $permission_slug) {

                    //get the permission object by name
                    $perm = Permission::where('name', $permission_slug)->first();
                    if ($perm) {
                        $data[] = $perm->id;
                    }
                }

                $role->permissions()->attach($data);

            }, 10);

            return response()->json(['success' => true]);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => 422,
                'msg' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

    }

    //------------ function show -----------\\

    public function show($id){
        //
        
        }

    //----------- Update Role --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Role::class);

        try {
            request()->validate([
                'role.name' => 'required',
            ]);

            \DB::transaction(function () use ($request, $id) {

                Role::whereId($id)->update($request['role']);

                $role = Role::findOrFail($id);
                $role->permissions()->detach();
                $permissions = $request->permissions;

                foreach ($permissions as $permission_slug) {

                    //get the permission object by name
                    $perm = Permission::where('name', $permission_slug)->first();
                    if ($perm) {
                        $data[] = $perm->id;
                    }
                }

                $role->permissions()->attach($data);

            }, 10);

            return response()->json(['success' => true]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'msg' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

    }

    //----------- Delete Role --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Role::class);

        Role::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'delete', Role::class);

        $selectedIds = $request->selectedIds;
        foreach ($selectedIds as $role_id) {
            Role::whereId($role_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true]);
    }

    //----------- Check Create Page --------------\\
    public function Check_Create_Page(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Role::class);
    }

    //----------- GET ALL Roles without paginate --------------\\

    public function getRoleswithoutpaginate()
    {
        $roles = Role::where('deleted_at', null)->get(['id', 'name']);
        return response()->json($roles);
    }

    //------------- Show Form Edit Permissions -----------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Role::class);

        $Role = Role::with('permissions')->where('deleted_at', '=', null)->findOrFail($id);
        if ($Role) {
            $item['name'] = $Role->name;
            $item['description'] = $Role->description;
            $data = [];
            if ($Role) {
                foreach ($Role->permissions as $permission) {
                    $data[] = $permission->name;
                }
            }
        }
        return response()->json([
            'permissions' => $data,
            'role' => $item,
        ]);
    }

}
