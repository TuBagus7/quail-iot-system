<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemDashboard extends Model
{
    use HasFactory;

    // Tabelnya kita kasih tau Laravel namanya apa
    protected $table = 'item_dashboard';

    // Kolom-kolom yang boleh diisi (buat keamanan biar gak sembarangan diinput)
    protected $fillable = [
        'gauge1',
        'gauge2',
        'gauge3',
        'gauge4',
        'gauge5',
        'item_teks',
        'status_buzzer',
    ];
}
