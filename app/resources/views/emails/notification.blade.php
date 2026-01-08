<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }} - Notification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid #e5e5e5;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .priority-bar {
            height: 4px;
        }
        .priority-urgent { background-color: #d32f2f; }
        .priority-high { background-color: #ff9800; }
        .priority-normal { background-color: #2196f3; }
        .priority-low { background-color: #4caf50; }
        .content {
            padding: 24px;
        }
        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .category-domain { background-color: #e3f2fd; color: #1565c0; }
        .category-subscription { background-color: #f3e5f5; color: #7b1fa2; }
        .category-financial { background-color: #e8f5e9; color: #2e7d32; }
        .category-client { background-color: #fff3e0; color: #ef6c00; }
        .category-system { background-color: #fce4ec; color: #c2185b; }
        .title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 12px 0;
        }
        .body {
            color: #555;
            margin-bottom: 20px;
        }
        .fields {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .field {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .field:last-child {
            border-bottom: none;
        }
        .field-label {
            font-weight: 500;
            color: #666;
        }
        .field-value {
            color: #333;
            text-align: right;
        }
        .action-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1a1a1a;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
        }
        .action-button:hover {
            background-color: #333;
        }
        .footer {
            padding: 20px 24px;
            background-color: #f9f9f9;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #e5e5e5;
        }
        .footer a {
            color: #666;
            text-decoration: none;
        }
        .test-banner {
            background-color: #4caf50;
            color: #fff;
            padding: 16px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($isTest)
            <div class="test-banner">
                ✓ This is a test notification - Email is configured correctly!
            </div>
            <div class="priority-bar priority-low"></div>
        @elseif($message)
            <div class="priority-bar priority-{{ $message->getPriority() }}"></div>
        @endif

        <div class="header">
            <h1>{{ $appName }}</h1>
        </div>

        <div class="content">
            @if($isTest)
                <span class="category-badge category-system">Test</span>
                <h2 class="title">Notification System Test</h2>
                <p class="body">
                    This is a test message from your ERP notification system. If you're seeing this email,
                    your email notifications are properly configured and working!
                </p>
                <div class="fields">
                    <div class="field">
                        <span class="field-label">Environment</span>
                        <span class="field-value">{{ config('app.env') }}</span>
                    </div>
                    <div class="field">
                        <span class="field-label">Timestamp</span>
                        <span class="field-value">{{ now()->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="field">
                        <span class="field-label">Mail Driver</span>
                        <span class="field-value">{{ config('mail.default') }}</span>
                    </div>
                </div>
            @elseif($message)
                <span class="category-badge category-{{ $message->getCategory() }}">
                    {{ ucfirst($message->getCategory()) }}
                </span>
                <h2 class="title">{{ $message->getTitle() }}</h2>
                <p class="body">{{ $message->getBody() }}</p>

                @if(count($message->getFields()) > 0)
                    <div class="fields">
                        @foreach($message->getFields() as $field)
                            <div class="field">
                                <span class="field-label">{{ $field['title'] }}</span>
                                <span class="field-value">{{ $field['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($message->getUrl())
                    <p style="text-align: center;">
                        <a href="{{ $message->getUrl() }}" class="action-button">
                            View Details
                        </a>
                    </p>
                @endif
            @endif
        </div>

        <div class="footer">
            <p>
                This notification was sent by <a href="{{ $appUrl }}">{{ $appName }}</a>
            </p>
            <p>
                {{ now()->format('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>
