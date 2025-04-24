<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Contracts\Role as RoleContract;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Contracts\Permission as PermissionContract;
class CustomerRole extends Model implements RoleContract
{
    use HasPermissions;

    protected $guard_name = 'web';
    protected $table = 'customer_roles';

    public static function findByName(string $name, string $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::named($name, $guardName);
        }

        return $role;
    }

    public static function findById($id, string $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        $role = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::withId($id, $guardName);
        }

        return $role;
    }

    public static function findOrCreate(string $name, string $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $role;
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (is_string($permission)) {
            $permission = app(PermissionContract::class)->findByName($permission, $this->getDefaultGuardName()); // Use PermissionContract
        }

        return $this->permissions->contains('id', $permission->id);
    }
}
