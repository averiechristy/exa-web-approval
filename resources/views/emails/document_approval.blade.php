<!DOCTYPE html>
<html>
<head>
    <title>Dokumen Menunggu Persetujuan</title>
</head>
<body>
    <h2>Dokumen Menunggu Persetujuan</h2>
    
    <p>Yth. Bpk/Ibu,</p>
    
    <p> Terdapat dokumen yang memerlukan persetujuan Anda:</p>
    
    <table>
        <tr>
            <td><strong>Nama Dokumen</strong></td>
            <td>: {{ $docName }}</td>
        </tr>
        <tr>
            <td><strong>Tier/Urutan</strong></td>
            <td>: {{ $order }}</td>
        </tr>
    </table>

    <p>Silakan login untuk melihat detail dokumen.</p>
    
    <p>Hormat kami,<br>Sistem Dokumen</p>
</body>
</html>