<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanAjar extends Model
{
    use HasFactory;

    protected $table = 'bahan_ajar';

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mapel_id',
        'judul',
        'deskripsi',
        'tipe_materi', // google_docs, google_slides, pdf, youtube, link
        'url_materi',
        'embed_url',
        'file_materi',
        'status', // aktif, draf
    ];

    /**
     * Relasi ke Guru (User)
     */
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Mata Pelajaran
     */
    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    /**
     * Relasi ke Evaluasi Bahan Ajar
     */
    public function evaluasi()
    {
        return $this->hasOne(EvaluasiBahanAjar::class, 'bahan_ajar_id');
    }

    /**
     * Helper untuk menghasilkan embed URL Google Docs, Slides, Drive, atau YouTube
     */
    public static function generateEmbedUrl(?string $url, string $tipeMateri): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // 1. Google Docs
        // https://docs.google.com/document/d/{ID}/edit... -> https://docs.google.com/document/d/{ID}/preview
        if (preg_match('/docs\.google\.com\/document\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://docs.google.com/document/d/{$matches[1]}/preview";
        }

        // 2. Google Slides
        // https://docs.google.com/presentation/d/{ID}/edit... -> https://docs.google.com/presentation/d/{ID}/embed?start=false&loop=false&delayms=3000
        if (preg_match('/docs\.google\.com\/presentation\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://docs.google.com/presentation/d/{$matches[1]}/embed?start=false&loop=false&delayms=3000";
        }

        // 3. Google Spreadsheets
        if (preg_match('/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://docs.google.com/spreadsheets/d/{$matches[1]}/preview";
        }

        // 4. Google Drive File / PDF Preview
        // https://drive.google.com/file/d/{ID}/view... -> https://drive.google.com/file/d/{ID}/preview
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        // 5. YouTube Video
        // https://www.youtube.com/watch?v={ID} or https://youtu.be/{ID} -> https://www.youtube.com/embed/{ID}
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }

        return $url;
    }
}

// akhir batas suci yang kamu ubah
