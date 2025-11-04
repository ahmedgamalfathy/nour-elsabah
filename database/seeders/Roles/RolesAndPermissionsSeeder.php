<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // premissions
        $permissions = [
            'all_users',
            'create_user',
            'edit_user',
            'update_user',
            'destroy_user',
            'change_user_status',

            'all_roles',
            'create_role',
            'edit_role',
            'update_role',
            'destroy_role',

            'all_categories',
            'create_category',
            'edit_category',
            'update_category',
            'destroy_category',

            'all_sub_categories',
            'create_sub_category',
            'edit_sub_category',
            'update_sub_category',
            'destroy_sub_category',

            'all_products',
            'create_product',
            'edit_product',
            'update_product',
            'destroy_product',

            'all_product_media',
            'create_product_media',
            'edit_product_media',
            'update_product_media',
            'destroy_product_media',

            'all_clients',
            'create_client',
            'edit_client',
            'update_client',
            'destroy_client',

            'all_orders',
            'create_order',
            'edit_order',
            'update_order',
            'destroy_order',

            'all_client_addresses',
            'create_client_address',
            'edit_client_address',
            'update_client_address',
            'destroy_client_address',

            'all_client_emails',
            'create_client_email',
            'edit_client_email',
            'update_client_email',
            'destroy_client_email',

            'all_client_phones',
            'create_client_phone',
            'edit_client_phone',
            'update_client_phone',
            'destroy_client_phone',

            // 'all_parameters',
            // 'create_parameter',
            // 'edit_parameter',
            // 'update_parameter',
            // 'destroy_parameter',

        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], [
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        // roles
        $superAdmin = Role::create(['name' => 'super admin']);
        $superAdmin->givePermissionTo(Permission::get());

        // $accountant = Role::create(['name' => 'مدير جمعية']);
        // $accountant->givePermissionTo([
        //     'all_users',
        //     'create_user',
        //     'edit_user',
        //     'update_user',
        //     'destroy_user',
        //     'change_user_status',

        //     'all_charity_cases',
        //     'create_charity_case',
        //     'edit_charity_case',
        //     'update_charity_case',
        //     'destroy_charity_case',

        //     'all_charity_case_documents',
        //     'create_charity_case_document',
        //     'edit_charity_case_document',
        //     'update_charity_case_document',
        //     'destroy_charity_case_document',

        //     'all_donations',
        //     'create_donation',
        //     'edit_donation',
        //     'update_donation',
        //     'destroy_donation',

        //     'all_roles',
        //     'create_role',
        //     'edit_role',
        //     'update_role',
        //     'destroy_role',

        //     'all_parameters',
        //     'create_parameter',
        //     'edit_parameter',
        //     'update_parameter',
        //     'destroy_parameter',
        // ]);

    }
}
