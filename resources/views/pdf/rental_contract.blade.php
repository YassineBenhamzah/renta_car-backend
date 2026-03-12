<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Contract #{{ $rental->id }}</title>
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563EB;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #2563EB;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-info {
            font-size: 10px;
            color: #555;
        }

        .contract-title {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            color: #111;
        }

        .contract-meta {
            text-align: right;
            font-size: 11px;
        }

        .section-title {
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 11px;
            font-weight: bold;
            padding: 5px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            border-left: 3px solid #2563EB;
        }

        .details-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .details-col {
            width: 48%;
            vertical-align: top;
        }

        .info-row {
            margin-bottom: 3px;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 80px;
            display: inline-block;
        }

        .value {
            color: #000;
        }

        .rent-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .rent-table th {
            background-color: green;
            color: #fff;
            padding: 6px;
            text-align: left;
        }

        .rent-table td {
            border-bottom: 1px solid #eee;
            padding: 6px;
        }

        .total-row td {
            border-top: 1px solid #999;
            font-weight: bold;
            font-size: 12px;
            background-color: #f8fafc;
        }

        .terms {
            font-size: 9px;
            color: #666;
            text-align: justify;
            margin-bottom: 20px;
            line-height: 1.2;
            border: 1px solid #eee;
            padding: 8px;
            background: #fafafa;
        }

        .signatures {
            width: 100%;
            margin-top: 20px;
        }

        .sig-box {
            width: 45%;
            border: 1px solid #ccc;
            height: 80px;
            padding: 5px;
            background: #fff;
            position: relative;
        }

        .sig-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 30px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
        }

        .sig-name {
            position: absolute;
            bottom: 5px;
            right: 5px;
            font-size: 10px;
            color: #555;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="logo">RENTA CAR</div>
                <div class="company-info">
                    123 Rental Avenue, Casablanca, 20000<br>
                    support@rentacar.com | +212 600 000 000<br>
                    RC: 12345 | ICE: 987654321
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="contract-title">RENTAL AGREEMENT</div>
                <div class="contract-meta">
                    <strong>Contract #:</strong> RENTAL-{{ str_pad($rental->id, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Date:</strong> {{ date('d F Y') }}<br>
                    <strong>Agent:</strong> {{ $rental->approver ? $rental->approver->name : 'System/Pending' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Two Column Details -->
    <table class="details-table">
        <tr>
            <td class="details-col" style="padding-right: 15px;">
                <div class="section-title">CLIENT INFORMATION</div>
                <div class="info-row"><span class="label">Name:</span> <span
                        class="value">{{ $rental->user->name }}</span></div>
                <div class="info-row"><span class="label">CIN:</span> <span
                        class="value">{{ $rental->user->cin ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="label">Permis:</span> <span
                        class="value">{{ $rental->user->permis ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="label">Phone:</span> <span
                        class="value">{{ $rental->user->phone ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="label">Email:</span> <span
                        class="value">{{ $rental->user->email }}</span></div>
                <div class="info-row"><span class="label">Address:</span> <span
                        class="value">{{ $rental->user->address ?? 'N/A' }}</span></div>
            </td>
            <td class="details-col" style="padding-left: 15px;">
                <div class="section-title">VEHICLE DETAILS</div>
                <div class="info-row"><span class="label">Vehicle:</span> <span class="value"
                        style="font-weight: bold;">{{ $rental->car->brand }} {{ $rental->car->model }}</span></div>
                <div class="info-row"><span class="label">Type:</span> <span class="value">{{ $rental->car->type }} /
                        {{ $rental->car->engine_type }}</span></div>
                <div class="info-row"><span class="label">Plate:</span> <span
                        class="value">{{ $rental->car->plate_number ?? 'Pending' }}</span></div>
                <div class="info-row"><span class="label">Trans.:</span> <span
                        class="value">{{ $rental->car->transmission }}</span></div>
                <div class="info-row"><span class="label">Doors:</span> <span class="value">{{ $rental->car->doors }}
                        Doors / {{ $rental->car->passengers }} Seats</span></div>
                <div class="info-row"><span class="label">Color:</span> <span
                        class="value">{{ $rental->car->color ?? 'Standard' }}</span></div>
            </td>
        </tr>
    </table>

    <!-- Financials -->
    <div class="section-title">RENTAL DETAILS & PAYMENT</div>
    <table class="rent-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Price/Day</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Vehicle Rental - {{ $rental->car->brand }} {{ $rental->car->model }}</td>
                <td>{{ date('d-m-Y H:i', strtotime($rental->start_date)) }}</td>
                <td>{{ date('d-m-Y H:i', strtotime($rental->end_date)) }}</td>
                <td>{{ $rental->days }}</td>
                <td>{{ number_format($rental->car->price_per_day, 2) }} MAD</td>
                <td>{{ number_format($rental->total_price, 2) }} MAD</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="border: none;"></td>
                <td style="text-align: right;">GRAND TOTAL:</td>
                <td>{{ number_format($rental->total_price, 2) }} MAD</td>
            </tr>
            <tr style="background-color: #fff;">
                <td colspan="4" style="border: none;">
                    <strong>Payment Status:</strong> <span
                        style="text-transform: uppercase; color: {{ $rental->payment_status == 'completed' ? 'green' : 'orange' }};">{{ $rental->payment_status ?? 'Pending' }}</span>
                    |
                    <strong>Method:</strong> <span
                        style="text-transform: uppercase;">{{ $rental->payment_method ?? 'N/A' }}</span>
                </td>
                <td colspan="2" style="border: none;"></td>
            </tr>
        </tbody>
    </table>

    <!-- Terms Compact -->
    <div class="section-title">TERMS & CONDITIONS (ABBREVIATED)</div>
    <div class="terms">
        1. <strong>Vehicle Condition:</strong> The Renter acknowledges receiving the vehicle in good working condition
        and agrees to return it in the same condition.<br>
        2. <strong>Insurance:</strong> The rental includes basic insurance. The Renter is liable for any damage or loss
        not covered by insurance.<br>
        3. <strong>Fuel:</strong> Vehicle must be returned with the same fuel level as rented. Differences will be
        charged.<br>
        4. <strong>Prohibited Use:</strong> The vehicle shall not be used for illegal purposes, racing, or
        sub-renting.<br>
        5. <strong>Jurisdiction:</strong> Any disputes arising from this contract shall be settled in the courts of
        Casablanca.
    </div>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td style="width: 48%; padding-right: 10px;">
                <div class="sig-box">
                    <div class="sig-title">CLIENT SIGNATURE</div>
                    <div style="font-size: 9px; color: #777;">I accept the terms and conditions.</div>
                    <div class="sig-name">{{ $rental->user->name }}</div>
                </div>
            </td>
            <td style="width: 4%;">&nbsp;</td>
            <td style="width: 48%; padding-left: 10px;">
                <div class="sig-box">
                    <div class="sig-title">AGENT / COMPANY STAMP</div>
                    <div style="font-size: 9px; color: #777;">Approved on {{ date('d/m/Y') }}</div>
                    <div class="sig-name">{{ $rental->approver ? $rental->approver->name : 'Authorized Agent' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Renta Car Systems &copy; {{ date('Y') }} - Generated on {{ date('d-m-Y H:i:s') }}
    </div>

</body>

</html>
