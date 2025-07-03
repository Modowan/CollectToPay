@extends('layouts.admin')

@section('title', 'تفاصيل عملية الاستيراد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>
                        تفاصيل عملية الاستيراد #{{ $import->id }}
                    </h3>
                    <div class="card-tools">
                        @switch($import->status)
                            @case('pending')
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    في الانتظار
                                </span>
                                @break
                            @case('processing')
                                <span class="badge bg-primary">
                                    <i class="fas fa-spinner fa-spin me-1"></i>
                                    جاري المعالجة
                                </span>
                                @break
                            @case('completed')
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>
                                    مكتمل
                                </span>
                                @break
                            @case('failed')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>
                                    فاشل
                                </span>
                                @break
                        @endswitch
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- معلومات أساسية -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info me-2"></i>
                                        معلومات الاستيراد
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td><strong>اسم الملف:</strong></td>
                                            <td>{{ $import->original_filename }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>الفندق:</strong></td>
                                            <td>
                                                <span class="badge bg-info">{{ $import->tenant->name }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>الفرع:</strong></td>
                                            <td>
                                                @if($import->branch)
                                                    <span class="badge bg-secondary">{{ $import->branch->name }}</span>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>تم الاستيراد بواسطة:</strong></td>
                                            <td>{{ $import->importedBy->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>تاريخ الإنشاء:</strong></td>
                                            <td>{{ $import->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @if($import->started_at)
                                            <tr>
                                                <td><strong>تاريخ البداية:</strong></td>
                                                <td>{{ $import->started_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @endif
                                        @if($import->completed_at)
                                            <tr>
                                                <td><strong>تاريخ الانتهاء:</strong></td>
                                                <td>{{ $import->completed_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        إحصائيات الاستيراد
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="border-end">
                                                <h4 class="text-primary">{{ number_format($import->total_records) }}</h4>
                                                <small class="text-muted">إجمالي السجلات</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border-end">
                                                <h4 class="text-success">{{ number_format($import->successful_records) }}</h4>
                                                <small class="text-muted">ناجحة</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="text-danger">{{ number_format($import->failed_records) }}</h4>
                                            <small class="text-muted">فاشلة</small>
                                        </div>
                                    </div>
                                    
                                    @if($import->total_records > 0)
                                        <div class="mt-3">
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $import->success_rate }}%"
                                                     title="نسبة النجاح: {{ $import->success_rate }}%">
                                                    {{ $import->success_rate }}%
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- الأخطاء -->
                    @if($import->errors->count() > 0)
                        <div class="card border-danger mb-4">
                            <div class="card-header bg-danger text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    الأخطاء ({{ $import->errors->count() }})
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>رقم الصف</th>
                                                <th>نوع الخطأ</th>
                                                <th>الحقل</th>
                                                <th>القيمة</th>
                                                <th>رسالة الخطأ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($import->errors->take(50) as $error)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $error->row_number }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning">{{ $error->error_type_description }}</span>
                                                    </td>
                                                    <td>{{ $error->field_name ?? '-' }}</td>
                                                    <td>
                                                        @if($error->field_value)
                                                            <code>{{ Str::limit($error->field_value, 30) }}</code>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $error->error_message }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    
                                    @if($import->errors->count() > 50)
                                        <div class="text-center mt-3">
                                            <small class="text-muted">
                                                عرض أول 50 خطأ من إجمالي {{ $import->errors->count() }} خطأ
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- الملاحظات -->
                    @if($import->notes)
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-sticky-note me-2"></i>
                                ملاحظات
                            </h6>
                            <p class="mb-0">{{ $import->notes }}</p>
                        </div>
                    @endif

                    <!-- أزرار الإجراءات -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.customer-import.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            العودة للقائمة
                        </a>
                        
                        <div>
                            @if($import->status === 'pending')
                                <a href="{{ route('admin.customer-import.preview', $import->id) }}" 
                                   class="btn btn-primary me-2">
                                    <i class="fas fa-eye me-2"></i>
                                    معاينة البيانات
                                </a>
                            @endif
                            
                            <form action="{{ route('admin.customer-import.destroy', $import->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه العملية؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($import->status === 'processing')
    @push('scripts')
    <script>
    // تحديث الصفحة كل 5 ثوان إذا كانت العملية جارية
    setInterval(function() {
        fetch(`/admin/customer-import/{{ $import->id }}/status`)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'processing') {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
    }, 5000);
    </script>
    @endpush
@endif
@endsection

