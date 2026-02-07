# Role-Based Access Control (RBAC)

## Overview

The RBAC system controls **who can do what** in GoPOS. Instead of managing permissions for each user individually, you create roles (like "Cashier" or "Manager") and assign them to users. This makes security management simple and scalable.

---

## Why Use RBAC?

### Key Benefits

| Benefit | How It Helps |
|---------|--------------|
| **Security** | Users only see what they need - no more, no less |
| **Simplicity** | Manage roles once, apply to many users |
| **Compliance** | Clear audit trail of who can access what |
| **Flexibility** | Create custom roles for your specific business needs |
| **Trilingual** | Role names in English, Arabic, and Kurdish |

### Problems It Solves

- **"My cashiers can see accounting data"** - Restrict module access by role
- **"Everyone has admin access"** - Create limited roles for each job function
- **"I can't track who changed what"** - Permissions create accountability
- **"New employee setup takes forever"** - Assign a role and they're ready

---

## Who Should Read This?

| Role | Why Read This |
|------|---------------|
| **System Administrators** | You manage users and security |
| **Business Owners** | You decide who has access to what |
| **IT Staff** | You implement security policies |
| **Developers** | You need to check permissions in code |

---

## Use Cases

### Use Case 1: Retail Store with Multiple Cashiers

**Scenario:** You have 5 cashiers and 2 managers. Cashiers should only process sales, while managers can do refunds and view reports.

**Solution:**
1. Use the built-in `Cashier` role for cashiers (POS access only)
2. Use the built-in `Manager` role for managers (POS + Reports + Limited accounting)
3. Assign roles when creating user accounts

**Benefit:** Cashiers cannot accidentally void sales or access sensitive reports.

---

### Use Case 2: Accounting Team Access

**Scenario:** Your accountant needs full access to financial data but should never process sales.

**Solution:**
1. Use the built-in `Accountant` role
2. This role has full accounting access but no POS permissions

**Benefit:** Separation of duties - sales and finance are independent.

---

### Use Case 3: Warehouse Staff

**Scenario:** Warehouse workers need to manage inventory but shouldn't see sales or financial data.

**Solution:**
1. Use the built-in `Warehouse Staff` role
2. This role can only access inventory functions

**Benefit:** Warehouse operations don't interfere with sales or accounting.

---

### Use Case 4: Custom Role for Supervisor

**Scenario:** You need a shift supervisor who can do refunds but not view reports.

**Solution:**
1. Create a new custom role "Shift Supervisor"
2. Select only the permissions needed: `pos.access`, `pos.sell`, `pos.refund`
3. Assign to supervisor users

**Benefit:** Precise control over exactly what each position can do.

---

## Features

### Feature 1: Default Roles

**What it does:** GoPOS comes with 7 pre-configured roles that cover most business needs.

**When to use:** Use these as-is for standard business setups, or as templates for custom roles.

**Default Roles:**

| Role | Best For | What They Can Do |
|------|----------|------------------|
| **Super Admin** | Business owners, IT admins | Everything - full system access |
| **Manager** | Store managers, supervisors | POS, inventory, reports, limited accounting |
| **Accountant** | Finance staff | Full accounting and financial access |
| **Cashier** | Sales staff | POS operations only |
| **Warehouse Staff** | Inventory workers | Inventory management only |
| **HR Manager** | HR department heads | Full HR access |
| **HR Staff** | HR assistants | Limited HR (attendance, leave) |

**How to view default roles:**
1. Navigate to **Settings > Roles**
2. View all roles with their permission counts
3. Click any role to see its permissions

---

### Feature 2: Permissions by Module

**What it does:** Permissions are organized by module, making it easy to grant access to specific areas.

**When to use:** When creating custom roles or reviewing what each role can do.

**Available Modules:**

#### POS Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `pos.access` | Open the POS interface | Cashier, Manager |
| `pos.sell` | Process sales | Cashier, Manager |
| `pos.refund` | Process refunds | Manager |
| `pos.discount` | Apply discounts | Manager |
| `pos.hold` | Hold/recall sales | Cashier, Manager |
| `pos.reports` | View POS reports | Manager |

#### Inventory Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `inventory.view` | View inventory | Warehouse Staff, Manager |
| `inventory.create` | Add products | Warehouse Staff |
| `inventory.edit` | Edit products | Warehouse Staff |
| `inventory.delete` | Delete products | Manager |
| `inventory.adjust` | Adjust stock quantities | Warehouse Staff |
| `inventory.transfer` | Transfer between warehouses | Warehouse Staff |

#### Sales Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `sales.view` | View sales history | Manager, Accountant |
| `sales.create` | Create sales | Cashier, Manager |
| `sales.edit` | Edit sales | Manager |
| `sales.delete` | Delete sales | Super Admin |
| `sales.export` | Export sales data | Manager, Accountant |

#### Purchases Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `purchases.view` | View purchases | Warehouse Staff, Manager |
| `purchases.create` | Create purchase orders | Warehouse Staff |
| `purchases.edit` | Edit purchases | Manager |
| `purchases.delete` | Delete purchases | Super Admin |
| `purchases.approve` | Approve purchases | Manager |

#### Customers Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `customers.view` | View customer list | Cashier, Manager |
| `customers.create` | Add customers | Cashier, Manager |
| `customers.edit` | Edit customer info | Manager |
| `customers.delete` | Delete customers | Manager |
| `customers.credit` | Manage customer credit | Manager, Accountant |

#### Suppliers Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `suppliers.view` | View supplier list | Warehouse Staff |
| `suppliers.create` | Add suppliers | Manager |
| `suppliers.edit` | Edit suppliers | Manager |
| `suppliers.delete` | Delete suppliers | Super Admin |

#### Accounting Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `accounting.view` | View accounting data | Accountant |
| `accounting.entries` | Create journal entries | Accountant |
| `accounting.approve` | Approve entries | Accountant, Manager |
| `accounting.reports` | View financial reports | Accountant, Manager |
| `accounting.settings` | Manage chart of accounts | Accountant |
| `accounting.close_period` | Close fiscal periods | Accountant |

#### HR Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `hr.view` | View HR data | HR Staff, Manager |
| `hr.employees` | Manage employees | HR Manager |
| `hr.payroll` | Process payroll | HR Manager |
| `hr.attendance` | Manage attendance | HR Staff |
| `hr.leave` | Manage leave requests | HR Staff |
| `hr.reports` | View HR reports | HR Manager |

#### Reports Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `reports.sales` | View sales reports | Manager |
| `reports.purchases` | View purchase reports | Manager |
| `reports.inventory` | View inventory reports | Warehouse Staff, Manager |
| `reports.financial` | View financial reports | Accountant |
| `reports.hr` | View HR reports | HR Manager |
| `reports.export` | Export any report | Manager |

#### Settings Permissions
| Permission | What It Allows | Typical Roles |
|------------|----------------|---------------|
| `settings.general` | General settings | Super Admin |
| `settings.users` | User management | Super Admin |
| `settings.roles` | Role management | Super Admin |
| `settings.company` | Company settings | Super Admin |
| `settings.integrations` | Integration settings | Super Admin |
| `settings.backup` | Backup & restore | Super Admin |

---

### Feature 3: Custom Role Creation

**What it does:** Create roles tailored to your specific business needs.

**When to use:** When default roles don't match your requirements.

**How to create a custom role:**

1. Navigate to **Settings > Roles**
2. Click **Create Role**
3. Fill in the details:
   - **Name (English):** Required - e.g., "Shift Supervisor"
   - **Name (Arabic):** Optional - e.g., "مشرف الوردية"
   - **Name (Kurdish):** Optional - e.g., "سەرپەرشتیاری شیفت"
   - **Description:** What this role is for
4. Check the permissions this role needs (grouped by module)
5. Click **Create**

**Business Example:**

Creating a "Senior Cashier" role:
- Can do everything a Cashier does
- Plus: Can apply discounts (`pos.discount`)
- Plus: Can view daily sales reports (`reports.sales`)

---

### Feature 4: Role Assignment

**What it does:** Link users to their roles.

**When to use:** When creating new users or changing someone's responsibilities.

**How to assign roles (via UI):**

1. Navigate to **Settings > Users**
2. Create new user or edit existing
3. In the **Roles** section, check one or more roles
4. Save the user

**How to assign roles (via code):**

```php
use App\Models\User;
use App\Models\Role;

// Get the user
$user = User::find(1);

// Assign a single role by name
$user->assignRole('manager');

// Assign multiple roles
$user->assignRole(['manager', 'accountant']);

// Using role model
$role = Role::findByName('cashier');
$user->assignRole($role);
```

**Business Example:**

Ahmed starts as a Cashier, then gets promoted:
1. Originally: Assign `Cashier` role
2. After promotion: Add `Manager` role (he now has both)
3. Or: Remove `Cashier`, add `Manager` (replace role)

---

### Feature 5: Permission Checking

**What it does:** Verify users have required access before allowing actions.

**When to use:** In code, templates, and custom features.

**How to check permissions in PHP:**

```php
// Check if user has a specific role
if ($user->hasRole('manager')) {
    // Show manager features
}

// Check if user has any of these roles
if ($user->hasRole(['manager', 'accountant'])) {
    // Show features for managers OR accountants
}

// Check if user has a specific permission
if ($user->hasPermission('pos.refund')) {
    // Show refund button
}

// Check if user has ALL of these permissions
if ($user->hasAllPermissions(['pos.sell', 'pos.refund'])) {
    // User can sell AND refund
}

// Get all user permissions
$permissions = $user->getAllPermissions();
```

**How to check permissions in Blade templates:**

```blade
@if(auth()->user()->hasRole('manager'))
    <a href="/reports">View Reports</a>
@endif

@if(auth()->user()->hasPermission('pos.discount'))
    <button>Apply Discount</button>
@endif
```

**How to check in Filament Resources:**

```php
public static function canViewAny(): bool
{
    return auth()->user()->hasPermission('sales.view');
}

public static function canCreate(): bool
{
    return auth()->user()->hasPermission('sales.create');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->hasPermission('sales.edit');
}

public static function canDelete(Model $record): bool
{
    return auth()->user()->hasPermission('sales.delete');
}
```

---

## Best Practices

### Do's

| Practice | Why |
|----------|-----|
| Start with default roles | They cover most common scenarios |
| Use least privilege | Give minimum permissions needed |
| Review roles quarterly | Job responsibilities change |
| Document custom roles | So others understand their purpose |
| Test after changes | Verify users can still do their jobs |

### Don'ts

| Avoid | Why |
|-------|-----|
| Giving everyone Super Admin | Defeats the purpose of RBAC |
| Too many custom roles | Hard to maintain - use defaults when possible |
| Permission creep | Remove permissions when no longer needed |
| Sharing accounts | Each user should have their own account |

---

## Technical Reference

### API Reference

#### Role Model

```php
use App\Models\Role;

// Find by name
$role = Role::findByName('manager');

// Get localized name (based on current locale)
$name = $role->localizedName;

// Get all permissions for this role
$permissions = $role->permissions;

// Check if system role (cannot be deleted)
if ($role->is_system) {
    // Protected role
}
```

#### Permission Model

```php
use App\Models\Permission;

// Find by name
$permission = Permission::findByName('pos.sell');

// Get by module
$posPermissions = Permission::where('module', 'pos')->get();

// Get localized name
$name = $permission->localizedName;
```

#### PermissionService

```php
use App\Services\PermissionService;

$service = new PermissionService();

// Get all permissions with translations
$permissions = $service->getAllPermissions();

// Seed permissions (run once during setup)
$service->seedPermissions();

// Seed default roles
$service->seedDefaultRoles();
```

### Database Schema

#### roles table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Unique role code (e.g., 'manager') |
| name_ar | string | Arabic display name |
| name_ckb | string | Kurdish display name |
| description | text | Role description |
| description_ar | text | Arabic description |
| description_ckb | text | Kurdish description |
| is_system | boolean | If true, cannot be deleted |
| created_at | timestamp | When created |
| updated_at | timestamp | Last modified |

#### permissions table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Unique permission code (e.g., 'pos.sell') |
| name_ar | string | Arabic display name |
| name_ckb | string | Kurdish display name |
| description | text | What this permission allows |
| description_ar | text | Arabic description |
| description_ckb | text | Kurdish description |
| module | string | Module grouping (pos, sales, etc.) |
| created_at | timestamp | When created |
| updated_at | timestamp | Last modified |

#### role_has_permissions table

| Column | Type | Description |
|--------|------|-------------|
| role_id | bigint | Foreign key to roles |
| permission_id | bigint | Foreign key to permissions |

#### model_has_roles table

| Column | Type | Description |
|--------|------|-------------|
| role_id | bigint | Foreign key to roles |
| model_type | string | Always 'App\Models\User' |
| model_id | bigint | User ID |

---

## Troubleshooting

### User has role but can't access feature

**Problem:** User is assigned a role but still can't see/do something.

**Solutions:**
1. Verify the role has the required permission (Settings > Roles > Edit role)
2. Check if permission check is correctly implemented in code
3. Clear application cache: `php artisan optimize:clear`
4. Ensure user's session is refreshed (log out and back in)

---

### Permission not appearing in list

**Problem:** A permission you need doesn't exist.

**Solutions:**
1. Run the permission seeder:
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```
2. Check `PermissionService` for the permission definition
3. Add the permission if it's for a custom feature

---

### Cannot delete a role

**Problem:** Delete button is disabled or deletion fails.

**Solution:** The role is marked as a system role (`is_system = true`). System roles are protected and cannot be deleted to prevent accidental security issues.

**Workaround:** Edit the role to remove unwanted permissions instead of deleting it.

---

### Permission changes not taking effect

**Problem:** After changing role permissions, users still have old access.

**Solutions:**
1. Clear cache: `php artisan optimize:clear`
2. Have users log out and back in
3. Check if user has multiple roles (permissions are combined)

---

*Last updated: January 2026*
