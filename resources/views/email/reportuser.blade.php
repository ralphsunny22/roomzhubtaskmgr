<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report User - Wavezio</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-height: 50px;
        }
        h2 {
            color: #333333;
        }
        p {
            color: #555555;
            line-height: 1.6;
        }
        .details-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .details-table th, .details-table td {
            padding: 8px 12px;
            border: 1px solid #eee;
            text-align: left;
        }
        .details-table th {
            background-color: #f4f4f4;
            color: #333;
        }
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #007BFF;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="logo">
            <!--<img src="https://rentalspaces.wavezio.com/assets/images/wavezio-logo.svg" alt="Wavezio Logo">-->
            <h1 style="color: #1C9E45;">Wavezio.</h1>
        </div>
        <h2>Report against user: {{ $reporteeName }}</h2>
        <table class="details-table">
            <tr>
                <th>Full Name</th>
                <td>{{ $reporteeName }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $reporteeEmail ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $reporteePhone ?? 'N/A' }}</td>
            </tr>

        </table>
        <p>This report was made by <strong>{{ $reporterName }}</strong></p>
        <table class="details-table">
            <tr>
                <th>Full Name</th>
                <td>{{ $reporterName }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $reporterEmail ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $reporterPhone ?? 'N/A' }}</td>
            </tr>

        </table>
        <div>
            <h4 style="text-align: center;">Details</h4>
            <p style="text-align: center;">
            {{ $content ? $content : 'N/A' }}
            </p>
        </div>

        <p>Task Summary</p>
        <table class="details-table">
            <tr>
                <th>Task Title</th>
                <td>{{ $taskTitle }}</td>
            </tr>
            <tr>
                <th>Task Location</th>
                <td>{{ $taskAddress ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Task Status</th>
                <td>{{ $clientTaskStatus ?? 'N/A' }}</td>
            </tr>

        </table>
        <p class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
