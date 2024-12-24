<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoice';
    protected $fillable = ['id', 'id_player', 'id_rental', 'id_belanja', 'user_id', 'harga_table', 'harga_cafe'];
    public function order()
    {
        return $this->belongsTo(Order::class,'id_belanja','id_table');
    }
    public function rentalinvoice()
    {
        return $this->belongsTo(RentalInvoice::class,'id_rental','id_rental');
    }
    public function nonmember()
    {
        return $this->belongsTo(NonMember::class,'id_player','id');
    }
}
