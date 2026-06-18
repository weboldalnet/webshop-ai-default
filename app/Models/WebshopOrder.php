<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $order_number
 * @property string $status
 * @property string $type
 * @property string|null $customer_name
 * @property string|null $customer_email
 * @property string|null $customer_phone
 * @property string|null $customer_company
 * @property string|null $customer_tax_number
 * @property string|null $billing_data
 * @property string|null $shipping_data
 * @property float $total_price
 * @property string $currency
 * @property string|null $note
 * @property bool $is_completed
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $admin_note
 * @property string|null $payment_method
 * @property string $payment_status
 * @property string $invoice_status
 * @property string|null $shipping_method
 * @property string $shipping_status
 * @property int|null $commerce_payment_transaction_id
 * @property int|null $commerce_invoice_document_id
 * @property int|null $commerce_shipment_id
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $invoiced_at
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property-read \Illuminate\Database\Eloquent\Collection $items
 * @mixin \Eloquent
 */
class WebshopOrder extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_orders';

    // Rendelés státuszok
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    // Rendelés típusok
    const TYPE_ORDER = 'order';
    const TYPE_QUOTE = 'quote';

    // Fizetési státuszok
    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    // Számla státuszok
    const INVOICE_STATUS_NOT_REQUIRED = 'not_required';
    const INVOICE_STATUS_PENDING = 'pending';
    const INVOICE_STATUS_INVOICED = 'invoiced';
    const INVOICE_STATUS_FAILED = 'failed';
    const INVOICE_STATUS_VOIDED = 'voided';

    // Szállítási státuszok
    const SHIPPING_STATUS_NOT_REQUIRED = 'not_required';
    const SHIPPING_STATUS_PENDING = 'pending';
    const SHIPPING_STATUS_PREPARED = 'prepared';
    const SHIPPING_STATUS_SHIPPED = 'shipped';
    const SHIPPING_STATUS_DELIVERED = 'delivered';
    const SHIPPING_STATUS_FAILED = 'failed';

    const TYPES = [
        self::TYPE_ORDER => 'Rendelés',
        self::TYPE_QUOTE => 'Ajánlatkérés',
    ];

    const STATUSES = [
        self::STATUS_PENDING => 'Függőben',
        self::STATUS_PROCESSING => 'Feldolgozás alatt',
        self::STATUS_SHIPPED => 'Kiszállítva',
        self::STATUS_COMPLETED => 'Teljesítve',
        self::STATUS_CANCELLED => 'Törölve',
        self::STATUS_FAILED => 'Sikertelen',
    ];

    const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_UNPAID => 'Fizetetlen',
        self::PAYMENT_STATUS_PENDING => 'Fizetés folyamatban',
        self::PAYMENT_STATUS_PAID => 'Fizetve',
        self::PAYMENT_STATUS_FAILED => 'Fizetés sikertelen',
        self::PAYMENT_STATUS_CANCELLED => 'Fizetés törölve',
        self::PAYMENT_STATUS_REFUNDED => 'Visszatérítve',
    ];

    const INVOICE_STATUSES = [
        self::INVOICE_STATUS_NOT_REQUIRED => 'Nem szükséges',
        self::INVOICE_STATUS_PENDING => 'Számlázás folyamatban',
        self::INVOICE_STATUS_INVOICED => 'Számlázva',
        self::INVOICE_STATUS_FAILED => 'Számlázás sikertelen',
        self::INVOICE_STATUS_VOIDED => 'Számla érvénytelenítve',
    ];

    const SHIPPING_STATUSES = [
        self::SHIPPING_STATUS_NOT_REQUIRED => 'Nem szükséges',
        self::SHIPPING_STATUS_PENDING => 'Szállítás függőben',
        self::SHIPPING_STATUS_PREPARED => 'Előkészítve',
        self::SHIPPING_STATUS_SHIPPED => 'Kiszállítva',
        self::SHIPPING_STATUS_DELIVERED => 'Kézbesítve',
        self::SHIPPING_STATUS_FAILED => 'Szállítás sikertelen',
    ];

    protected $fillable = [
        'order_number', 'status', 'type', 'customer_name', 'customer_email', 'customer_phone',
        'customer_company', 'customer_tax_number', 'billing_data', 'shipping_data',
        'total_price', 'currency', 'is_completed', 'completed_at', 'admin_note', 'note',
        'payment_method', 'payment_status', 'invoice_status',
        'shipping_method', 'shipping_status',
        'commerce_payment_transaction_id', 'commerce_invoice_document_id', 'commerce_shipment_id',
        'paid_at', 'invoiced_at', 'shipped_at',
    ];

    protected $casts = [
        'total_price' => 'float',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
        'invoiced_at' => 'datetime',
        'shipped_at' => 'datetime',
        'billing_data' => 'array',
        'shipping_data' => 'array',
    ];

    // --- Relációk ---

    public function items()
    {
        return $this->hasMany(WebshopOrderItem::class, 'order_id');
    }

    public function paymentTransactions()
    {
        if (!class_exists(\Weboldalnet\CommerceCore\Models\PaymentTransaction::class)) {
            return null;
        }
        return $this->hasMany(\Weboldalnet\CommerceCore\Models\PaymentTransaction::class, 'order_id');
    }

    public function paymentTransaction()
    {
        if (!class_exists(\Weboldalnet\CommerceCore\Models\PaymentTransaction::class)) {
            return null;
        }
        return $this->belongsTo(\Weboldalnet\CommerceCore\Models\PaymentTransaction::class, 'commerce_payment_transaction_id');
    }

    public function invoiceDocument()
    {
        if (!class_exists(\Weboldalnet\CommerceCore\Models\InvoiceDocument::class)) {
            return null;
        }
        return $this->belongsTo(\Weboldalnet\CommerceCore\Models\InvoiceDocument::class, 'commerce_invoice_document_id');
    }

    public function shipment()
    {
        if (!class_exists(\Weboldalnet\CommerceCore\Models\Shipment::class)) {
            return null;
        }
        return $this->belongsTo(\Weboldalnet\CommerceCore\Models\Shipment::class, 'commerce_shipment_id');
    }

    // --- Fizetési státusz helper metódusok ---

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isPaymentPending(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function isPaymentFailed(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_FAILED;
    }

    public function isPaymentCancelled(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_CANCELLED;
    }

    public function isPaymentRetryable(): bool
    {
        return in_array($this->payment_status, [
            self::PAYMENT_STATUS_FAILED,
            self::PAYMENT_STATUS_CANCELLED,
            self::PAYMENT_STATUS_UNPAID,
            self::PAYMENT_STATUS_PENDING,
        ]);
    }

    public function markPaymentPending(): void
    {
        $this->update(['payment_status' => self::PAYMENT_STATUS_PENDING]);
    }

    public function markPaid(?int $transactionId = null): void
    {
        $data = ['payment_status' => self::PAYMENT_STATUS_PAID, 'paid_at' => now()];
        if ($transactionId) {
            $data['commerce_payment_transaction_id'] = $transactionId;
        }
        $this->update($data);
    }

    public function markPaymentFailed(): void
    {
        $this->update(['payment_status' => self::PAYMENT_STATUS_FAILED]);
    }

    public function markPaymentCancelled(): void
    {
        $this->update(['payment_status' => self::PAYMENT_STATUS_CANCELLED]);
    }

    // --- Számla státusz helper metódusok ---

    public function isInvoiced(): bool
    {
        return $this->invoice_status === self::INVOICE_STATUS_INVOICED;
    }

    public function markInvoicePending(): void
    {
        $this->update(['invoice_status' => self::INVOICE_STATUS_PENDING]);
    }

    public function markInvoiced(?int $documentId = null): void
    {
        $data = ['invoice_status' => self::INVOICE_STATUS_INVOICED, 'invoiced_at' => now()];
        if ($documentId) {
            $data['commerce_invoice_document_id'] = $documentId;
        }
        $this->update($data);
    }

    public function markInvoiceFailed(): void
    {
        $this->update(['invoice_status' => self::INVOICE_STATUS_FAILED]);
    }

    // --- Szállítási státusz helper metódusok ---

    public function isShipped(): bool
    {
        return in_array($this->shipping_status, [
            self::SHIPPING_STATUS_SHIPPED,
            self::SHIPPING_STATUS_DELIVERED,
        ]);
    }

    public function markShippingPending(): void
    {
        $this->update(['shipping_status' => self::SHIPPING_STATUS_PENDING]);
    }

    public function markShipped(?int $shipmentId = null): void
    {
        $data = ['shipping_status' => self::SHIPPING_STATUS_SHIPPED, 'shipped_at' => now()];
        if ($shipmentId) {
            $data['commerce_shipment_id'] = $shipmentId;
        }
        $this->update($data);
    }

    public function markShippingFailed(): void
    {
        $this->update(['shipping_status' => self::SHIPPING_STATUS_FAILED]);
    }

    // --- Scope-ok ---

    public function scopeSearch($query, $search)
    {
        return $search ? $query->where(function ($q) use ($search) {
            $q->where('order_number', 'ILIKE', '%'.$search.'%')
              ->orWhere('customer_name', 'ILIKE', '%'.$search.'%')
              ->orWhere('customer_email', 'ILIKE', '%'.$search.'%');
        }) : $query;
    }

    public function scopeByStatus($query, $status) { return $status ? $query->where('status', $status) : $query; }

    public function scopeByPaymentStatus($query, $paymentStatus) { return $paymentStatus ? $query->where('payment_status', $paymentStatus) : $query; }

    public function scopeCompleted($query, $completed)
    {
        return ($completed !== null && $completed !== '') ? $query->where('is_completed', $completed === '1' || $completed === true) : $query;
    }

    // --- Attribute accessors ---

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status] ?? $this->status; }
    public function getTypeLabelAttribute(): string { return self::TYPES[$this->type] ?? $this->type; }
    public function getPaymentStatusLabelAttribute(): string { return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status; }
    public function getInvoiceStatusLabelAttribute(): string { return self::INVOICE_STATUSES[$this->invoice_status] ?? $this->invoice_status; }
    public function getShippingStatusLabelAttribute(): string { return self::SHIPPING_STATUSES[$this->shipping_status] ?? $this->shipping_status; }
}
