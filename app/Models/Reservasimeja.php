<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasimeja extends Model
{
    use HasFactory;
    protected $table = 'reservasi_meja';
    protected $fillable = ['id', 'nama', 'no_telp', 'lama_waktu', 'tanggal_main'];
}
