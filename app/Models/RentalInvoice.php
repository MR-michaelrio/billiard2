<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalInvoice extends Model
{
    use HasFactory;
    protected $table = 'rental_invoice';
    protected $fillable = ['id_rental', 'lama_waktu', 'waktu_mulai', 'waktu_akhir', 'no_meja', 'metode'];
    protected $casts = [
        'waktu_akhir' => 'datetime',
        'waktu_mulai' => 'datetime',
    ];
    public function invoice()
    {
        return $this->belongsTo(Order::class,'id_rental','id_rental');
    }

    public function invoices()
    {
        return $this->belongsTo(Invoice::class,'id_rental','id_rental');
    }
}
