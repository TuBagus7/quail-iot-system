<!DOCTYPE html>
<html>

<head>
    <title>Notifikasi Kualitas Air</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        .header {
            background-color: #ef4444;
            color: white;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .data-box {
            background-color: #f9fafb;
            border: 1px dashed #cbd5e1;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Status Kualitas Air BURUK!</h1>
        </div>
        <div class="content">
            <p>Halo Bos! 👋</p>
            <p>Sistem baru aja ngedeteksi kalau kualitas air di kandang puyuh lagi **BURUK** nih. Sebaiknya segera dicek
                ya biar puyuh-puyuhnya tetep sehat.</p>

            <div class="data-box">
                <strong>Detail Kondisi Saat Ini:</strong>
                <ul>
                    <li><strong>Status:</strong> {{ $data['item_teks'] }}</li>
                    <li><strong>Kekeruhan:</strong> {{ $data['gauge3'] }} NTU</li>
                    <li><strong>Suhu:</strong> {{ $data['gauge1'] }} °C</li>
                    <li><strong>Volume Air:</strong> {{ $data['gauge2'] }} ml</li>
                </ul>
            </div>

            <p>Jangan lupa ganti airnya kalau emang udah keruh banget ya Bang! 💧</p>
        </div>
        <div class="footer">
            Sistem Monitoring Kandang Puyuh - QuailYes IoT
        </div>
    </div>
</body>

</html>