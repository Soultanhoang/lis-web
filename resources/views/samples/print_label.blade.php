<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Tem {{ $sample->sample_code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif; /* Dùng Arial cho dễ đọc số */
            margin: 0;
            padding: 10px;
            font-size: 12px;
        }

        /* Khu vực điều khiển (không in) */
        .controls {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px dashed #ccc;
            background: #f9f9f9;
        }
        .controls label { margin-right: 5px; font-weight: bold; }
        .controls input[type="number"] { width: 60px; margin-right: 10px; padding: 5px; }
        .controls button { cursor: pointer; padding: 5px 10px; margin-right: 5px; }

        /* Khu vực chứa các tem */
        #label-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Khoảng cách giữa các tem trên màn hình */
        }

        /* --- ĐỊNH DẠNG TEM (QUAN TRỌNG) --- */
        .label {
            /* Kích thước tem thực tế (Tuỳ chỉnh theo giấy in của bạn, VD: 50mm x 30mm) */
            width: 250px; 
            /* height: 120px; có thể set chiều cao cố định nếu cần */
            
            border: 1px solid #ddd; /* Viền mờ để nhìn trên web */
            padding: 5px;
            background-color: white;
            box-sizing: border-box;
            
            /* Canh giữa nội dung */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            
            /* Ngắt trang khi in nhiều tem */
            page-break-inside: avoid;
        }

        /* Nội dung bên trong tem */
        .patient-name { 
            font-weight: bold; 
            font-size: 13px; 
            text-transform: uppercase;
            margin-bottom: 2px;
            text-align: center;
        }
        
        .info { 
            font-size: 11px; 
            margin-bottom: 2px;
            text-align: center;
        }

        .sample-code-text {
            font-weight: bold;
            font-size: 14px;
            margin-top: 2px;
        }

        .barcode { 
            margin-top: 2px; 
            width: 100%;
            text-align: center;
        }
        
        /* Ảnh barcode co giãn theo khung */
        .barcode img { 
            max-width: 90%; 
            height: 40px; /* Chiều cao barcode */
        }

        /* Tem mẫu ẩn đi */
        #label-template { display: none; }

        /* --- CSS KHI IN (QUAN TRỌNG NHẤT) --- */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            /* Ẩn nút bấm */
            .controls { display: none !important; }
            
            #label-container {
                display: block; /* Hoặc flex tuỳ máy in */
            }

            .label {
                border: none !important; /* Bỏ viền khi in thật */
                margin: 0; /* Canh lề sát */
                page-break-after: always; /* Mỗi tem là 1 trang (nếu dùng máy in tem chuyên dụng) */
                
                /* Nếu in A4 thì dùng margin-bottom thay vì page-break */
                /* margin-bottom: 10px; */ 
            }
        }
    </style>
</head>
<body>

{{-- Khu vực điều khiển --}}
<div class="controls no-print">
    <label for="quantity">Số lượng tem:</label>
    <input type="number" id="quantity" value="1" min="1">
    
    <button onclick="generateLabels()">Xem thử</button>
    <button onclick="window.print()">In Tem</button>
    <button onclick="window.close()">Đóng</button>
</div>

{{-- 
    TEM MẪU (Template)
    Dữ liệu lấy từ $sample (đã load testRequest và patient)
--}}
<div id="label-template" class="label">
    
    {{-- 1. Tên bệnh nhân --}}
    <div class="patient-name">
        {{ $sample->testRequest->patient->full_name ?? 'Không tên' }}
    </div>

    {{-- 3. Loại mẫu (Huyết thanh/Nước tiểu) - Quan trọng để dán đúng ống --}}
    <div class="info" style="font-weight: bold; font-style: italic;">
        {{ $sample->specimen_type }}
    </div>

    {{-- 4. Mã Barcode (Hình ảnh) --}}
    <div class="barcode">
        @if($barcodeBase64)
            <img src="{{ $barcodeBase64 }}" alt="{{ $sample->sample_code }}">
        @else
            <span>Lỗi Barcode</span>
        @endif
    </div>

    {{-- 5. Mã số dưới Barcode (để đọc bằng mắt thường) --}}
    <div class="sample-code-text">
        {{ $sample->sample_code }}
    </div>
</div>

{{-- Container chứa tem --}}
<div id="label-container"></div>

<script>
    function generateLabels() {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const template = document.getElementById('label-template');
        const container = document.getElementById('label-container');
        
        container.innerHTML = ''; // Xóa cũ

        for (let i = 0; i < quantity; i++) {
            const clone = template.cloneNode(true);
            clone.id = ''; // Xóa ID template
            clone.style.display = 'flex'; // Hiển thị tem (vì template đang display:none)
            container.appendChild(clone);
        }
    }

    // Tự động tạo tem khi vào trang
    window.onload = function() {
        generateLabels();
        // Tự động bật cửa sổ in (nếu muốn)
        // setTimeout(function() { window.print(); }, 500);
    }
</script>

</body>
</html>