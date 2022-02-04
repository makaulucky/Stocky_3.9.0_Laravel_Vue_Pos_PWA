<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sale;
use App\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sale $sale
     * @return mixed
     */
    public function view(User $user)
    {
        $permission = Permission::where('name', 'Sales_view')->first();
        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        $permission = Permission::where('name', 'Sales_add')->first();
        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sale $sale
     * @return mixed
     */
    public function update(User $user)
    {
        $permission = Permission::where('name', 'Sales_edit')->first();
        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sale $sale
     * @return mixed
     */
    public function delete(User $user)
    {
        $permission = Permission::where('name', 'Sales_delete')->first();
        return $user->hasRole($permission->roles);
    }

    public function Reports_sales(User $user)
    {
        $permission = Permission::where('name', 'Reports_sales')->first();
        return $user->hasRole($permission->roles);
    }

    public function Sales_pos(User $user)
    {
        $permission = Permission::where('name', 'Pos_view')->first();
        return $user->hasRole($permission->roles);
    }

    public function check_record(User $user, $sale)
    {
        return $user->id === $sale->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sale $sale
     * @return mixed
     */
    public function restore(User $user)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sale $sale
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        //
    }
}
