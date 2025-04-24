<?php

namespace Database\Seeders;

use App\Enums\CustomerRole as CustomerRoleEnum;
use App\Models\CustomerRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Staff permission
        $manageUsers = Permission::create(['name' => 'manage users']);
        $importUsers = Permission::create(['name' => 'import users']);
        $importCredit = Permission::create(['name' => 'import credit']);

        //customers permissions
        $haveCash = Permission::create(['name' => 'have cash']);
        $haveCredit = Permission::create(['name' => 'have credit']);
        $splitPayments = Permission::create(['name' => 'split payments']);
        $makePurchase = Permission::create(['name' => 'make purchase']);
        //staff roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([$manageUsers, $importUsers, $importCredit]);

        //customer
        CustomerRole::create(['name' => CustomerRoleEnum::PUBLIC])->givePermissionTo([$makePurchase]);
        CustomerRole::create(['name' => CustomerRoleEnum::BENEFICIARIES])->givePermissionTo([$makePurchase,
            $haveCredit]);
        CustomerRole::create(['name' => CustomerRoleEnum::COPAYMENT])->givePermissionTo([$makePurchase,$haveCredit,
            $haveCash, $splitPayments]);
    }
}
