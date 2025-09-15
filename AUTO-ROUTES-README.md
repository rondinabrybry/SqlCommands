# 🚀 Auto-Routes Installation Guide

Your `brybry/sql-practice` package now includes **automatic routes** and a complete web interface! No manual setup required.

## 📦 One-Command Installation

```bash
composer require brybry/sql-practice
```

That's it! The package automatically:
- ✅ **Loads routes** at `/sql-practice`
- ✅ **Includes web interface** with professional design
- ✅ **Sets up database** with sample data
- ✅ **Configures everything** you need

## 🎯 Instant Access

After installation, visit:
```
http://yourapp.com/sql-practice
```

You'll get a complete SQL learning environment with:
- 📝 **SQL Editor** (textarea for commands)
- 📊 **Results Display** (tables, success/error messages)
- 📋 **Database Schema** (sidebar reference)
- 💡 **Sample Queries** (click to auto-fill)
- 📱 **Mobile-Friendly** design
- 🔄 **Reset Database** functionality

## ⚙️ Optional Configuration

Publish the config file (optional):
```bash
php artisan vendor:publish --provider="SqlCommands\Providers\SqlCommandsServiceProvider" --tag="config"
```

This creates `config/sql-commands.php` where you can customize:
- Route prefix (default: `sql-practice`)
- Database path
- Allowed SQL operations
- Security settings

## 🔐 Available Routes (Auto-loaded)

| Route | Method | Purpose |
|-------|--------|---------|
| `/sql-practice` | GET | Main interface |
| `/sql-practice/execute` | POST | Execute SQL commands |
| `/sql-practice/schema` | GET | Get database schema |
| `/sql-practice/samples` | GET | Get sample queries |
| `/sql-practice/reset` | POST | Reset database |
| `/sql-practice/export` | POST | Export results as CSV |

## 🛡️ Security Features (Built-in)

- ✅ **SQL Injection Protection** - All queries use prepared statements
- ✅ **Operation Restrictions** - Configurable allowed SQL commands  
- ✅ **Input Validation** - Safe table/column name handling
- ✅ **Isolated Database** - Practice DB separate from your app
- ✅ **Execution Timeouts** - Prevents runaway queries

## 📱 Interface Features

### What Your Users Get:
1. **Professional SQL Editor** - Large textarea with syntax support
2. **Instant Results** - Execute button + Ctrl+Enter shortcut
3. **Smart Display** - Tables for SELECT, messages for INSERT/UPDATE/DELETE
4. **Schema Browser** - See all tables and columns in sidebar
5. **Learning Samples** - 12+ example queries to learn from
6. **Error Help** - Clear error messages for learning
7. **Mobile Ready** - Responsive design for phones/tablets
8. **Export Data** - Download query results as CSV

### Sample Interface:
```
┌─────────────────────────────────┬─────────────────┐
│ SQL Command Editor              │ Database Schema │
│ ┌─────────────────────────────┐ │ 📋 users       │
│ │ SELECT u.name, COUNT(o.id)  │ │   - id (INT)   │
│ │ FROM users u                │ │   - name (TEXT)│
│ │ LEFT JOIN orders o          │ │   - email      │
│ │ ON u.id = o.user_id         │ │   - age        │
│ │ GROUP BY u.id, u.name;      │ │                │
│ └─────────────────────────────┘ │ 📋 orders      │
│ [▶️ Execute] [🗑️ Clear] [🔄 Reset] │   - id         │
│                                 │   - user_id    │
│ Query Results ✅ 4 rows         │   - total      │
│ ┌─────────────────────────────┐ │   - status     │
│ │ name     │ order_count      │ │                │
│ │ John     │ 2                │ │ 💡 Samples     │
│ │ Jane     │ 1                │ │ • Basic SELECT │
│ │ Alice    │ 0                │ │ • JOIN queries │
│ │ Bob      │ 1                │ │ • INSERT data  │
│ └─────────────────────────────┘ │ • UPDATE/DELETE│
└─────────────────────────────────┴─────────────────┘
```

## 🎓 Educational Use Cases

Perfect for:
- 📚 **SQL Learning Platforms**
- 🏫 **Universities/Bootcamps**
- 💻 **Online Courses**
- 📱 **Mobile Learning Apps**
- 👨‍🎓 **Self-Study Environments**

## 🔧 Advanced Options

### Add Authentication
```php
// In a middleware or route group
Route::group(['middleware' => 'auth'], function() {
    // Your other routes
});
```

### Customize Routes Prefix
In `config/sql-commands.php`:
```php
'route_prefix' => 'my-sql-lab', // Changes to /my-sql-lab
```

### Restrict Operations
```php
'allowed_operations' => ['SELECT'], // Only allow SELECT queries
```

### Custom Database
```php
'practice_database_path' => storage_path('my-practice.sqlite'),
```

## 📊 What Students Can Practice

### Beginner Queries:
- `SELECT * FROM users`
- `SELECT name, email FROM users WHERE age > 25`
- `INSERT INTO users (name, email) VALUES ('New User', 'test@example.com')`

### Intermediate Queries:
- `SELECT u.name, COUNT(o.id) FROM users u LEFT JOIN orders o ON u.id = o.user_id GROUP BY u.id`
- `UPDATE users SET status = 'active' WHERE age BETWEEN 18 AND 65`
- `DELETE FROM orders WHERE created_at < '2024-01-01'`

### Advanced Queries:
- Subqueries, window functions, CTEs
- Complex JOINs across multiple tables
- Aggregate functions with HAVING clauses

## 🚀 Zero Configuration Deployment

1. **Install**: `composer require brybry/sql-practice`
2. **Visit**: `/sql-practice`
3. **Start Learning**: SQL interface is ready!

No routes to add, no controllers to create, no views to publish. Everything works out of the box!

---

**Your SQL learning environment is ready in under 30 seconds! 🎉**