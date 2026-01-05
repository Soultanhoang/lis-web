<div class="card">
    <div class="card-header bg-info">
        <h3 class="card-title">Danh sách Mẫu bệnh phẩm (Cần lấy)</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Barcode ống nghiệm</th>
                    <th>Loại mẫu</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testRequest->samples as $sample)
                    <tr>
                        <td class="font-weight-bold">{{ $sample->sample_code }}</td>
                        <td>{{ $sample->specimen_type }}</td>
                        <td>
                            @if($sample->status == 'pending')
                                <span class="badge badge-warning">Chưa lấy</span>
                            @elseif($sample->status == 'received')
                                <span class="badge badge-success">Đã nhận</span>
                            @endif
                        </td>
                        <td>
                            {{-- Nút in Barcode cho từng ống --}}
                            <a href="#" class="btn btn-sm btn-default">
                                <i class="fas fa-print"></i> In Tem
                            </a>
                            
                            {{-- Nút xác nhận đã nhận mẫu (Check-in) --}}
                            @if($sample->status == 'pending')
                                <a href="{{ route('samples.receive', $sample->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check"></i> Nhận mẫu
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>