<?php

namespace Ultraware\Roles\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ultraware\Roles\Models\Permission;

trait RoleHasRelations
{
    /**
     * Role belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('roles.models.permission'))->withTimestamps();
    }

    /**
     * Role belongs to many users.
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(config('auth.providers.users.model'))->withTimestamps();
    }

    /**
     * Role belongs to parent role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(config('roles.models.role'),'parent_id');
    }

    public function ancestors()
    {
        $ancestors = $this->where('id', '=', $this->parent_id)->get();
        while ($ancestors->last() && $ancestors->last()->parent_id !== null)
        {
            $parent = $this->where('id', '=', $ancestors->last()->parent_id)->get();
            $ancestors = $ancestors->merge($parent);
        }
        return $ancestors;
    }

    /**
     * Role has many children roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(config('roles.models.role'),'parent_id');
    }

    public function descendants()
    {
        $descendants = $this->where('parent_id', '=', $this->id)->get();
        foreach($descendants as $descendant)
            $descendants = $descendants->merge($descendant->descendants());
        return $descendants;
    }

    /**
     * Attach permission to a role.
     *
     * @param int|Permission $permission
     * @return int|bool
     */
    public function attachPermission($permission)
    {
        return (!$this->permissions()->get()->contains($permission)) ? $this->permissions()->attach($permission) : true;
    }

    /**
     * Detach permission from a role.
     *
     * @param int|Permission $permission
     * @return int
     */
    public function detachPermission($permission)
    {
        return $this->permissions()->detach($permission);
    }

    /**
     * Detach all permissions.
     *
     * @return int
     */
    public function detachAllPermissions()
    {
        return $this->permissions()->detach();
    }

    /**
     * Sync permissions for a role.
     *
     * @param array|Permission[]|Collection $permissions
     * @return array
     */
    public function syncPermissions($permissions)
    {
        return $this->permissions()->sync($permissions);
    }
}
