<!DOCTYPE html>
<html>
<head>
    <title>Document Approval</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 20px;
        }
    </style>
</head>
<body>
    <h2>Document Pending Approval</h2>

    <p>Dear Sir/Madam,</p>

    <!-- Content / Isi Pesan -->
    <p>{!! nl2br(e($customMessage)) !!}</p>

    <p>Please log in to the system to review the document and take the necessary action.</p>

    <!-- === BUTTON LOGIN (Email Friendly) === -->
    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 25px 0;">
        <tr>
            <td align="center" bgcolor="#007bff" style="border-radius: 6px;">
                <a href="https://exa-web-approval.test/" 
                   target="_blank"
                   style="display: inline-block; 
                          padding: 14px 32px; 
                          background-color: #007bff; 
                          color: #ffffff; 
                          text-decoration: none; 
                          font-weight: bold; 
                          font-size: 16px; 
                          border-radius: 6px; 
                          font-family: Arial, sans-serif;">
                    Login
                </a>
            </td>
        </tr>
    </table>
    <!-- ==================================== -->

    <p>
        Best regards,<br>
        Document Management System
    </p>
</body>
</html>