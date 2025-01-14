<?php
$ocrResult = ''; // Variabel untuk hasil OCR

// Cek apakah file hasil OCR ada dan hapus file tersebut
$existingOcrFiles = glob('uploads/*.txt'); // Menemukan semua file .txt di folder uploads
foreach ($existingOcrFiles as $ocrFile) {
    unlink($ocrFile); // Hapus file OCR yang ada
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true); // Pastikan folder uploads ada

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'tif', 'bmp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Simpan file di folder uploads/
            $destPath = $uploadDir . uniqid() . '_' . $fileName;
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                try {
                    $outputFile = $destPath;
                    $command = "\"C:\\Program Files\\Tesseract-OCR\\tesseract.exe\" " . escapeshellarg($destPath) . " " . escapeshellarg($outputFile) . " -l eng";
                    exec($command . " 2>&1", $output, $return_var);

                    // Cek apakah file hasil OCR ada
                    if (file_exists($outputFile . ".txt")) {
                        $ocrResult = file_get_contents($outputFile . ".txt"); // Membaca hasil OCR dari file
                    } else {
                        $ocrResult = "File hasil OCR tidak ditemukan.";
                    }
                } catch (Exception $e) {
                    $ocrResult = "Terjadi kesalahan: " . $e->getMessage();
                }
            } else {
                $ocrResult = "Gagal memindahkan file ke direktori tujuan.";
            }
        } else {
            $ocrResult = "Format file tidak didukung.";
        }
    } else {
        $ocrResult = "Tidak ada file yang diunggah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload dan Convert Gambar ke Teks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
            display: block;
            text-align: left;
        }

        input[type="file"] {
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            font-size: 14px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        textarea {
            width: 100%;
            height: 200px;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            color: #333;
            resize: none;
            background-color: #f9f9f9;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .result {
            margin-top: 20px;
            font-size: 16px;
            color: #28a745;
        }

        .error {
            margin-top: 20px;
            font-size: 16px;
            color: #dc3545;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Gambar ke Text (OCR)</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="image">Pilih Gambar:</label>
            <input type="file" name="image" id="image" accept="image/*" required>
            <button type="submit">Upload dan Convert</button>
        </form>

            <?php if (isset($ocrResult) && $ocrResult): ?>
                <textarea id="txt_area" readonly><?php echo htmlspecialchars($ocrResult); ?></textarea>
                <button class="clear-button" onclick="clearTextArea()">Clear</button>
                <div class="result">Hasil OCR berhasil ditampilkan di atas!</div>
            <?php else: ?>
                <div class="error">Tidak ada hasil OCR yang tersedia. Coba lagi dengan gambar yang jelas.</div>
            <?php endif; ?>
        
        <div class="footer">
            <p>dibikin-bikin oleh <a href="#">D.A.M.Z</a></p>
        </div>
        
    </div>
    <script>
        // Function to clear the content of the textarea
        function clearTextArea() {
            document.getElementById("txt_area").value = ""; // Clear the text area
        }
    </script>
</body>
</html>

