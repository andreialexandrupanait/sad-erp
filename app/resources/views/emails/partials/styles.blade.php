<style>
    /* Base Reset & Typography */
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: #334155;
        margin: 0;
        padding: 0;
        background-color: #f8fafc;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Main Container */
    .container {
        max-width: 600px;
        margin: 32px auto;
        background-color: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Header */
    .header {
        text-align: center;
        padding: 32px 40px 24px;
    }
    .logo {
        font-size: 22px;
        font-weight: 700;
        color: #1e40af;
        letter-spacing: -0.5px;
    }

    /* Status Badge - Subtle pill-style badge instead of heavy banner */
    .status-badge-container {
        text-align: center;
        padding: 0 40px 24px;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .status-badge .status-icon {
        font-size: 16px;
        line-height: 1;
    }
    .status-badge.status-sent {
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .status-badge.status-accepted {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .status-badge.status-rejected {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .status-badge.status-modified {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fcd34d;
    }
    .status-badge.status-viewed {
        background-color: #f5f3ff;
        color: #7c3aed;
        border: 1px solid #c4b5fd;
    }

    /* Legacy status-banner support (backward compatible) */
    .status-banner {
        padding: 0 40px 24px;
        text-align: center;
    }
    .status-banner > span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .status-banner.status-sent > span {
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .status-banner.status-accepted > span {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .status-banner.status-rejected > span {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .status-banner.status-modified > span {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fcd34d;
    }
    .status-banner.status-viewed > span {
        background-color: #f5f3ff;
        color: #7c3aed;
        border: 1px solid #c4b5fd;
    }
    .status-icon {
        font-size: 16px;
        line-height: 1;
    }

    /* Content Area */
    .content {
        padding: 8px 40px 40px;
    }

    /* Typography */
    h1 {
        color: #0f172a;
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 12px 0;
        line-height: 1.3;
        letter-spacing: -0.3px;
    }
    .greeting {
        font-size: 15px;
        color: #475569;
        margin-bottom: 8px;
    }
    .message {
        font-size: 15px;
        color: #64748b;
        margin-bottom: 28px;
        line-height: 1.7;
    }

    /* Info Box - Subject/Project Title */
    .info-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin: 0 0 24px 0;
    }
    .info-box p {
        margin: 0;
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
    }
    .info-box strong {
        color: #334155;
        font-weight: 600;
    }
    .info-box.warning {
        background-color: #fffbeb;
        border-color: #fde68a;
    }
    .info-box.warning p {
        color: #92400e;
    }
    .info-box.success {
        background-color: #ecfdf5;
        border-color: #a7f3d0;
    }
    .info-box.success p {
        color: #065f46;
    }
    .info-box.danger {
        background-color: #fef2f2;
        border-color: #fecaca;
    }
    .info-box.danger p {
        color: #991b1b;
    }

    /* Details Card */
    .details-box {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 0;
        margin-bottom: 28px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
    }
    .detail-value {
        font-weight: 600;
        font-size: 14px;
        color: #0f172a;
        text-align: right;
    }
    .detail-value.highlight {
        color: #059669;
        font-size: 15px;
    }
    .detail-value.text-danger {
        color: #dc2626;
    }

    /* Total Amount Section - Softer design */
    .total-box {
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        margin: 0;
        padding: 0;
    }
    .total-box .detail-row {
        padding: 20px;
        border-bottom: none;
        background-color: transparent;
    }
    .total-box .detail-label {
        color: #475569;
        font-size: 14px;
        font-weight: 600;
    }
    .total-box .detail-value {
        color: #0f172a;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    /* Alternative: Prominent Total (for emphasis) */
    .total-prominent {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        border-radius: 12px;
        padding: 20px 24px;
        margin: 24px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .total-prominent .total-label {
        color: rgba(255,255,255,0.85);
        font-size: 14px;
        font-weight: 500;
    }
    .total-prominent .total-value {
        color: #ffffff;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    /* CTA Button */
    .btn-container {
        text-align: center;
        margin: 32px 0;
    }
    .btn {
        display: inline-block;
        text-decoration: none;
        padding: 14px 36px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.2s ease;
    }
    .btn-primary {
        background-color: #2563eb;
        color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }
    .btn-secondary {
        background-color: #f8fafc;
        color: #334155 !important;
        border: 1px solid #e2e8f0;
    }

    /* Services List */
    .services-list {
        margin: 28px 0;
        background-color: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .section-header {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 16px 20px 12px;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        margin: 0;
    }
    .service-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }
    .service-item:last-of-type {
        border-bottom: none;
    }
    .service-name {
        color: #334155;
        font-weight: 500;
        flex: 1;
        padding-right: 16px;
        line-height: 1.4;
    }
    .service-price {
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
        font-size: 14px;
    }
    .services-total {
        padding: 16px 20px;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .services-total-label {
        font-weight: 600;
        color: #334155;
        font-size: 14px;
    }
    .services-total-value {
        font-weight: 700;
        color: #0f172a;
        font-size: 16px;
    }

    /* Divider */
    .divider {
        height: 1px;
        background-color: #e2e8f0;
        margin: 28px 0;
    }

    /* Contact Section */
    .contact-section {
        text-align: center;
        padding-top: 28px;
        border-top: 1px solid #e2e8f0;
        margin-top: 8px;
    }
    .contact-label {
        color: #94a3b8;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .contact-links a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
    }
    .contact-separator {
        color: #e2e8f0;
        margin: 0 12px;
    }

    /* Footer */
    .footer {
        background-color: #f8fafc;
        padding: 24px 40px;
        text-align: center;
        border-top: 1px solid #e2e8f0;
    }
    .footer p {
        margin: 6px 0;
        font-size: 12px;
        color: #94a3b8;
    }
    .footer a {
        color: #64748b;
        text-decoration: none;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-success {
        background-color: #ecfdf5;
        color: #059669;
    }
    .badge-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    .badge-warning {
        background-color: #fffbeb;
        color: #d97706;
    }
    .badge-info {
        background-color: #eff6ff;
        color: #2563eb;
    }

    /* Signature */
    .signature {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
    }
    .signature p {
        margin: 4px 0;
        font-size: 14px;
        color: #64748b;
    }
    .signature strong {
        color: #334155;
    }

    /* Mobile Responsive */
    @media only screen and (max-width: 620px) {
        .container {
            margin: 16px;
            border-radius: 12px;
        }
        .header {
            padding: 24px 24px 20px;
        }
        .status-badge-container,
        .status-banner {
            padding: 0 24px 20px;
        }
        .content {
            padding: 8px 24px 32px;
        }
        .detail-row {
            padding: 14px 16px;
        }
        .total-box .detail-row {
            padding: 16px;
        }
        .service-item {
            padding: 14px 16px;
        }
        .section-header {
            padding: 14px 16px 10px;
        }
        .services-total {
            padding: 14px 16px;
        }
        .footer {
            padding: 20px 24px;
        }
        h1 {
            font-size: 22px;
        }
    }
</style>
