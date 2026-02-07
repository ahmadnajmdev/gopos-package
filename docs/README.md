# GoPOS Documentation

Welcome to the GoPOS documentation. GoPOS is a comprehensive Point of Sale and Business Management System designed specifically for the Iraq/Kurdistan market with trilingual support (English, Arabic, Kurdish Sorani).

---

## What is GoPOS?

GoPOS is an all-in-one business management solution that combines:
- **Point of Sale (POS)** - Fast, efficient sales processing
- **Inventory Management** - Complete stock control across multiple locations
- **Accounting** - Full financial management and reporting
- **Human Resources** - Employee, attendance, and payroll management

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Trilingual** | Full support for English, Arabic, and Kurdish Sorani |
| **All-in-One** | No need for multiple systems - everything integrated |
| **Modern UI** | Clean, intuitive interface built with FilamentPHP |
| **Flexible** | Role-based permissions adapt to any business structure |
| **Scalable** | From single store to multi-warehouse operations |

---

## Documentation Overview

### Quick Start (10 minutes)

| Who Are You? | Start Here |
|--------------|------------|
| New user getting started | [QUICK-START.md](QUICK-START.md) |
| Day-to-day user operations | [USER-GUIDE.md](USER-GUIDE.md) |
| Having issues? | [FAQ.md](FAQ.md) |

### Module Documentation

Each module has detailed documentation including use cases, benefits, and step-by-step guides:

| Module | Description | Best For |
|--------|-------------|----------|
| [RBAC & Permissions](01-rbac-roles-permissions.md) | Control who can do what | Administrators, Security |
| [POS Enhancements](02-pos-enhancements.md) | Advanced sales features | Cashiers, Sales Managers |
| [Inventory Management](03-inventory-enhancements.md) | Stock control & warehouses | Warehouse Staff, Inventory Managers |
| [Accounting](04-accounting-enhancements.md) | Financial management | Accountants, Finance Teams |
| [HR Module](05-hr-module.md) | Employee & payroll management | HR Managers, Payroll Staff |

---

## Module Quick Reference

### Who Should Use Each Module?

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              GoPOS Modules                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐    Used by: Administrators                             │
│  │    RBAC     │    Purpose: Define who can access what                 │
│  │ Permissions │    Read this if: You manage users or security          │
│  └─────────────┘                                                        │
│                                                                         │
│  ┌─────────────┐    Used by: Cashiers, Sales Staff                      │
│  │     POS     │    Purpose: Process sales, manage shifts               │
│  │ Operations  │    Read this if: You work at the register              │
│  └─────────────┘                                                        │
│                                                                         │
│  ┌─────────────┐    Used by: Warehouse Staff, Inventory Managers        │
│  │  Inventory  │    Purpose: Track stock, transfers, counts             │
│  │ Management  │    Read this if: You manage products/stock             │
│  └─────────────┘                                                        │
│                                                                         │
│  ┌─────────────┐    Used by: Accountants, Finance Team                  │
│  │ Accounting  │    Purpose: Financial records, reports                 │
│  │  & Finance  │    Read this if: You handle money/finances             │
│  └─────────────┘                                                        │
│                                                                         │
│  ┌─────────────┐    Used by: HR Staff, Managers                         │
│  │   Human     │    Purpose: Employees, attendance, payroll             │
│  │  Resources  │    Read this if: You manage people/salaries            │
│  └─────────────┘                                                        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Common Business Scenarios

### "I run a retail shop"

**You'll primarily use:**
1. **POS Module** - Daily sales, shift management, customer loyalty
2. **Inventory Module** - Stock tracking, reorder alerts
3. **RBAC** - Set up cashier and manager roles

**Start with:** [QUICK-START.md](QUICK-START.md) → [POS Guide](02-pos-enhancements.md)

---

### "I have multiple warehouses"

**You'll primarily use:**
1. **Inventory Module** - Multi-warehouse, stock transfers, batch tracking
2. **POS Module** - Sell from any location
3. **Accounting** - Track costs across locations

**Start with:** [Inventory Guide](03-inventory-enhancements.md)

---

### "I need financial reports"

**You'll primarily use:**
1. **Accounting Module** - Journal entries, bank reconciliation, budgets
2. **Reports** - Financial statements, trial balance

**Start with:** [Accounting Guide](04-accounting-enhancements.md)

---

### "I manage employees"

**You'll primarily use:**
1. **HR Module** - Employee records, attendance, leave management
2. **Payroll** - Salary processing, payslips
3. **RBAC** - Employee access permissions

**Start with:** [HR Guide](05-hr-module.md)

---

## Translations

All documentation is available in:

| Language | Directory |
|----------|-----------|
| English | `/docs/` (default) |
| Arabic (العربية) | `/docs/ar/` |
| Kurdish (کوردی) | `/docs/ckb/` |

---

## Documentation Structure

Each module documentation follows a consistent structure:

1. **Overview** - What is this module and why use it
2. **Key Benefits** - What problems it solves
3. **Use Cases** - Real-world scenarios
4. **Features** - Detailed feature breakdown with:
   - What it does
   - When to use it
   - How to use it (UI + Code)
   - Business examples
5. **Technical Reference** - API, database schema, code examples
6. **Troubleshooting** - Common issues and solutions

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | January 2026 | Initial release |

---

## Getting Help

| Issue Type | Action |
|------------|--------|
| User questions | Contact your system administrator |
| Bug reports | Document steps to reproduce |
| Feature requests | Submit to your administrator |

---

*GoPOS - Point of Sale & Business Management System for Iraq/Kurdistan*
