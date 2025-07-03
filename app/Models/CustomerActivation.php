<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'activation_token',
        'status',
        'email_sent_at',
        'activated_at',
        'expires_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * العلاقة مع العميل
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * إنشاء رمز تفعيل جديد
     */
    public static function createForCustomer($customerId, $expiresInHours = 72)
    {
        return self::create([
            'customer_id' => $customerId,
            'activation_token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addHours($expiresInHours)
        ]);
    }

    /**
     * التحقق من صلاحية الرمز
     */
    public function isValid()
    {
        return $this->status === 'pending' && $this->expires_at > now();
    }

    /**
     * التحقق من انتهاء صلاحية الرمز
     */
    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    /**
     * تفعيل الحساب
     */
    public function activate($ipAddress = null, $userAgent = null)
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->update([
            'status' => 'activated',
            'activated_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);

        // تحديث حالة العميل
        $this->customer->update([
            'is_activated' => true,
            'activated_at' => now()
        ]);

        return true;
    }

    /**
     * تحديد انتهاء صلاحية الرمز
     */
    public function expire()
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * تحديث وقت إرسال الإيميل
     */
    public function markEmailSent()
    {
        $this->update(['email_sent_at' => now()]);
    }

    /**
     * الحصول على رابط التفعيل
     */
    public function getActivationUrl()
    {
        return route('customer.activate', ['token' => $this->activation_token]);
    }

    /**
     * البحث عن رمز تفعيل صالح
     */
    public static function findValidToken($token)
    {
        return self::where('activation_token', $token)
                   ->where('status', 'pending')
                   ->where('expires_at', '>', now())
                   ->first();
    }
}

