<!DOCTYPE html>
<html>
<head>
    <title>Document Approval</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; }
        td { padding: 8px 0; }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: black;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Document Pending Approval</h2>

    <p>Dear Sir/Madam,</p>

    @if(!empty($customMessage))
        {!! nl2br($customMessage) !!}
    @else
        <p>This is to inform you that a document is currently awaiting your approval.</p>

        <table>
            <tr>
                <td><strong>Document Name</strong></td>
                <td>: {{ $docName }}</td>
            </tr>
            <tr>
                <td><strong>Approval Level</strong></td>
                <td>: Tier {{ $approval->tier ?? $order }}</td>
            </tr>
        </table>
    @endif

    <p>Please log in to the system to review the document and take the necessary action.</p>

    <!-- Button Login untuk localhost -->
    <a href="http://localhost:8000" class="button" target="_blank">
        Login
    </a>

    <p>
        Best regards,<br>
        Document Management System
    </p>
</body>
</html>