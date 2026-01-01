<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Access Credentials for :site', ['site' => $siteName]) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            text-align: center;
            padding: 30px 40px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #1e40af;
            letter-spacing: -0.5px;
        }
        .status-banner {
            padding: 16px 40px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        .content {
            padding: 30px 40px;
        }
        h1 {
            color: #111827;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 16px 0;
            line-height: 1.3;
        }
        .message {
            font-size: 15px;
            color: #4b5563;
            margin-bottom: 24px;
            line-height: 1.7;
        }
        .custom-message {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #0c4a6e;
            white-space: pre-wrap;
        }
        .credentials-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        .credentials-table th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
            text-align: left;
            padding: 12px 16px;
            border-bottom: 2px solid #e5e7eb;
        }
        .credentials-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .credentials-table tr:last-child td {
            border-bottom: none;
        }
        .credentials-table tr:hover {
            background-color: #f9fafb;
        }
        .platform-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background-color: #dbeafe;
            color: #1e40af;
        }
        .credential-value {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            color: #111827;
            word-break: break-all;
        }
        .url-link {
            color: #2563eb;
            text-decoration: none;
            word-break: break-all;
        }
        .url-link:hover {
            text-decoration: underline;
        }
        .security-notice {
            background-color: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
        }
        .security-notice h3 {
            color: #92400e;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }
        .security-notice p {
            color: #92400e;
            font-size: 13px;
            margin: 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 6px 0;
            font-size: 12px;
            color: #9ca3af;
        }
        .footer a {
            color: #6b7280;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }
            .content, .header, .footer {
                padding: 20px;
            }
            .credentials-table th,
            .credentials-table td {
                padding: 10px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ $appName }}</div>
        </div>

        <div class="status-banner">
            <span>{{ __('Access Credentials') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('Credentials for :site', ['site' => $siteName]) }}</h1>

            @if($customMessage)
                <div class="custom-message">{{ $customMessage }}</div>
            @else
                <p class="message">
                    {{ __('Below are the access credentials for your platform. Please store these securely and do not share them with unauthorized individuals.') }}
                </p>
            @endif

            <table class="credentials-table">
                <thead>
                    <tr>
                        <th>{{ __('Platform') }}</th>
                        <th>{{ __('Username') }}</th>
                        <th>{{ __('Password') }}</th>
                        <th>{{ __('URL') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($credentials as $credential)
                    <tr>
                        <td>
                            <span class="platform-badge">{{ $credential->platform }}</span>
                        </td>
                        <td>
                            <span class="credential-value">{{ $credential->username ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="credential-value">{{ $credential->password ?? '-' }}</span>
                        </td>
                        <td>
                            @if($credential->url)
                                <a href="{{ $credential->url }}" class="url-link" target="_blank">{{ $credential->url }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="security-notice">
                <h3>{{ __('Security Reminder') }}</h3>
                <p>{{ __('Please keep these credentials confidential. We recommend changing passwords periodically and using a password manager for secure storage. Do not share this email with unauthorized persons.') }}</p>
            </div>
        </div>

        <div class="footer">
            <p>{{ __('This email was sent from') }} <a href="{{ $appUrl }}">{{ $appName }}</a></p>
            <p>&copy; {{ date('Y') }} {{ $appName }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
