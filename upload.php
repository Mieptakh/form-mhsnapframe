<?php
// Folder untuk menyimpan file dan data
$upload_dir = "uploads/";
$data_file = "uploads/receipt_data.json"; // File JSON untuk menyimpan data receipt
$max_file_size = 10 * 1024 * 1024; // 10MB

// Membuat folder jika belum ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fungsi untuk membuat ID Receipt
function generateReceiptID() {
    $date = date("Ymd"); // Tanggal saat ini (YYYYMMDD)
    $randomNumber = rand(1000, 9999); // Nomor acak
    return "MH-" . $date . "-" . $randomNumber;
}

// Generate ID Receipt
$receipt_id = generateReceiptID();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organization = htmlspecialchars($_POST['organization']);
    $start_date = htmlspecialchars($_POST['start_date']);
    $end_date = htmlspecialchars($_POST['end_date']);
    $path = htmlspecialchars($_POST['path']);
    $caption_template = htmlspecialchars($_POST['caption_template']);

    // Validasi file frame
    $frame_file = $_FILES['frame_file'];
    if ($frame_file['size'] > $max_file_size) {
        die("File frame terlalu besar. Maksimal 10MB.");
    }
    $frame_ext = pathinfo($frame_file['name'], PATHINFO_EXTENSION);
    if (!in_array($frame_ext, ['png', 'jpg', 'jpeg'])) {
        die("Format file frame tidak valid. Hanya PNG, JPG, JPEG yang diperbolehkan.");
    }

    $frame_filename = $upload_dir . "frame_" . time() . "." . $frame_ext;
    move_uploaded_file($frame_file['tmp_name'], $frame_filename);

    // Validasi file bukti follow IG (opsional)
    $ig_filename = null;
    if (!empty($_FILES['ig_follow_proof']['name'])) {
        $ig_proof = $_FILES['ig_follow_proof'];
        $ig_ext = pathinfo($ig_proof['name'], PATHINFO_EXTENSION);
        $ig_filename = $upload_dir . "ig_proof_" . time() . "." . $ig_ext;
        move_uploaded_file($ig_proof['tmp_name'], $ig_filename);
    }

    // Data yang akan disimpan
    $data = [
        'receipt_id' => $receipt_id,
        'organization' => $organization,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'path' => $path,
        'caption_template' => $caption_template,
        'frame_file' => $frame_filename,
        'ig_follow_proof' => $ig_filename,
        'timestamp' => date("Y-m-d H:i:s")
    ];

    // Simpan data ke file JSON
    if (!file_exists($data_file)) {
        file_put_contents($data_file, json_encode([])); // Inisialisasi file JSON
    }
    $current_data = json_decode(file_get_contents($data_file), true);
    $current_data[] = $data;
    file_put_contents($data_file, json_encode($current_data, JSON_PRETTY_PRINT));

    // Generate QR Code untuk ID Receipt
    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($receipt_id) . "&size=200x200";

    // Nomor WhatsApp untuk konfirmasi
    $whatsapp_number = "6285183241229"; // Ganti dengan nomor WhatsApp tujuan
    $whatsapp_message = urlencode("Halo, saya ingin konfirmasi ID Receipt: {$receipt_id}");

    // Tampilkan halaman sukses
    echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Formulir Berhasil</title>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap' rel='stylesheet'>
            <link rel="icon" type="image/x-icon" href="icon/favicon.ico">
           <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #eef2f3, #dfe4ea);
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        overflow: hidden;
    }

    .container {
        width: 100%;
        max-width: 400px;
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    h1 {
        color: #0078D7;
        font-weight: 600;
        font-size: 1.4rem;
        margin-bottom: 10px;
    }

    h3 {
        color: #333;
        font-weight: 500;
        font-size: 1.2rem;
        margin: 5px 0;
    }

    p {
        color: #555;
        line-height: 1.4;
        font-size: 0.85rem;
        margin: 5px 0;
    }

    .qr-code {
        margin: 10px 0;
    }

    .qr-code img {
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 10px;
        max-width: 100px;
        height: auto;
    }

    .details {
        margin-top: 10px;
        text-align: left;
        font-size: 0.85rem;
    }

    .details p {
        margin: 3px 0;
    }

    .btn {
        display: inline-block;
        padding: 8px 15px;
        background: linear-gradient(135deg, #25D366, #1da64c);
        color: #fff;
        text-decoration: none;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: background 0.3s ease, transform 0.2s ease;
        margin-top: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
        background: linear-gradient(135deg, #1da64c, #25D366);
        transform: scale(1.03);
    }

    .footer {
        margin-top: 15px;
        font-size: 0.75rem;
        color: #999;
    }

    @media (max-width: 480px) {
        h1 {
            font-size: 1.2rem;
        }

        h3 {
            font-size: 1rem;
        }

        p,
        .details p {
            font-size: 0.8rem;
        }

        .btn {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
    }
        
    a {
        color: #0078D7;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    a:hover {
        color: #0056a3;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .btn {
        display: inline-block;
        padding: 8px 15px;
        background: linear-gradient(135deg, #25D366, #1da64c);
        color: #fff;
        text-decoration: none;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: background 0.3s ease, transform 0.2s ease;
        margin-top: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
        background: linear-gradient(135deg, #1da64c, #25D366);
        transform: scale(1.03);
    }

    .details a {
        color: #28a745;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: underline;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    .details a:hover {
        color: #1c7d33;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
</style>

        </head>
        <body>
            <div class='container'>
                <h1>Formulir Berhasil Dikirim!</h1>
                <p>ID Receipt Anda:</p>
                <h3>{$receipt_id}</h3>
                <div class='qr-code'>
                    <img src='{$qr_code_url}' alt='QR Code'>
                </div>
                <p>Simpan ID Receipt ini untuk verifikasi: <strong>{$receipt_id}</strong></p>
                <a href='https://wa.me/{$whatsapp_number}?text={$whatsapp_message}' class='btn'>Konfirmasi via WhatsApp</a>
                <hr>
                <div class='details'>
                    <p><strong>Organisasi:</strong> {$organization}</p>
                    <p><strong>Periode:</strong> {$start_date} hingga {$end_date}</p>
                    <p><strong>Caption Template:</strong> {$caption_template}</p>
                    <p><strong>File Frame:</strong> <a href='{$frame_filename}' target='_blank'>Download Frame</a></p>
                    " . (!empty($ig_filename) ? "<p><strong>Bukti Follow IG:</strong> <a href='{$ig_filename}' target='_blank'>Download Bukti</a></p>" : "") . "
                </div>
                <div class='footer'>
                    &copy; " . date("Y") . " MHSnapFrame. All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
}
?>
