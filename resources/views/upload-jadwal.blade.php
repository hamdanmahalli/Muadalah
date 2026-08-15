<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Jadwal Pelajaran - Smart TU</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Upload Jadwal TU</h2>
        
        <form action="/import-jadwal" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih File Excel (.xlsx)</label>
                <input type="file" name="file_excel" required class="w-full border border-gray-300 p-2 rounded-lg text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                Unggah Data Jadwal
            </button>
        </form>
    </div>
</body>
</html>