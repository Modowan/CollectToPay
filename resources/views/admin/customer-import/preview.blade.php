@extends('layouts.admin')

@section('title', 'Aperçu des données d'importation)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye me-2"></i>
                        Aperçu des données d'importation
                    </h3>
                    <div class="card-tools">
                        <span class="badge bg-warning">
                            <i class="fas fa-clock me-1"></i>
                            في الانتظار
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- معلومات الاستيراد -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-file-excel"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">اسم الملف</span>
                                    <span class="info-box-number">{{ $import->original_filename }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-hotel"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">الفندق</span>
                                    <span class="info-box-number">{{ $import->tenant->name }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">الفرع</span>
                                    <span class="info-box-number">
                                        {{ $import->branch ? $import->branch->name : 'غير محدد' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">إجمالي السجلات</span>
                                    <span class="info-box-number">{{ number_format($import->total_records) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($import->preview_data)
                        <!-- عرض الأعمدة -->
                        <div class="mb-4">
                            <h5>
                                <i class="fas fa-columns me-2"></i>
                                الأعمدة المكتشفة
                            </h5>
                            <div class="row">
                                @foreach($import->preview_data['headers'] as $header)
                                    <div class="col-md-2 mb-2">
                                        @php
                                            $isRequired = in_array(strtolower($header), ['name', 'email']);
                                            $badgeClass = $isRequired ? 'bg-success' : 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} w-100">
                                            @if($isRequired)
                                                <i class="fas fa-check me-1"></i>
                                            @endif
                                            {{ $header }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- معاينة البيانات -->
                        <div class="mb-4">
                            <h5>
                                <i class="fas fa-table me-2"></i>
                                معاينة البيانات (أول {{ $import->preview_data['preview_count'] }} سجلات)
                            </h5>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            @foreach($import->preview_data['headers'] as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($import->preview_data['rows'] as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                @foreach($import->preview_data['headers'] as $header)
                                                    <td>
                                                        @php
                                                            $value = $row[strtolower($header)] ?? '';
                                                            $isEmail = strtolower($header) === 'email';
                                                        @endphp
                                                        
                                                        @if($isEmail && $value)
                                                            <div class="d-flex align-items-center">
                                                                @if(filter_var($value, FILTER_VALIDATE_EMAIL))
                                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                                @else
                                                                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                                                @endif
                                                                {{ $value }}
                                                            </div>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- تحذيرات وملاحظات -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                ملاحظات مهمة
                            </h6>
                            <ul class="mb-0">
                                <li>سيتم إنشاء حساب لكل عميل مع كلمة مرور مؤقتة</li>
                                <li>سيتم إرسال رسالة ترحيب لكل عميل تحتوي على رابط تفعيل الحساب</li>
                                <li>العملاء الذين لديهم بريد إلكتروني مكرر سيتم تجاهلهم</li>
                                <li>يمكن للعملاء تعيين كلمة مرور جديدة واستكمال معلوماتهم الشخصية بعد التفعيل</li>
                            </ul>
                        </div>

                        <!-- أزرار الإجراءات -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.customer-import.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                العودة للقائمة
                            </a>
                            
                            <div>
                                <a href="{{ route('admin.customer-import.create') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-2"></i>
                                    إلغاء
                                </a>
                                
                                <form action="{{ route('admin.customer-import.confirm', $import->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirmImport()">
                                    @csrf
                                    <button type="submit" class="btn btn-success" id="confirmBtn">
                                        <i class="fas fa-check me-2"></i>
                                        تأكيد الاستيراد
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                خطأ في معاينة البيانات
                            </h6>
                            <p class="mb-0">لم يتم العثور على بيانات صالحة في الملف. يرجى التأكد من تنسيق الملف والمحاولة مرة أخرى.</p>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.customer-import.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                العودة للقائمة
                            </a>
                            
                            <a href="{{ route('admin.customer-import.create') }}" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>
                                رفع ملف جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmImport() {
    const confirmed = confirm(
        'هل أنت متأكد من تأكيد الاستيراد؟\n\n' +
        'سيتم إنشاء {{ $import->total_records }} حساب عميل جديد وإرسال رسائل ترحيب.\n' +
        'هذه العملية لا يمكن التراجع عنها.'
    );
    
    if (confirmed) {
        const confirmBtn = document.getElementById('confirmBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري المعالجة...';
    }
    
    return confirmed;
}
</script>
@endpush
@endsection

