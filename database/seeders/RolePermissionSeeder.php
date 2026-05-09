<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = [
            ['id' => 1, 'name' => 'Super Admin', 'slug' => 'superadmin', 'description' => 'Full access ke semua fitur', 'is_system' => true],
            ['id' => 2, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'Akses ke sebagian besar fitur admin', 'is_system' => true],
            ['id' => 3, 'name' => 'Customer Service', 'slug' => 'cs', 'description' => 'Akses chat dan lihat customer', 'is_system' => true],
            ['id' => 4, 'name' => 'Sales', 'slug' => 'sales', 'description' => 'Akses chat, customer, dan funnel', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }

        // 2. Create Permissions
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'Dashboard', 'description' => 'Akses halaman dashboard'],
            // Chat
            ['name' => 'View Chat', 'slug' => 'chat.view', 'module' => 'Chat', 'description' => 'Akses halaman chat'],
            ['name' => 'Send Message', 'slug' => 'chat.send', 'module' => 'Chat', 'description' => 'Kirim pesan ke customer'],
            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view', 'module' => 'Customers', 'description' => 'Lihat daftar customer'],
            ['name' => 'Create Customer', 'slug' => 'customers.create', 'module' => 'Customers', 'description' => 'Tambah customer baru'],
            ['name' => 'Edit Customer', 'slug' => 'customers.edit', 'module' => 'Customers', 'description' => 'Edit data customer'],
            ['name' => 'Delete Customer', 'slug' => 'customers.delete', 'module' => 'Customers', 'description' => 'Hapus customer'],
            // Companies
            ['name' => 'View Companies', 'slug' => 'companies.view', 'module' => 'Companies', 'description' => 'Lihat daftar perusahaan'],
            ['name' => 'Manage Companies', 'slug' => 'companies.manage', 'module' => 'Companies', 'description' => 'Kelola perusahaan'],
            // Labels
            ['name' => 'View Labels', 'slug' => 'labels.view', 'module' => 'Labels', 'description' => 'Lihat daftar label'],
            ['name' => 'Manage Labels', 'slug' => 'labels.manage', 'module' => 'Labels', 'description' => 'Kelola label'],
            // Deals
            ['name' => 'View Deals', 'slug' => 'deals.view', 'module' => 'Deals', 'description' => 'Lihat status deal'],
            ['name' => 'Manage Deals', 'slug' => 'deals.manage', 'module' => 'Deals', 'description' => 'Kelola status deal'],
            // Auto Replies
            ['name' => 'View Auto Replies', 'slug' => 'auto_replies.view', 'module' => 'Auto Replies', 'description' => 'Lihat auto reply'],
            ['name' => 'Manage Auto Replies', 'slug' => 'auto_replies.manage', 'module' => 'Auto Replies', 'description' => 'Kelola auto reply'],
            // Broadcasts
            ['name' => 'View Broadcasts', 'slug' => 'broadcasts.view', 'module' => 'Broadcasts', 'description' => 'Lihat daftar broadcast'],
            ['name' => 'Manage Broadcasts', 'slug' => 'broadcasts.manage', 'module' => 'Broadcasts', 'description' => 'Kelola broadcast'],
            // Templates
            ['name' => 'View Templates', 'slug' => 'templates.view', 'module' => 'Templates', 'description' => 'Lihat template chat'],
            ['name' => 'Manage Templates', 'slug' => 'templates.manage', 'module' => 'Templates', 'description' => 'Kelola template chat'],
            // Promotions
            ['name' => 'View Promotions', 'slug' => 'promotions.view', 'module' => 'Promotions', 'description' => 'Lihat promosi'],
            ['name' => 'Manage Promotions', 'slug' => 'promotions.manage', 'module' => 'Promotions', 'description' => 'Kelola promosi'],
            // Import
            ['name' => 'Access Import', 'slug' => 'import.access', 'module' => 'Import', 'description' => 'Akses fitur import data'],
            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'Reports', 'description' => 'Lihat laporan'],
            ['name' => 'Manage Reports', 'slug' => 'reports.manage', 'module' => 'Reports', 'description' => 'Kelola laporan'],
            // API
            ['name' => 'View API', 'slug' => 'api.view', 'module' => 'API', 'description' => 'Lihat dokumentasi API'],
            ['name' => 'Manage API Keys', 'slug' => 'api.manage', 'module' => 'API', 'description' => 'Kelola API keys'],
            ['name' => 'View API Logs', 'slug' => 'api.logs', 'module' => 'API', 'description' => 'Lihat log API'],
            // Users
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'Users', 'description' => 'Lihat daftar pengguna'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'module' => 'Users', 'description' => 'Kelola pengguna'],
            // Roles
            ['name' => 'View Roles', 'slug' => 'roles.view', 'module' => 'Roles', 'description' => 'Lihat daftar role'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'module' => 'Roles', 'description' => 'Kelola role dan permissions'],
            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'Settings', 'description' => 'Lihat pengaturan'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'module' => 'Settings', 'description' => 'Ubah pengaturan'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        // 3. Assign Permissions to Roles
        $superAdmin = Role::where('slug', 'superadmin')->first();
        $admin = Role::where('slug', 'admin')->first();
        $cs = Role::where('slug', 'cs')->first();
        $sales = Role::where('slug', 'sales')->first();

        // Super Admin: All
        $superAdmin->permissions()->sync(Permission::all());

        // Admin: All except users.*, roles.*, api.*
        $adminPermissions = Permission::where('slug', 'not like', 'users.%')
            ->where('slug', 'not like', 'roles.%')
            ->where('slug', 'not like', 'api.%')
            ->get();
        $admin->permissions()->sync($adminPermissions);

        // CS: Specific
        $csPermissions = Permission::whereIn('slug', ['dashboard.view', 'chat.view', 'chat.send', 'customers.view'])->get();
        $cs->permissions()->sync($csPermissions);

        // Sales: Specific
        $salesPermissions = Permission::whereIn('slug', ['dashboard.view', 'chat.view', 'chat.send', 'customers.view', 'deals.view'])->get();
        $sales->permissions()->sync($salesPermissions);

        // 4. Update existing users role_id
        User::where('role', 'superadmin')->update(['role_id' => 1]);
        User::where('role', 'admin')->update(['role_id' => 2]);
        User::where('role', 'cs')->update(['role_id' => 3]);
        User::where('role', 'sales')->update(['role_id' => 4]);
    }
}
