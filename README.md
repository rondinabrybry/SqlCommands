# SqlCommands Laravel Package

A **secure** PHP package for SQL learning and practice with Laravel and SQLite integration. Perfect for educational environments where students need to practice SQL queries safely.

## � **NEW: Zero-Configuration Installation**

**Routes and web interface are now included automatically!** No manual setup required.

```bash
composer require brybry/sql-practice
```

Then visit: `http://yourapp.com/sql-practice` - That's it!

## �🔒 Security Features

- **SQL Injection Prevention**: All queries use parameterized statements
- **Input Sanitization**: Table and column names are properly escaped
- **Operation Restrictions**: Configurable allowed operations
- **Safe Environment**: Isolated practice database separate from production

## 📦 Installation

Install via Composer:

```bash
composer require brybry/sql-practice
```

For Laravel projects, the service provider will be automatically registered.

## 🚀 Laravel Integration

### Quick Setup for SQL Practice

1. **Publish the configuration:**
   ```bash
   php artisan vendor:publish --provider="SqlCommands\Providers\SqlCommandsServiceProvider" --tag="config"
   ```

2. **Create a practice database:**
   ```bash
   touch database/practice.sqlite
   ```

3. **Configure database connection** in `config/database.php`:
   ```php
   'practice' => [
       'driver' => 'sqlite',
       'database' => database_path('practice.sqlite'),
       'prefix' => '',
       'foreign_key_constraints' => true,
   ],
   ```

4. **Initialize sample data:**
   ```php
   use SqlCommands\SqlSimulator;
   
   $simulator = new SqlSimulator(database_path('practice.sqlite'));
   $simulator->createSampleTables();
   $simulator->insertSampleData();
   ```

## 💻 Usage Examples

### Basic Query Building (Secure)

```php
use SqlCommands\SqlCommands;

// All methods return ['sql' => $query, 'params' => $parameters]

// SELECT with WHERE conditions
$query = SqlCommands::select('users', ['id', 'name'], [
    'where' => ['status' => 'active', 'age' => [18, 25, 30]], // IN clause
    'orderBy' => 'name',
    'limit' => 10
]);
// Result: ['sql' => 'SELECT `id`, `name` FROM `users` WHERE `status` = ? AND `age` IN (?, ?, ?) ORDER BY `name` LIMIT 10', 'params' => ['active', 18, 25, 30]]

// Secure INSERT
$query = SqlCommands::insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 28
]);

// Secure UPDATE with WHERE conditions
$query = SqlCommands::update('users', 
    ['status' => 'inactive'], 
    ['id' => 123]
);

// Safe DELETE with conditions
$query = SqlCommands::delete('users', ['status' => 'spam']);
```

### Advanced Queries

```php
// JOINs
$query = SqlCommands::leftJoin('users', 'orders', 'users.id = orders.user_id', 
    ['users.name', 'orders.total']
);

// Aggregates
$countQuery = "SELECT " . SqlCommands::count('id') . " FROM users";
$sumQuery = "SELECT " . SqlCommands::sum('total') . " FROM orders";

// BETWEEN with parameters
$between = SqlCommands::between('created_at', '2024-01-01', '2024-12-31');
$query = SqlCommands::select('orders', ['*'], [
    'where' => [$between['expression'] => $between['params']]
]);
```

### SQLite Functions (200+ Available)

The package includes comprehensive support for **all major SQLite functions** plus **advanced educational features**:

```php
// 🔤 String Functions  
SqlCommands::upper('name');               // UPPER(`name`)
SqlCommands::lower('email');              // LOWER(`email`)
SqlCommands::length('description');       // LENGTH(`description`)
SqlCommands::trim('text', ' .');          // TRIM(`text`, ' .')
SqlCommands::substr('phone', 1, 3);       // SUBSTR(`phone`, 1, 3)
SqlCommands::replace('text', 'old', 'new'); // REPLACE(`text`, 'old', 'new')
SqlCommands::printf('User: %s (ID: %d)', 'John', 123);

// 🎓 NEW: Educational Functions for Teaching Advanced SQL
SqlCommands::caseWhen([
    'age < 18' => 'Minor',
    'age < 65' => 'Adult'
], 'Senior');                             // CASE/WHEN conditional logic

SqlCommands::coalesce('mobile', 'home', 'email', 'N/A'); // NULL handling
SqlCommands::like('name', 'John%');       // Pattern matching with params
SqlCommands::exists($subquery);           // Subquery existence checks

// CTE (Common Table Expressions)
$withQuery = SqlCommands::with(['active_users' => $cte], $mainQuery);

// PIVOT simulation for data analysis
$pivot = SqlCommands::pivot('sales', 'quarter', ['Q1', 'Q2', 'Q3', 'Q4']);

// Database constraints for teaching design
SqlCommands::foreignKey('user_id', 'users', 'id', 'CASCADE');
SqlCommands::check('age >= 0 AND age <= 150');

// 🔢 Math & Numeric Functions
SqlCommands::abs('balance');              // ABS(`balance`)
SqlCommands::round('price', 2);           // ROUND(`price`, 2)
SqlCommands::sqrt('area');                // SQRT(`area`)
SqlCommands::pow('base', 'exponent');     // POW(`base`, `exponent`)
SqlCommands::random();                    // RANDOM()

// 📅 Date & Time Functions
SqlCommands::date('now');                 // date('now')
SqlCommands::datetime('now', '+1 day');   // datetime('now', '+1 day')
SqlCommands::strftime('%Y-%m-%d', 'now'); // strftime('%Y-%m-%d', 'now')

// 📊 Enhanced Aggregates
SqlCommands::total('amount');             // TOTAL(`amount`) - float sum
SqlCommands::groupConcat('tags', ', ');   // GROUP_CONCAT(`tags`, ', ')

// 🪟 Window Functions (SQLite 3.25+)
SqlCommands::rowNumber();                 // ROW_NUMBER()
SqlCommands::rank();                      // RANK()
SqlCommands::lag('salary', 1);            // LAG(`salary`, 1)
SqlCommands::lead('price', 1, '0');       // LEAD(`price`, 1, '0')

// 🗂️ JSON Functions (json1 extension)
SqlCommands::jsonExtract('{"name": "John"}', '$.name');
SqlCommands::jsonArray('item1', 'item2'); // json_array('item1', 'item2')
SqlCommands::jsonObject('key', 'value');  // json_object('key', 'value')

// 🔧 Utility Functions
SqlCommands::iif('age >= 18', 'adult', 'minor'); // IIF(age >= 18, 'adult', 'minor')
SqlCommands::changes();                   // CHANGES()
SqlCommands::lastInsertRowid();          // LAST_INSERT_ROWID()

// Real-world educational example
$query = SqlCommands::select('employees', [
    'name',
    SqlCommands::upper('department') . ' as dept_upper',
    SqlCommands::caseWhen([
        'salary < 30000' => 'Entry',
        'salary < 60000' => 'Mid',
        'salary >= 60000' => 'Senior'
    ], 'Executive') . ' as level',
    SqlCommands::coalesce('bonus', '0') . ' as bonus_amount',
    SqlCommands::round('salary * 1.1', 2) . ' as salary_with_raise'
], [
    'where' => ['active' => 1],
    'orderBy' => 'salary'
]);
```

🎓 **Perfect for Teaching:**
- **Conditional Logic** - CASE/WHEN statements for business rules
- **NULL Handling** - COALESCE, NULLIF for data quality
- **Pattern Matching** - LIKE, GLOB, REGEXP for search functionality  
- **Advanced Queries** - Subqueries, CTEs, window functions
- **Data Analysis** - PIVOT simulation, aggregations
- **Database Design** - Constraints, indexes, relationships

📚 **[Complete SQLite Functions Reference →](SQLITE-FUNCTIONS.md)** - Detailed documentation with 200+ functions, examples, and usage patterns.
```

### Using the Simulator

```php
use SqlCommands\SqlSimulator;

$simulator = new SqlSimulator(database_path('practice.sqlite'));

// Execute queries safely
$selectQuery = SqlCommands::select('users', ['name', 'email']);
$result = $simulator->executeQuery($selectQuery);

if ($result['success']) {
    foreach ($result['data'] as $row) {
        echo $row['name'] . ': ' . $row['email'] . "\n";
    }
} else {
    echo "Error: " . $result['error'];
}

// Get database schema for learning
$schema = $simulator->getSchema();
print_r($schema);
```

### Laravel Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SqlCommands\SqlCommands;
use SqlCommands\SqlSimulator;

class SqlPracticeController extends Controller
{
    private $simulator;
    
    public function __construct()
    {
        $this->simulator = new SqlSimulator(database_path('practice.sqlite'));
    }
    
    public function executeQuery(Request $request)
    {
        $request->validate([
            'table' => 'required|string|max:50',
            'operation' => 'required|in:select,insert,update,delete',
            'data' => 'array'
        ]);
        
        try {
            switch ($request->operation) {
                case 'select':
                    $query = SqlCommands::select($request->table, $request->columns ?? ['*']);
                    break;
                case 'insert':
                    $query = SqlCommands::insert($request->table, $request->data);
                    break;
                // ... other operations
            }
            
            $result = $this->simulator->executeQuery($query);
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    public function getSchema()
    {
        return response()->json($this->simulator->getSchema());
    }
}
}
```

## 📱 Mobile-Friendly Features

- **Lightweight SQLite**: Perfect for mobile web applications
- **Responsive Design**: Built-in web interface adapts to mobile screens
- **Offline Capability**: SQLite works without network connectivity
- **Touch-Friendly**: Optimized for touch interactions on tablets/phones

## 🛠 Available Methods

### Query Builders (All return `['sql' => $query, 'params' => $parameters]`)

| Method | Description | Example |
|--------|-------------|---------|
| `select($table, $columns, $options)` | SELECT with WHERE, ORDER BY, LIMIT | `select('users', ['name'], ['where' => ['active' => 1]])` |
| `insert($table, $data)` | Parameterized INSERT | `insert('users', ['name' => 'John'])` |
| `update($table, $data, $where)` | Parameterized UPDATE | `update('users', ['name' => 'Jane'], ['id' => 1])` |
| `delete($table, $where)` | Parameterized DELETE | `delete('users', ['status' => 'inactive'])` |
| `createTable($table, $columns)` | CREATE TABLE | `createTable('users', ['id' => 'INTEGER PRIMARY KEY'])` |
| `dropTable($table, $ifExists)` | DROP TABLE | `dropTable('temp_table', true)` |
| `truncateTable($table)` | TRUNCATE/DELETE ALL | `truncateTable('logs')` |

### JOIN Operations

- `innerJoin($table1, $table2, $on, $columns)`
- `leftJoin($table1, $table2, $on, $columns)`
- `crossJoin($table1, $table2, $columns)`
- `rightJoin()` - *MySQL only, throws exception in SQLite*

### Aggregate Functions

- `count($column)` - COUNT function
- `sum($column)` - SUM function  
- `avg($column)` - AVG function
- `min($column)` - MIN function
- `max($column)` - MAX function

### String Functions

- `length($column)` - LENGTH function
- `substring($column, $start, $length)` - SUBSTRING/SUBSTR
- `concat(...$args)` - Database-specific concatenation

### Utility Functions

- `groupBy($columns)` - GROUP BY clause
- `having($condition)` - HAVING clause
- `between($column, $start, $end)` - BETWEEN expression with parameters
- `showTables()` - Database-specific table listing
- `describeTable($table)` - Database-specific table structure

## 🔧 Database Compatibility

| Feature | SQLite | MySQL | PostgreSQL |
|---------|--------|-------|------------|
| Basic CRUD | ✅ | ✅ | ✅ |
| JOINs (except RIGHT) | ✅ | ✅ | ✅ |
| RIGHT JOIN | ❌ | ✅ | ✅ |
| TRUNCATE | ❌* | ✅ | ✅ |
| SHOW TABLES | ❌* | ✅ | ❌* |
| String Functions | ✅ | ✅ | ✅ |

*Automatically converted to compatible alternatives

## 🧪 Testing

Run the test suite:

```bash
php test_improved.php
```

For PHPUnit tests (requires dev dependencies):

```bash
composer install --dev
vendor/bin/phpunit tests/
```

## ⚙ Configuration Options

The `config/sql-commands.php` file provides these options:

```php
return [
    'practice_database_path' => database_path('practice.sqlite'),
    'allowed_operations' => ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER'],
    'auto_create_samples' => true,
    'max_execution_time' => 30,
    'enable_web_interface' => true,
    'route_prefix' => 'sql-practice',
    'middleware' => ['web'],
];
```

## 🚨 Security Notes

1. **Always use parameterized queries** - Never concatenate user input directly into SQL
2. **Validate user input** - Check table/column names against allowed lists
3. **Use separate database** - Keep practice database isolated from production
4. **Limit operations** - Configure allowed operations in config file
5. **Set execution timeouts** - Prevent runaway queries in learning environment

## 📚 Educational Use Cases

- **SQL Learning Platforms**: Safe environment for students to practice
- **Database Design Courses**: Create and modify table structures
- **Query Optimization Training**: Analyze query performance
- **Mobile Learning Apps**: Offline SQL practice on tablets/phones
- **Coding Bootcamps**: Hands-on SQL exercises
- **Self-Paced Learning**: Individual practice with immediate feedback

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality  
4. Ensure all tests pass
5. Submit a pull request

## 📄 License

MIT License - see LICENSE file for details.

## 🔗 Links

- [GitHub Repository](https://github.com/rondinabrybry/SqlCommands)
- [Documentation](https://github.com/rondinabrybry/SqlCommands#readme)
- [Issue Tracker](https://github.com/rondinabrybry/SqlCommands/issues)

---

**Made with ❤️ for SQL education and Laravel developers by Bryan Rondinab**
