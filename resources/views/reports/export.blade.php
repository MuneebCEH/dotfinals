<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leads Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .stats {
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }
        .categories {
            margin-bottom: 30px;
        }
        .category-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Leads Performance Report</h1>
        <p>Generated for: {{ $user->name }}</p>
    </div>

    <div class="stats">
        <h2>Key Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Total Leads</div>
                <div class="stat-value">{{ $totalLeads }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Active Leads</div>
                <div class="stat-value">{{ $activeLeads }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Converted Leads</div>
                <div class="stat-value">{{ $convertedLeads }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Conversion Rate</div>
                <div class="stat-value">{{ $conversionRate }}%</div>
            </div>
        </div>
    </div>

    <div class="categories">
        <h2>Top Categories</h2>
        @foreach($topCategories as $category)
            <div class="category-item">
                <span>{{ $category->name }}</span>
                <span>{{ $category->leads_count }} leads</span>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Report generated on: {{ $generatedAt }}</p>
    </div>
</body>
</html>
