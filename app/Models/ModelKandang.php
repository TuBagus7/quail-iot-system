<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelKandang extends Model
{
    use HasFactory;

    // Tabelnya kita kasih tau Laravel namanya apa
    protected $table = 'data_kandang';

    // Kolom-kolom yang boleh diisi (buat keamanan biar gak sembarangan diinput)
    protected $fillable = [
        'gauge1',
        'gauge2',
        'gauge3',
        'gauge4',
        'item_teks',
        'status_buzzer',
    ];
}
