<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Practice Environment</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 0;
            min-height: 600px;
        }

        .sql-editor {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .editor-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
        }

        .editor-controls {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #219a52;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .sql-textarea {
            width: 100%;
            height: 200px;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
            transition: border-color 0.3s;
        }

        .sql-textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        .result-section {
            margin-top: 20px;
            flex: 1;
        }

        .result-header {
            font-size: 1.2em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #bdc3c7;
        }

        .status-success {
            background: #27ae60;
        }

        .status-error {
            background: #e74c3c;
        }

        .result-container {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 6px;
            overflow: hidden;
        }

        .result-table th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
        }

        .result-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .result-table tr:hover {
            background: #f8f9fa;
        }

        .error-message {
            background: #fee;
            color: #c0392b;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #e74c3c;
        }

        .success-message {
            background: #efe;
            color: #27ae60;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #27ae60;
        }

        .sidebar {
            background: #f8f9fa;
            border-left: 1px solid #e9ecef;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .schema-table {
            background: white;
            border-radius: 6px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .schema-table-header {
            background: #34495e;
            color: white;
            padding: 10px 15px;
            font-weight: 500;
        }

        .schema-column {
            padding: 8px 15px;
            border-bottom: 1px solid #eee;
            font-family: monospace;
            font-size: 13px;
        }

        .schema-column:last-child {
            border-bottom: none;
        }

        .sample-query {
            background: white;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #e9ecef;
        }

        .sample-query:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
        }

        .sample-query-title {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .sample-query-desc {
            font-size: 12px;
            color: #7f8c8d;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                border-left: none;
                border-top: 1px solid #e9ecef;
                max-height: 300px;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
            
            .editor-controls {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 SQL Practice Environment</h1>
            <p>Learn SQL with a safe, interactive database. Type your queries and see results instantly!</p>
        </div>

        <div class="main-content">
            <div class="sql-editor">
                <div class="editor-header">
                    <div class="editor-title">SQL Command Editor</div>
                    <div class="editor-controls">
                        <button class="btn btn-success" id="executeBtn">
                            <span id="executeText">▶️ Execute</span>
                            <span id="loadingSpinner" class="loading" style="display:none;"></span>
                        </button>
                        <button class="btn btn-secondary" id="clearBtn">🗑️ Clear</button>
                        <button class="btn btn-danger" id="resetBtn">🔄 Reset DB</button>
                    </div>
                </div>

                <textarea 
                    id="sqlTextarea" 
                    class="sql-textarea" 
                    placeholder="Type your SQL commands here...&#10;&#10;Examples:&#10;SELECT * FROM users;&#10;INSERT INTO users (name, email, age) VALUES ('John', 'john@example.com', 25);&#10;UPDATE users SET age = 30 WHERE name = 'John';&#10;DELETE FROM users WHERE age < 18;"
                ></textarea>

                <div class="result-section">
                    <div class="result-header">
                        <span>Query Results</span>
                        <div id="statusIndicator" class="status-indicator"></div>
                        <span id="resultCount" style="font-size: 0.9em; color: #7f8c8d;"></span>
                    </div>
                    <div id="resultContainer" class="result-container">
                        <p style="color: #7f8c8d; text-align: center; padding: 20px;">
                            Execute a SQL query to see results here...
                        </p>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <h3>📋 Database Schema</h3>
                <div id="schemaContainer">
                    @if(isset($schema) && !empty($schema))
                        @foreach($schema as $tableName => $columns)
                            <div class="schema-table">
                                <div class="schema-table-header">{{ $tableName }}</div>
                                @foreach($columns as $column)
                                    <div class="schema-column">
                                        {{ $column['name'] ?? 'column' }} - {{ $column['type'] ?? 'type' }}
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <p style="color: #7f8c8d;">Loading schema...</p>
                    @endif
                </div>

                <h3 style="margin-top: 25px;">💡 Sample Queries</h3>
                <div id="samplesContainer">
                    <p style="color: #7f8c8d;">Loading samples...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token for Laravel
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // DOM Elements
        const sqlTextarea = document.getElementById('sqlTextarea');
        const executeBtn = document.getElementById('executeBtn');
        const clearBtn = document.getElementById('clearBtn');
        const resetBtn = document.getElementById('resetBtn');
        const resultContainer = document.getElementById('resultContainer');
        const statusIndicator = document.getElementById('statusIndicator');
        const resultCount = document.getElementById('resultCount');
        const executeText = document.getElementById('executeText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const samplesContainer = document.getElementById('samplesContainer');

        // Execute SQL Command
        executeBtn.addEventListener('click', async () => {
            const sql = sqlTextarea.value.trim();
            if (!sql) {
                alert('Please enter a SQL command');
                return;
            }

            setLoading(true);
            
            try {
                const response = await fetch('/sql-practice/execute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        sql_command: sql,
                        command_type: 'raw'
                    })
                });

                const data = await response.json();
                displayResult(data);

            } catch (error) {
                displayResult({
                    success: false,
                    error: 'Network error: ' + error.message
                });
            }
            
            setLoading(false);
        });

        // Clear textarea
        clearBtn.addEventListener('click', () => {
            sqlTextarea.value = '';
            resultContainer.innerHTML = '<p style="color: #7f8c8d; text-align: center; padding: 20px;">Execute a SQL query to see results here...</p>';
            statusIndicator.className = 'status-indicator';
            resultCount.textContent = '';
        });

        // Reset database
        resetBtn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to reset the database? This will delete all data and recreate sample tables.')) {
                return;
            }

            setLoading(true);
            
            try {
                const response = await fetch('/sql-practice/reset', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    displayResult({
                        success: true,
                        result: { message: data.message }
                    });
                    // Reload schema
                    loadSchema();
                } else {
                    displayResult(data);
                }

            } catch (error) {
                displayResult({
                    success: false,
                    error: 'Network error: ' + error.message
                });
            }
            
            setLoading(false);
        });

        // Display query results
        function displayResult(data) {
            if (data.success) {
                statusIndicator.className = 'status-indicator status-success';
                
                if (data.result && data.result.data && Array.isArray(data.result.data)) {
                    // Display table data
                    const rows = data.result.data;
                    if (rows.length > 0) {
                        const table = createTable(rows);
                        resultContainer.innerHTML = table;
                        resultCount.textContent = `${rows.length} row(s)`;
                    } else {
                        resultContainer.innerHTML = '<div class="success-message">Query executed successfully. No rows returned.</div>';
                        resultCount.textContent = '0 rows';
                    }
                } else if (data.result && data.result.affectedRows !== undefined) {
                    // Display affected rows for INSERT/UPDATE/DELETE
                    resultContainer.innerHTML = `<div class="success-message">Query executed successfully. ${data.result.affectedRows} row(s) affected.</div>`;
                    resultCount.textContent = `${data.result.affectedRows} affected`;
                } else if (data.result && data.result.message) {
                    // Display message
                    resultContainer.innerHTML = `<div class="success-message">${data.result.message}</div>`;
                    resultCount.textContent = '';
                } else {
                    resultContainer.innerHTML = '<div class="success-message">Query executed successfully.</div>';
                    resultCount.textContent = '';
                }
            } else {
                statusIndicator.className = 'status-indicator status-error';
                resultContainer.innerHTML = `<div class="error-message"><strong>Error:</strong> ${data.error}</div>`;
                resultCount.textContent = 'Error';
            }
        }

        // Create HTML table from data
        function createTable(rows) {
            if (rows.length === 0) return '<p>No data</p>';
            
            const columns = Object.keys(rows[0]);
            let html = '<table class="result-table"><thead><tr>';
            
            columns.forEach(col => {
                html += `<th>${col}</th>`;
            });
            
            html += '</tr></thead><tbody>';
            
            rows.forEach(row => {
                html += '<tr>';
                columns.forEach(col => {
                    const value = row[col];
                    html += `<td>${value !== null ? value : '<em>NULL</em>'}</td>`;
                });
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            return html;
        }

        // Set loading state
        function setLoading(loading) {
            if (loading) {
                executeText.style.display = 'none';
                loadingSpinner.style.display = 'inline-block';
                executeBtn.disabled = true;
            } else {
                executeText.style.display = 'inline';
                loadingSpinner.style.display = 'none';
                executeBtn.disabled = false;
            }
        }

        // Load sample queries
        async function loadSamples() {
            try {
                const response = await fetch('/sql-practice/samples');
                const samples = await response.json();
                
                let html = '';
                for (const [title, sample] of Object.entries(samples)) {
                    html += `
                        <div class="sample-query" onclick="insertSample('${sample.sql.replace(/'/g, "\\'")}')">
                            <div class="sample-query-title">${title}</div>
                            <div class="sample-query-desc">${sample.description}</div>
                        </div>
                    `;
                }
                
                samplesContainer.innerHTML = html;
                
            } catch (error) {
                samplesContainer.innerHTML = '<p style="color: #e74c3c;">Failed to load samples</p>';
            }
        }

        // Insert sample query into textarea
        function insertSample(sql) {
            sqlTextarea.value = sql;
            sqlTextarea.focus();
        }

        // Load schema
        async function loadSchema() {
            try {
                const response = await fetch('/sql-practice/schema');
                const schema = await response.json();
                
                let html = '';
                for (const [tableName, columns] of Object.entries(schema)) {
                    html += `<div class="schema-table">`;
                    html += `<div class="schema-table-header">${tableName}</div>`;
                    columns.forEach(column => {
                        html += `<div class="schema-column">${column.name} - ${column.type}</div>`;
                    });
                    html += `</div>`;
                }
                
                document.getElementById('schemaContainer').innerHTML = html || '<p style="color: #7f8c8d;">No tables found</p>';
                
            } catch (error) {
                console.error('Failed to load schema:', error);
            }
        }

        // Keyboard shortcuts
        sqlTextarea.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                executeBtn.click();
            }
        });

        // Initialize
        loadSamples();
        if (!document.getElementById('schemaContainer').innerHTML.includes('Loading schema')) {
            loadSchema();
        }
    </script>
</body>
</html>