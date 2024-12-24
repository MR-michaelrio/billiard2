<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $table = 'member';
    protected $primaryKey = 'id_member';
    protected $keyType = 'string'; 
    protected $fillable = ['id_member', 'nama', 'no_telp', 'alamat', 'tanggal_lahir', 'mulai_member', 'akhir_member'];
    
}
