<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SaleReturn;
use App\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleReturnPolicy
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
     * @param  \App\Models\SaleReturn  $SaleReturn
     * @return mixed
     */
    public function view(User $user)
    {
        $permission = Permission::where('name', 'Sale_Returns_view')->first();
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
        $permission = Permission::where('name', 'Sale_Returns_add')->first();
        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SaleReturn  $SaleReturn
     * @return mixed
     */
    public function update(User $user)
    {
        $permission = Permission::where('name', 'Sale_Returns_edit')->first();
        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SaleReturn  $SaleReturn
     * @return mixed
     */
    public function delete(User $user)
    {
        $permission = Permission::where('name', 'Sale_Returns_delete')->first();
        return $user->hasRole($permission->roles);
    }

    public function check_record(User $user, $SaleReturn)
    {
        return $user->id === $SaleReturn->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SaleReturn  $SaleReturn
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
     * @param  \App\Models\SaleReturn  $SaleReturn
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        //
    }
}
