<?php

namespace App\Services;

use App\Models\DataImport;
use App\Models\ImportError;
use App\Models\Customer;
use App\Models\CustomerActivation;
use App\Models\Tenant;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CustomerImportService
{
    /**
     * معاينة ملف الاستيراد
     */
    public function previewFile($filePath, DataImport $import)
    {
        $fullPath = Storage::disk('local')->path($filePath);
        
        try {
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            // إزالة الصفوف الفارغة
            $data = array_filter($data, function($row) {
                return !empty(array_filter($row));
            });
            
            if (empty($data)) {
                throw new \Exception('الملف فارغ أو لا يحتوي على بيانات صالحة');
            }
            
            // الصف الأول يحتوي على العناوين
            $headers = array_shift($data);
            $headers = array_map('trim', $headers);
            
            // التحقق من وجود الأعمدة المطلوبة
            $requiredColumns = ['name', 'email'];
            $missingColumns = [];
            
            foreach ($requiredColumns as $column) {
                if (!in_array(strtolower($column), array_map('strtolower', $headers))) {
                    $missingColumns[] = $column;
                }
            }
            
            if (!empty($missingColumns)) {
                throw new \Exception('الأعمدة المطلوبة مفقودة: ' . implode(', ', $missingColumns));
            }
            
            // معالجة البيانات للمعاينة (أول 10 صفوف فقط)
            $previewRows = array_slice($data, 0, 10);
            $processedRows = [];
            
            foreach ($previewRows as $index => $row) {
                $processedRow = [];
                foreach ($headers as $headerIndex => $header) {
                    $processedRow[strtolower(trim($header))] = $row[$headerIndex] ?? '';
                }
                $processedRows[] = $processedRow;
            }
            
            return [
                'headers' => $headers,
                'rows' => $processedRows,
                'total_rows' => count($data),
                'preview_count' => count($processedRows)
            ];
            
        } catch (\Exception $e) {
            throw new \Exception('خطأ في قراءة الملف: ' . $e->getMessage());
        }
    }

    /**
     * معالجة عملية الاستيراد
     */
    public function processImport(DataImport $import)
    {
        $import->start();
        
        try {
            $filePath = Storage::disk('local')->path('imports/' . $import->filename);
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            // إزالة الصفوف الفارغة
            $data = array_filter($data, function($row) {
                return !empty(array_filter($row));
            });
            
            $headers = array_shift($data);
            $headers = array_map('trim', $headers);
            
            // تغيير الاتصال لقاعدة بيانات المستأجر
            $this->switchToTenantDatabase($import->tenant);
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($data as $rowIndex => $row) {
                try {
                    $rowData = [];
                    foreach ($headers as $headerIndex => $header) {
                        $rowData[strtolower(trim($header))] = trim($row[$headerIndex] ?? '');
                    }
                    
                    // التحقق من صحة البيانات
                    $this->validateRowData($rowData, $rowIndex + 2, $import->id);
                    
                    // إنشاء العميل
                    $customer = $this->createCustomer($rowData, $import);
                    
                    // إنشاء رمز التفعيل
                    $activation = CustomerActivation::createForCustomer($customer->id);
                    
                    // إرسال رسالة الترحيب
                    $this->sendWelcomeEmail($customer, $activation);
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    ImportError::createError(
                        $import->id,
                        $rowIndex + 2,
                        'processing_error',
                        $e->getMessage(),
                        null,
                        null,
                        $rowData ?? null
                    );
                    $errorCount++;
                }
            }
            
            // تحديث إحصائيات الاستيراد
            $import->update([
                'successful_records' => $successCount,
                'failed_records' => $errorCount
            ]);
            
            $import->complete();
            
        } catch (\Exception $e) {
            $import->fail($e->getMessage());
            throw $e;
        } finally {
            // العودة للاتصال الافتراضي
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
    }

    /**
     * التحقق من صحة بيانات الصف
     */
    private function validateRowData($rowData, $rowNumber, $importId)
    {
        // التحقق من وجود الاسم
        if (empty($rowData['name'])) {
            throw new \Exception('الاسم مطلوب');
        }
        
        // التحقق من وجود البريد الإلكتروني
        if (empty($rowData['email'])) {
            throw new \Exception('البريد الإلكتروني مطلوب');
        }
        
        // التحقق من صحة البريد الإلكتروني
        if (!filter_var($rowData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('البريد الإلكتروني غير صحيح');
        }
        
        // التحقق من عدم تكرار البريد الإلكتروني
        $existingCustomer = Customer::where('email', $rowData['email'])->first();
        if ($existingCustomer) {
            throw new \Exception('البريد الإلكتروني موجود مسبقاً');
        }
    }

    /**
     * إنشاء عميل جديد
     */
    private function createCustomer($rowData, DataImport $import)
    {
        return Customer::create([
            'name' => $rowData['name'],
            'email' => $rowData['email'],
            'password' => Hash::make(Str::random(12)), // كلمة مرور مؤقتة
            'is_activated' => false,
            'profile_completed' => false,
            'imported_from' => $import->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * إرسال رسالة الترحيب
     */
    private function sendWelcomeEmail($customer, $activation)
    {
        try {
            Mail::send('emails.customer-welcome', [
                'customer' => $customer,
                'activation_url' => $activation->getActivationUrl(),
                'expires_at' => $activation->expires_at
            ], function ($message) use ($customer) {
                $message->to($customer->email, $customer->name)
                       ->subject('مرحباً بك - تفعيل حسابك مطلوب');
            });
            
            $activation->markEmailSent();
            
        } catch (\Exception $e) {
            // تسجيل الخطأ لكن لا نوقف العملية
            \Log::error('فشل في إرسال رسالة الترحيب: ' . $e->getMessage());
        }
    }

    /**
     * تغيير الاتصال لقاعدة بيانات المستأجر
     */
    private function switchToTenantDatabase(Tenant $tenant)
    {
        DB::purge('mysql');
        config(['database.connections.mysql.database' => $tenant->database_name]);
        DB::reconnect('mysql');
    }

    /**
     * الحصول على فروع المستأجر
     */
    public function getBranchesForTenant(Tenant $tenant)
    {
        try {
            $this->switchToTenantDatabase($tenant);
            
            // التحقق من وجود جدول branches
            if (!DB::getSchemaBuilder()->hasTable('branches')) {
                return [];
            }
            
            $branches = DB::table('branches')
                         ->select('id', 'name', 'location')
                         ->where('status', 'active')
                         ->get()
                         ->toArray();
            
            return $branches;
            
        } catch (\Exception $e) {
            return [];
        } finally {
            // العودة للاتصال الافتراضي
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
    }
}

