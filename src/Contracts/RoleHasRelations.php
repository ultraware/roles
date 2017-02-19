<?php

namespace Ultraware\Roles\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ultraware\Roles\Models\Permission;


interface RoleHasRelations
{
    /**
     * Role belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions();

    /**
     * Role belongs to many users.
     *
     * @return BelongsToMany
     */
    public function users();

    /**
     * Role belongs to parent role.
     *
     * @return BelongsTo
     */
    public function parent();
    /**
     * Roles parent, grand parent, etc.
     *
     * @return Collection
     */
    public function ancestors();
    /**
     * Role has many children roles
     *
     * @return HasMany
     */
    public function children();

    /**
     * Roles children, grand children, etc.
     *
     * @return Collection
     */
    public function descendants();

    /**
     * Attach permission to a role.
     *
     * @param int|Permission $permission
     * @return int|bool
     */
    public function attachPermission($permission);

    /**
     * Detach permission from a role.
     *
     * @param int|Permission $permission
     * @return int
     */
    public function detachPermission($permission);

    /**
     * Detach all permissions.
     *
     * @return int
     */
    public function detachAllPermissions();

    /**
     * Sync permissions for a role.
     *
     * @param array|Permission[]|Collection $permissions
     * @return array
     */
    public function syncPermissions($permissions);
}
