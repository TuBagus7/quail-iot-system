<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiKualitasAirBad extends Mailable
{
    use Queueable, SerializesModels;

    // Kita bikin variabel penampung buat datanya ya Bang
    public $data;

    /**
     * Pas class ini dipanggil, kita minta datanya sekalian
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Judul Emailnya mau apa? Diatur di sini ya Bang
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ PERINGATAN: Kualitas Air Kandang Puyuh Buruk!',
        );
    }

    /**
     * Tampilan emailnya ambil dari file mana? Diatur di sini
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notifikasi_kualitas_air', // Nanti kita buat file blade-nya di sini
        );
    }

    /**
     * Kalau mau nambahin lampiran (file), bisa di sini, tapi sekarang gak usah dulu
     */
    public function attachments(): array
    {
        return [];
    }
}
