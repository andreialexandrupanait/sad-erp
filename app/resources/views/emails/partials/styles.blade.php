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
        max-width: 600px;
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
    }
    .status-icon {
        font-size: 20px;
    }
    .status-viewed {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }
    .status-accepted {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    .status-rejected {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    .status-modified {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    .status-sent {
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
    .details-box {
        background-color: #f9fafb;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid #e5e7eb;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .detail-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .detail-row:first-child {
        padding-top: 0;
    }
    .detail-label {
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
    }
    .detail-value {
        font-weight: 600;
        font-size: 14px;
        color: #111827;
        text-align: right;
    }
    .detail-value.highlight {
        color: #059669;
        font-size: 16px;
    }
    .detail-value.text-danger {
        color: #dc2626;
    }
    .total-box {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        color: white;
        margin: 24px -20px -20px;
        padding: 20px;
        border-radius: 0 0 10px 10px;
    }
    .total-box .detail-row {
        border-bottom-color: rgba(255,255,255,0.2);
    }
    .total-box .detail-label {
        color: rgba(255,255,255,0.8);
    }
    .total-box .detail-value {
        color: white;
        font-size: 20px;
    }
    .btn-container {
        text-align: center;
        margin: 28px 0;
    }
    .btn {
        display: inline-block;
        text-decoration: none;
        padding: 14px 32px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.2s;
    }
    .btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white !important;
        box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.4);
    }
    .btn-secondary {
        background-color: #f3f4f6;
        color: #374151 !important;
        border: 1px solid #d1d5db;
    }
    .info-box {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 16px;
        margin: 20px 0;
    }
    .info-box.warning {
        background-color: #fef3c7;
        border-color: #fcd34d;
    }
    .info-box.success {
        background-color: #d1fae5;
        border-color: #6ee7b7;
    }
    .info-box.danger {
        background-color: #fee2e2;
        border-color: #fca5a5;
    }
    .info-box p {
        margin: 0;
        font-size: 14px;
        color: #1f2937;
    }
    .signature {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    .signature p {
        margin: 4px 0;
        font-size: 14px;
        color: #4b5563;
    }
    .signature strong {
        color: #111827;
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
    .section-header {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e2e8f0;
    }
    .services-list {
        margin: 24px 0;
        background: linear-gradient(to bottom, #f8fafc, #ffffff);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
    }
    .service-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }
    .service-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .service-item:first-child {
        padding-top: 0;
    }
    .service-name {
        color: #334155;
        font-weight: 500;
        flex: 1;
        padding-right: 16px;
    }
    .service-price {
        font-weight: 600;
        color: #059669;
        white-space: nowrap;
    }
    .services-total {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .services-total-label {
        font-weight: 600;
        color: #1e293b;
        font-size: 15px;
    }
    .services-total-value {
        font-weight: 700;
        color: #059669;
        font-size: 18px;
    }
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-success {
        background-color: #d1fae5;
        color: #065f46;
    }
    .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }
    .badge-info {
        background-color: #dbeafe;
        color: #1e40af;
    }
</style>
