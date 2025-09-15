# SQLite Functions Reference

This document provides a comprehensive guide to all SQLite functions supported by the brybry/sql-practice package.

## 📚 Function Categories

### 🔤 String Functions & Operators

#### Basic String Operations
```php
// String concatenation (SQLite uses ||)
SqlCommands::concat('column1', 'column2'); // column1 || column2

// String length
SqlCommands::length('name'); // LENGTH(`name`)

// Case conversion
SqlCommands::upper('name');  // UPPER(`name`)
SqlCommands::lower('name');  // LOWER(`name`)

// Trimming
SqlCommands::trim('name');           // TRIM(`name`)
SqlCommands::trim('name', ' .');     // TRIM(`name`, ' .')
SqlCommands::ltrim('name');          // LTRIM(`name`)
SqlCommands::rtrim('name');          // RTRIM(`name`)

// Substring operations
SqlCommands::substring('name', 1, 5); // SUBSTR(`name`, 1, 5)
SqlCommands::instr('haystack', 'needle'); // INSTR(`haystack`, 'needle')
SqlCommands::replace('text', 'old', 'new'); // REPLACE(`text`, 'old', 'new')
```

#### Advanced String Functions
```php
// Formatted output
SqlCommands::printf('Hello %s, you have %d messages', 'John', 5);
// PRINTF('Hello %s, you have %d messages', 'John', 5)

// SQL quoting and encoding
SqlCommands::quote('text');    // QUOTE(`text`)
SqlCommands::hex('data');      // HEX(`data`)

// Unicode operations
SqlCommands::unicode('char');  // UNICODE(`char`)
SqlCommands::char(65, 66, 67); // CHAR(65, 66, 67) → 'ABC'

// Binary operations
SqlCommands::zeroblob(1024);   // ZEROBLOB(1024)
```

### 🔢 Numeric & Math Functions

#### Basic Math
```php
SqlCommands::abs('number');    // ABS(`number`)
SqlCommands::sign('number');   // SIGN(`number`)
SqlCommands::round('price', 2); // ROUND(`price`, 2)

// Random functions
SqlCommands::random();         // RANDOM()
SqlCommands::randomblob(16);   // RANDOMBLOB(16)
```

#### Advanced Math
```php
// Rounding functions
SqlCommands::ceil('value');    // CEIL(`value`)
SqlCommands::ceiling('value'); // CEILING(`value`)
SqlCommands::floor('value');   // FLOOR(`value`)

// Power and roots
SqlCommands::pow('base', 'exp');    // POW(`base`, `exp`)
SqlCommands::power('base', 'exp');  // POWER(`base`, `exp`)
SqlCommands::sqrt('number');        // SQRT(`number`)

// Logarithmic functions
SqlCommands::exp('x');    // EXP(`x`)
SqlCommands::ln('x');     // LN(`x`)
SqlCommands::log('x');    // LOG(`x`)
SqlCommands::log2('x');   // LOG2(`x`)
```

#### Trigonometric Functions
```php
// Basic trig
SqlCommands::sin('angle');     // SIN(`angle`)
SqlCommands::cos('angle');     // COS(`angle`)
SqlCommands::tan('angle');     // TAN(`angle`)

// Inverse trig
SqlCommands::asin('value');    // ASIN(`value`)
SqlCommands::acos('value');    // ACOS(`value`)
SqlCommands::atan('value');    // ATAN(`value`)
SqlCommands::atan2('y', 'x');  // ATAN2(`y`, `x`)

// Hyperbolic
SqlCommands::sinh('x');        // SINH(`x`)
SqlCommands::cosh('x');        // COSH(`x`)
SqlCommands::tanh('x');        // TANH(`x`)

// Angle conversion
SqlCommands::degrees('radians'); // DEGREES(`radians`)
SqlCommands::radians('degrees'); // RADIANS(`degrees`)
```

### 📅 Date & Time Functions

All date/time functions return ISO8601 format by default:

```php
// Basic date/time functions
SqlCommands::date('now');                    // date('now')
SqlCommands::time('now');                    // time('now')  
SqlCommands::datetime('now');                // datetime('now')
SqlCommands::julianday('now');              // julianday('now')

// With modifiers
SqlCommands::date('now', '+1 day', 'start of month');
// date('now', '+1 day', 'start of month')

SqlCommands::strftime('%Y-%m-%d', 'now', 'localtime');
// strftime('%Y-%m-%d', 'now', 'localtime')
```

#### Common Date Modifiers
- `'+N days'`, `'-N days'`
- `'+N hours'`, `'-N hours'`
- `'start of month'`, `'start of year'`
- `'start of day'`
- `'weekday N'` (0=Sunday, 6=Saturday)
- `'localtime'`, `'utc'`

### 📊 Aggregate Functions

```php
// Basic aggregates (existing)
SqlCommands::count('*');       // COUNT(*)
SqlCommands::count('column');  // COUNT(`column`)
SqlCommands::sum('amount');    // SUM(`amount`)
SqlCommands::avg('score');     // AVG(`score`)
SqlCommands::min('date');      // MIN(`date`)
SqlCommands::max('date');      // MAX(`date`)

// New aggregates
SqlCommands::total('amount');  // TOTAL(`amount`) - float sum
SqlCommands::groupConcat('name');           // GROUP_CONCAT(`name`)
SqlCommands::groupConcat('name', '; ');     // GROUP_CONCAT(`name`, '; ')
```

### 🪟 Window Functions (SQLite 3.25+)

```php
// Ranking functions
SqlCommands::rowNumber();      // ROW_NUMBER()
SqlCommands::rank();           // RANK()
SqlCommands::denseRank();      // DENSE_RANK()
SqlCommands::percentRank();    // PERCENT_RANK()
SqlCommands::cumeDist();       // CUME_DIST()
SqlCommands::ntile(4);         // NTILE(4)

// Offset functions
SqlCommands::lag('salary', 1);              // LAG(`salary`, 1)
SqlCommands::lag('salary', 1, '0');         // LAG(`salary`, 1, '0')
SqlCommands::lead('salary', 1);             // LEAD(`salary`, 1)

// Frame functions
SqlCommands::firstValue('name');            // FIRST_VALUE(`name`)
SqlCommands::lastValue('name');             // LAST_VALUE(`name`)
SqlCommands::nthValue('name', 2);           // NTH_VALUE(`name`, 2)
```

#### Window Function Usage Example
```sql
SELECT 
    name,
    salary,
    ROW_NUMBER() OVER (ORDER BY salary DESC) as rank,
    LAG(salary, 1) OVER (ORDER BY salary) as prev_salary
FROM employees;
```

### 🗂️ JSON Functions (json1 extension)

```php
// JSON creation
SqlCommands::json('{"key": "value"}');      // json('{"key": "value"}')
SqlCommands::jsonArray('a', 'b', 'c');      // json_array('a', 'b', 'c')
SqlCommands::jsonObject('key1', 'value1', 'key2', 'value2');
// json_object('key1', 'value1', 'key2', 'value2')

// JSON querying
SqlCommands::jsonExtract('{"name": "John"}', '$.name');
// json_extract('{"name": "John"}', '$.name')

SqlCommands::jsonArrayLength('[1,2,3]');    // json_array_length('[1,2,3]')
SqlCommands::jsonType('{"key": "value"}', '$.key');
// json_type('{"key": "value"}', '$.key')

// JSON modification
SqlCommands::jsonInsert('{}', '$.name', 'John');
SqlCommands::jsonReplace('{"name": "John"}', '$.name', 'Jane');
SqlCommands::jsonSet('{}', '$.name', 'John');
SqlCommands::jsonRemove('{"name": "John", "age": 30}', '$.age');

// JSON validation and utilities
SqlCommands::jsonValid('{"valid": true}');  // json_valid('{"valid": true}')
SqlCommands::jsonQuote('text');             // json_quote('text')

// JSON aggregation
SqlCommands::jsonGroupArray('name');        // json_group_array(`name`)
SqlCommands::jsonGroupObject('key', 'value'); // json_group_object(`key`, `value`)
```

### 🔧 Miscellaneous Functions

```php
// Conditional
SqlCommands::iif('age > 18', 'adult', 'minor');
// IIF(age > 18, 'adult', 'minor')

// Database state
SqlCommands::changes();            // CHANGES()
SqlCommands::lastInsertRowid();    // LAST_INSERT_ROWID()
SqlCommands::totalChanges();       // TOTAL_CHANGES()

// Query optimization hints
SqlCommands::likelihood('condition', 0.1);  // LIKELIHOOD(condition, 0.1)
SqlCommands::likely('condition');           // LIKELY(condition)
SqlCommands::unlikely('condition');         // UNLIKELY(condition)
```

### 🎓 Educational & Advanced Query Functions

These functions are specifically designed for teaching advanced SQL concepts and real-world query patterns.

#### Conditional Logic (CASE/WHEN)
```php
// Simple CASE/WHEN with conditions
SqlCommands::caseWhen([
    'age < 18' => 'Minor',
    'age < 65' => 'Adult', 
    'age >= 65' => 'Senior'
], 'Unknown');
// CASE WHEN age < 18 THEN 'Minor' WHEN age < 65 THEN 'Adult' WHEN age >= 65 THEN 'Senior' ELSE 'Unknown' END

// Column-based CASE/WHEN
SqlCommands::caseWhenColumn('status', [
    'A' => 'Active',
    'I' => 'Inactive',
    'P' => 'Pending'
], 'Unknown');
// CASE WHEN `status` = 'A' THEN 'Active' WHEN `status` = 'I' THEN 'Inactive' WHEN `status` = 'P' THEN 'Pending' ELSE 'Unknown' END
```

#### NULL Handling Functions
```php
// COALESCE - return first non-null value
SqlCommands::coalesce('mobile_phone', 'home_phone', 'email', 'No contact');
// COALESCE(`mobile_phone`, `home_phone`, `email`, 'No contact')

// NULLIF - return NULL if values are equal
SqlCommands::nullif('salary', '0');    // NULLIF(`salary`, '0')

// IFNULL - SQLite specific null replacement
SqlCommands::ifnull('description', 'No description available');
// IFNULL(`description`, 'No description available')
```

#### Pattern Matching & Search
```php
// LIKE with wildcards
$like = SqlCommands::like('name', 'John%');
// Returns: ['expression' => '`name` LIKE ?', 'params' => ['John%']]

$notLike = SqlCommands::notLike('email', '%@spam.com');
// Returns: ['expression' => '`email` NOT LIKE ?', 'params' => ['%@spam.com']]

// GLOB patterns (SQLite specific)
$glob = SqlCommands::glob('filename', '*.pdf'); 
// Returns: ['expression' => '`filename` GLOB ?', 'params' => ['*.pdf']]

// REGEXP patterns
$regexp = SqlCommands::regexp('phone', '^\\+1[0-9]{10}$');
// Returns: ['expression' => '`phone` REGEXP ?', 'params' => ['^\\+1[0-9]{10}$']]

// Convenience pattern functions
SqlCommands::startsWith('email', 'admin');    // `email` LIKE 'admin%'
SqlCommands::endsWith('filename', '.jpg');    // `filename` LIKE '%.jpg' 
SqlCommands::contains('description', 'urgent'); // `description` LIKE '%urgent%'
```

#### Subqueries & Advanced Queries
```php
// EXISTS subqueries
$subquery = ['sql' => 'SELECT 1 FROM orders WHERE user_id = users.id', 'params' => []];
SqlCommands::exists($subquery);              // EXISTS (SELECT 1 FROM orders WHERE user_id = users.id)
SqlCommands::notExists($subquery);           // NOT EXISTS (SELECT 1 FROM orders WHERE user_id = users.id)

// IN/NOT IN with subqueries
$activeUsersSubquery = ['sql' => 'SELECT id FROM users WHERE last_login > ?', 'params' => ['2024-01-01']];
$inSub = SqlCommands::inSubquery('user_id', $activeUsersSubquery);
// Returns: ['expression' => '`user_id` IN (SELECT id FROM users WHERE last_login > ?)', 'params' => ['2024-01-01']]

$notInSub = SqlCommands::notInSubquery('user_id', $activeUsersSubquery);
// Returns: ['expression' => '`user_id` NOT IN (SELECT id FROM users WHERE last_login > ?)', 'params' => ['2024-01-01']]
```

#### Common Table Expressions (CTEs)
```php
// WITH clauses for complex queries
$cte1 = ['sql' => 'SELECT * FROM employees WHERE department = ?', 'params' => ['IT']];
$cte2 = ['sql' => 'SELECT * FROM employees WHERE salary > ?', 'params' => [50000]];
$mainQuery = ['sql' => 'SELECT * FROM it_employees INNER JOIN high_earners USING(id)', 'params' => []];

$withQuery = SqlCommands::with([
    'it_employees' => $cte1,
    'high_earners' => $cte2
], $mainQuery);
// WITH `it_employees` AS (SELECT * FROM employees WHERE department = ?), 
//      `high_earners` AS (SELECT * FROM employees WHERE salary > ?) 
// SELECT * FROM it_employees INNER JOIN high_earners USING(id)

// Recursive CTEs
$recursiveCTE = ['sql' => 'SELECT id, name, manager_id, 1 as level FROM employees WHERE manager_id IS NULL UNION ALL SELECT e.id, e.name, e.manager_id, h.level + 1 FROM employees e JOIN hierarchy h ON e.manager_id = h.id', 'params' => []];
$hierarchyQuery = SqlCommands::withRecursive(['hierarchy' => $recursiveCTE], ['sql' => 'SELECT * FROM hierarchy', 'params' => []]);
```

#### Data Analysis (PIVOT Simulation)
```php
// PIVOT simulation using CASE statements
$pivot = SqlCommands::pivot('sales_amount', 'quarter', ['Q1', 'Q2', 'Q3', 'Q4']);
// Returns array of columns:
// SUM(CASE WHEN `quarter` = 'Q1' THEN `sales_amount` ELSE 0 END) AS Q1,
// SUM(CASE WHEN `quarter` = 'Q2' THEN `sales_amount` ELSE 0 END) AS Q2, ...

// UNPIVOT simulation
$unpivot = SqlCommands::unpivot('sales_summary', ['Q1_sales', 'Q2_sales', 'Q3_sales'], 'quarter', 'amount');
// SELECT `id`, 'Q1_sales' as quarter, `Q1_sales` as amount FROM `sales_summary` 
// UNION ALL SELECT `id`, 'Q2_sales' as quarter, `Q2_sales` as amount FROM `sales_summary` ...
```

#### Database Design & Constraints
```php
// Primary keys
SqlCommands::primaryKey('id');               // PRIMARY KEY(`id`)
SqlCommands::primaryKey('user_id', 'role_id'); // PRIMARY KEY(`user_id`, `role_id`)

// Foreign keys with actions
SqlCommands::foreignKey('user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
// FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT

// Unique constraints
SqlCommands::unique('email');                // UNIQUE(`email`)
SqlCommands::unique('username', 'email');   // UNIQUE(`username`, `email`)

// Check constraints
SqlCommands::check('age >= 0 AND age <= 150'); // CHECK (age >= 0 AND age <= 150)
SqlCommands::check('price > 0');             // CHECK (price > 0)

// Column constraints
SqlCommands::notNull('email');              // `email` NOT NULL
SqlCommands::defaultValue('status', 'active'); // `status` DEFAULT 'active'
```

#### Query Analysis & Optimization
```php
// Query explanation
$query = SqlCommands::select('users', ['*'], ['where' => ['active' => 1]]);
$explain = SqlCommands::explain($query);        // EXPLAIN SELECT ...
$explainPlan = SqlCommands::explain($query, true); // EXPLAIN QUERY PLAN SELECT ...

// Index hints
SqlCommands::indexed('user_id', 'user_btree_idx'); // `user_id` INDEXED BY `user_btree_idx`
SqlCommands::notIndexed('created_at');            // `created_at` NOT INDEXED
```

## 🎯 Educational Query Examples

### Real-World Teaching Scenarios

#### 1. Student Grading System
```php
$gradeQuery = SqlCommands::select('student_scores', [
    'student_name',
    'score',
    SqlCommands::caseWhen([
        'score >= 90' => 'A',
        'score >= 80' => 'B',
        'score >= 70' => 'C', 
        'score >= 60' => 'D'
    ], 'F') . ' as letter_grade',
    SqlCommands::caseWhen([
        'score >= 60' => 'Pass',
    ], 'Fail') . ' as pass_fail'
]);
```

#### 2. Customer Contact Management
```php
$contactQuery = SqlCommands::select('customers', [
    'name',
    'email',
    SqlCommands::coalesce('mobile_phone', 'home_phone', 'work_phone', 'No phone') . ' as best_phone',
    SqlCommands::caseWhen([
        'email IS NOT NULL' => 'Email Available',
        'mobile_phone IS NOT NULL' => 'Phone Available'
    ], 'No Contact') . ' as contact_status'
]);
```

#### 3. Sales Analysis with PIVOT
```php
// Monthly sales pivot
$pivot = SqlCommands::pivot('amount', 'month', ['Jan', 'Feb', 'Mar', 'Apr']);
$salesQuery = SqlCommands::select('sales', [
    'salesperson',
    ...$pivot['columns']  // Jan, Feb, Mar, Apr columns
]) . ' ' . SqlCommands::groupBy('salesperson');
```

#### 4. Hierarchical Data (Employee Management)
```php
// Find all employees under a manager using CTE
$cte = ['sql' => 'SELECT id, name, manager_id, 0 as level FROM employees WHERE id = ? UNION ALL SELECT e.id, e.name, e.manager_id, h.level + 1 FROM employees e JOIN hierarchy h ON e.manager_id = h.id', 'params' => [1]];

$hierarchyQuery = SqlCommands::withRecursive(['hierarchy' => $cte], [
    'sql' => 'SELECT name, level FROM hierarchy ORDER BY level, name',
    'params' => []
]);
```

#### 5. Advanced Search with Multiple Patterns
```php
// Complex product search
$namePattern = SqlCommands::like('name', '%smartphone%');
$codePattern = SqlCommands::regexp('product_code', '^SP-[0-9]{4}$');
$descPattern = SqlCommands::contains('description', 'Android');

$searchQuery = SqlCommands::select('products', ['name', 'price', 'description'], [
    'where' => [
        $namePattern['expression'] => $namePattern['params'],
        'AND',
        $codePattern['expression'] => $codePattern['params'],
        'OR', 
        $descPattern['expression'] => $descPattern['params']
    ]
]);
```

---

## 📝 Notes
```

### ⚙️ Special SQLite Commands

```php
// Database management
SqlCommands::pragma('foreign_keys');        // Returns ['sql' => 'PRAGMA foreign_keys', 'params' => []]
SqlCommands::pragma('journal_mode', 'WAL'); // PRAGMA journal_mode = WAL
SqlCommands::vacuum();                      // VACUUM
SqlCommands::analyze();                     // ANALYZE
SqlCommands::analyze('table_name');         // ANALYZE `table_name`

// Database attachment
SqlCommands::attachDatabase('/path/to/db.sqlite', 'attached_db');
SqlCommands::detachDatabase('attached_db');
```

## 🎯 Usage Examples

### Complete Query Examples

```php
// String manipulation query
$query = SqlCommands::select('users', [
    'name',
    SqlCommands::upper('name') . ' as name_upper',
    SqlCommands::length('email') . ' as email_length',
    SqlCommands::substr('phone', 1, 3) . ' as area_code'
]);

// Math calculations
$query = SqlCommands::select('products', [
    'name',
    'price',
    SqlCommands::round('price * 1.1', 2) . ' as price_with_tax',
    SqlCommands::abs('price - 100') . ' as price_diff'
]);

// Date operations
$query = SqlCommands::select('orders', [
    'id',
    SqlCommands::date('created_at') . ' as order_date',
    SqlCommands::strftime('%w', 'created_at') . ' as weekday'
], [
    'where' => ['created_at' => SqlCommands::date('now', '-30 days')]
]);

// Window functions with aggregation
$query = SqlCommands::select('sales', [
    'employee_id',
    'amount',
    SqlCommands::sum('amount') . ' OVER (PARTITION BY employee_id) as total_by_employee',
    SqlCommands::rowNumber() . ' OVER (ORDER BY amount DESC) as sales_rank'
]);

// JSON operations
$query = SqlCommands::select('products', [
    'name',
    SqlCommands::jsonExtract('metadata', '$.category') . ' as category',
    SqlCommands::jsonArrayLength('tags') . ' as tag_count'
]);
```

## 🔍 Function Reference Quick Index

### String Functions
`upper()`, `lower()`, `length()`, `trim()`, `ltrim()`, `rtrim()`, `substr()`, `instr()`, `replace()`, `printf()`, `quote()`, `hex()`, `unicode()`, `char()`, `zeroblob()`

### Math Functions  
`abs()`, `sign()`, `round()`, `random()`, `randomblob()`, `ceil()`, `ceiling()`, `floor()`, `pow()`, `power()`, `sqrt()`, `exp()`, `ln()`, `log()`, `log2()`

### Trigonometric
`sin()`, `cos()`, `tan()`, `asin()`, `acos()`, `atan()`, `atan2()`, `sinh()`, `cosh()`, `tanh()`, `degrees()`, `radians()`

### Date/Time
`date()`, `time()`, `datetime()`, `julianday()`, `strftime()`

### Aggregates
`count()`, `sum()`, `avg()`, `min()`, `max()`, `total()`, `groupConcat()`

### Window Functions
`rowNumber()`, `rank()`, `denseRank()`, `percentRank()`, `cumeDist()`, `ntile()`, `lag()`, `lead()`, `firstValue()`, `lastValue()`, `nthValue()`

### JSON Functions
`json()`, `jsonArray()`, `jsonObject()`, `jsonExtract()`, `jsonInsert()`, `jsonReplace()`, `jsonSet()`, `jsonRemove()`, `jsonValid()`, `jsonQuote()`, `jsonGroupArray()`, `jsonGroupObject()`

### Utility Functions
`iif()`, `changes()`, `lastInsertRowid()`, `totalChanges()`, `likelihood()`, `likely()`, `unlikely()`

### Educational & Advanced Functions
`caseWhen()`, `caseWhenColumn()`, `coalesce()`, `nullif()`, `ifnull()`, `like()`, `glob()`, `regexp()`, `exists()`, `inSubquery()`, `with()`, `pivot()`, `explain()`, `foreignKey()`, `primaryKey()`, `unique()`, `check()`

### Special Commands
`pragma()`, `vacuum()`, `analyze()`, `attachDatabase()`, `detachDatabase()`

---

## 📝 Notes

- All functions return properly escaped SQL strings
- Parameters are automatically sanitized to prevent SQL injection
- Column names are wrapped in backticks for safety
- String values are properly quoted
- Numeric values are validated
- Functions that return arrays include both `sql` and `params` keys for prepared statements

For more examples and usage patterns, see the main README.md file.