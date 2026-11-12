<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Customer Bills Statement - Crownora Group</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #f6f3ee; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1c1917; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f6f3ee; padding-top: 24px; padding-bottom: 40px; }
        .main-container { max-width: 680px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #ebd9c8; }
        .header { background: linear-gradient(135deg, #811a26 0%, #571018 100%); padding: 36px 32px 30px 32px; text-align: center; color: #ffffff; }
        .logo-text { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin: 0; color: #ffffff; font-family: 'Georgia', serif; }
        .tagline { font-size: 13px; color: #fed7aa; margin: 6px 0 0 0; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
        .statement-badge { display: inline-block; background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); border-radius: 30px; padding: 6px 18px; margin-top: 18px; font-size: 13px; font-weight: 700; color: #ffffff; letter-spacing: 0.5px; }
        .content { padding: 32px; }
        .intro-title { font-size: 20px; font-weight: 800; color: #1c1917; margin: 0 0 6px 0; }
        .intro-sub { font-size: 13px; color: #78716c; margin: 0 0 24px 0; }
        .metrics-grid { width: 100%; border-collapse: separate; border-spacing: 10px; margin-bottom: 28px; }
        .metric-card { background-color: #fbf9f6; border: 1px solid #e7dfd5; border-radius: 14px; padding: 16px; text-align: left; }
        .metric-card-payable { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 1px solid #fdba74; border-radius: 14px; padding: 16px; text-align: left; }
        .metric-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #78716c; font-weight: 700; margin: 0 0 4px 0; }
        .metric-value { font-size: 20px; font-weight: 800; color: #1c1917; margin: 0; }
        .metric-payable-value { font-size: 22px; font-weight: 900; color: #811a26; margin: 0; }
        .metric-sub { font-size: 11px; color: #a8a29e; margin-top: 4px; }
        .section-header { font-size: 16px; font-weight: 800; color: #1c1917; margin: 28px 0 14px 0; padding-bottom: 8px; border-bottom: 2px solid #f3ebe1; display: flex; align-items: center; justify-content: space-between; }
        .bills-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        .bills-table th { background-color: #fbf9f6; padding: 12px 10px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; border-bottom: 2px solid #e7dfd5; }
        .bills-table td { padding: 14px 10px; border-bottom: 1px solid #f0e9df; vertical-align: middle; }
        .cust-name { font-weight: 700; color: #1c1917; font-size: 13px; margin: 0; }
        .cust-meta { font-size: 11px; color: #78716c; margin: 3px 0 0 0; }
        .tiffin-tag { display: inline-block; background-color: #f5efe6; border: 1px solid #e7ded2; border-radius: 6px; padding: 2px 7px; font-size: 11px; font-weight: 600; color: #574c43; margin-top: 4px; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; }
        .status-paid { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-unpaid { background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .btn-cta { display: inline-block; background: #811a26; color: #ffffff !important; font-weight: 700; font-size: 14px; text-decoration: none; padding: 14px 30px; border-radius: 12px; margin-top: 24px; text-align: center; box-shadow: 0 4px 14px rgba(129, 26, 38, 0.3); }
        .notes-box { background-color: #fbf9f6; border-left: 4px solid #811a26; border-radius: 8px; padding: 14px 18px; margin: 26px 0 10px 0; font-size: 12px; color: #57534e; line-height: 1.6; }
        .footer { background-color: #fbf9f6; padding: 24px 32px; text-align: center; font-size: 12px; color: #a8a29e; border-top: 1px solid #ebd9c8; line-height: 1.6; }
        @media only screen and (max-width: 600px) {
            .content { padding: 20px 16px; }
            .header { padding: 26px 18px; }
            .metric-card, .metric-card-payable { padding: 12px 10px; }
            .metric-value { font-size: 16px; }
            .metric-payable-value { font-size: 18px; }
            .bills-table th:nth-child(3), .bills-table td:nth-child(3),
            .bills-table th:nth-child(4), .bills-table td:nth-child(4) { display: none; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-container">
            <!-- Header Banner -->
            <div class="header">
                <h1 class="logo-text">CROWNORA GROUP</h1>
                <p class="tagline">Hostel Mess &amp; Daily Tiffin Services</p>
                <div class="statement-badge">
                    🗓️ Monthly Billing Statement &bull; {{ $readableMonth }}
                </div>
            </div>

            <!-- Content Body -->
            <div class="content">
                <h2 class="intro-title">Dear Administrator,</h2>
                <p class="intro-sub">
                    Here is your automated monthly billing summary for <strong>{{ $readableMonth }}</strong> generated on <strong>{{ $generatedAt }}</strong>. This statement reflects all active mess subscribers, advance adjustments, and net amounts payable.
                </p>

                <!-- Financial Metric Cards -->
                <table class="metrics-grid" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="25%" class="metric-card">
                            <p class="metric-label">Subscribers</p>
                            <p class="metric-value">{{ $summary['total_customers'] }}</p>
                            <p class="metric-sub">{{ $summary['unpaid_count'] }} Unpaid &bull; {{ $summary['paid_count'] }} Paid</p>
                        </td>
                        <td width="25%" class="metric-card">
                            <p class="metric-label">Gross Billed</p>
                            <p class="metric-value">₹{{ number_format($summary['total_gross'], 2) }}</p>
                            <p class="metric-sub">Standard monthly fee</p>
                        </td>
                        <td width="25%" class="metric-card">
                            <p class="metric-label">Advance Adjusted</p>
                            <p class="metric-value">-₹{{ number_format($summary['total_advance_deducted'], 2) }}</p>
                            <p class="metric-sub">Deducted from balance</p>
                        </td>
                        <td width="25%" class="metric-card-payable">
                            <p class="metric-label" style="color: #9a3412;">Net Payable</p>
                            <p class="metric-payable-value">₹{{ number_format($summary['total_payable'], 2) }}</p>
                            <p class="metric-sub" style="color: #c2410c; font-weight: 600;">Total Outstanding</p>
                        </td>
                    </tr>
                </table>

                <!-- Customer Bills Breakdown Table -->
                <div class="section-header">
                    <span>📋 Customer-Wise Itemized Bills</span>
                    <span style="font-size: 12px; color: #78716c; font-weight: normal;">{{ count($bills) }} Customer Records</span>
                </div>

                @if(count($bills) === 0)
                    <div style="text-align: center; padding: 30px; background-color: #fbf9f6; border-radius: 12px; color: #78716c; font-size: 13px;">
                        No active monthly mess subscriptions found for this billing period.
                    </div>
                @else
                    <table class="bills-table" width="100%" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="36%">Customer Details</th>
                                <th width="24%">Plan / Mess</th>
                                <th width="14%" style="text-align: right;">Total</th>
                                <th width="14%" style="text-align: right;">Advance</th>
                                <th width="16%" style="text-align: right;">Payable</th>
                                <th width="10%" style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                                <tr>
                                    <td>
                                        <p class="cust-name">{{ $bill->user->name ?? 'Customer #' . $bill->user_id }}</p>
                                        <p class="cust-meta">
                                            @if(!empty($bill->user->phone))
                                                📞 {{ $bill->user->phone }}<br>
                                            @endif
                                            ✉️ {{ $bill->user->email ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td>
                                        @if($bill->user && $bill->user->tiffins && $bill->user->tiffins->count() > 0)
                                            @foreach($bill->user->tiffins as $tiffin)
                                                <div class="tiffin-tag">
                                                    {{ $tiffin->title }} (₹{{ number_format($tiffin->price) }})
                                                </div>
                                            @endforeach
                                        @else
                                            <span style="font-size: 11px; color: #a8a29e;">Monthly Mess Plan</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; font-weight: 600; color: #57534e;">
                                        ₹{{ number_format($bill->total_amount, 2) }}
                                    </td>
                                    <td style="text-align: right; color: #059669; font-weight: 600;">
                                        @if($bill->advance_deducted > 0)
                                            -₹{{ number_format($bill->advance_deducted, 2) }}
                                        @else
                                            ₹0.00
                                        @endif
                                    </td>
                                    <td style="text-align: right; font-weight: 800; color: #811a26; font-size: 14px;">
                                        ₹{{ number_format($bill->payable_amount, 2) }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if(strtolower($bill->status) === 'paid')
                                            <span class="status-badge status-paid">PAID</span>
                                        @else
                                            <span class="status-badge status-unpaid">UNPAID</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #fbf9f6; font-weight: 800;">
                                <td colspan="2" style="padding: 14px 10px; font-size: 12px; text-transform: uppercase; color: #57534e;">
                                    Total Summary ({{ count($bills) }} Customers)
                                </td>
                                <td style="text-align: right; padding: 14px 10px; color: #57534e;">
                                    ₹{{ number_format($summary['total_gross'], 2) }}
                                </td>
                                <td style="text-align: right; padding: 14px 10px; color: #059669;">
                                    -₹{{ number_format($summary['total_advance_deducted'], 2) }}
                                </td>
                                <td style="text-align: right; padding: 14px 10px; color: #811a26; font-size: 15px;">
                                    ₹{{ number_format($summary['total_payable'], 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                <!-- Policy & Automation Note -->
                <div class="notes-box">
                    <strong>📌 Billing Policy &amp; Terms Note:</strong><br>
                    &bull; Monthly mess subscriptions are billed upfront for the whole calendar month.<br>
                    &bull; Any existing advance payment balance on the customer account has been automatically deducted from their bill.<br>
                    &bull; You can update the payment status (Mark as Paid/Unpaid) directly from the Crownora Admin Portal.
                </div>

                <!-- Call to Action -->
                <div style="text-align: center; margin-top: 10px;">
                    <a href="{{ $adminDashboardUrl }}" class="btn-cta" target="_blank">
                        ⚡ Open Crownora Admin Dashboard
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 6px 0; font-weight: 700; color: #57534e;">
                    Crownora Group &bull; Hostel Mess &amp; Tiffin Management System
                </p>
                <p style="margin: 0 0 8px 0;">
                    Automatic monthly billing trigger executed on 1st of every month at 08:00 AM IST.
                </p>
                <p style="margin: 0; font-size: 11px; color: #c4beb5;">
                    &copy; {{ date('Y') }} Crownora Group. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

