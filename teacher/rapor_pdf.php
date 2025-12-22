<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
requireRole(["teacher","admin"]);
global $pdo;

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$stmt = $pdo->prepare("SELECT * FROM rapor_n5 WHERE id = :id");
$stmt->execute([":id" => $id]);
$rapor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rapor) {
    die("Rapor tidak ditemukan.");
}

// TODO: join ke tabel students untuk ambil nama siswa
$nama_siswa = $rapor["student_id"];
$nis        = $rapor["student_id"];
$kelas      = $rapor["kelas"];
$tanggal    = $rapor["tanggal"];

require_once __DIR__ . "/../lib/tcpdf/tcpdf.php";

$pdf = new TCPDF();
$pdf->SetCreator("Nihongo Daichi Online");
$pdf->SetAuthor("JAYADI GLOBAL EDUCATION CENTER");
$pdf->SetTitle("Rapor N5 - " . $nama_siswa);
$pdf->AddPage();
$pdf->SetFont("dejavusans", "", 11);

// Header institusi
$pdf->Cell(0, 8, "JAYADI GLOBAL EDUCATION CENTER", 0, 1, "C");
$pdf->Cell(0, 6, "NILAI HASIL BELAJAR SISWA KELAS N5", 0, 1, "C");
$pdf->Ln(4);

// Data siswa
$pdf->Cell(0, 6, "Nama Siswa: " . $nama_siswa, 0, 1);
$pdf->Cell(0, 6, "NIS: " . $nis, 0, 1);
$pdf->Cell(0, 6, "Kelas: " . $kelas, 0, 1);
$pdf->Cell(0, 6, "Tanggal: " . date("d-m-Y", strtotime($tanggal)), 0, 1);
$pdf->Ln(4);

// A. Sikap
$pdf->SetFont("dejavusans", "B", 11);
$pdf->Cell(0, 6, "A. Penilaian Sikap", 0, 1);
$pdf->SetFont("dejavusans", "", 11);
$pdf->Cell(80, 6, "Kehadiran", 1);
$pdf->Cell(30, 6, $rapor["kehadiran"], 1, 1, "C");
$pdf->Cell(80, 6, "Keaktifan", 1);
$pdf->Cell(30, 6, $rapor["keaktifan"], 1, 1, "C");
$pdf->Ln(3);

// B. Tugas
$pdf->SetFont("dejavusans", "B", 11);
$pdf->Cell(0, 6, "B. Penilaian Tugas", 0, 1);
$pdf->SetFont("dejavusans", "", 11);
$pdf->Cell(80, 6, "Keseluruhan Tugas", 1);
$pdf->Cell(30, 6, $rapor["tugas_keseluruhan"], 1, 1, "C");
$pdf->Ln(3);

// C. Kompetensi TO
$pdf->SetFont("dejavusans", "B", 11);
$pdf->Cell(0, 6, "C. Penilaian Kompetensi TO", 0, 1);
$pdf->SetFont("dejavusans", "", 11);
$pdf->Cell(80, 6, "Bunpou, Goi, Tanbun Sakusei", 1);
$pdf->Cell(30, 6, $rapor["bunpou_goi"], 1, 1, "C");
$pdf->Cell(80, 6, "Kanji", 1);
$pdf->Cell(30, 6, $rapor["kanji"], 1, 1, "C");
$pdf->Cell(80, 6, "Dokkai", 1);
$pdf->Cell(30, 6, $rapor["dokkai"], 1, 1, "C");
$pdf->Cell(80, 6, "Choukai", 1);
$pdf->Cell(30, 6, $rapor["choukai"], 1, 1, "C");
$pdf->Cell(80, 6, "Kaiwa", 1);
$pdf->Cell(30, 6, $rapor["kaiwa"], 1, 1, "C");
$pdf->Ln(3);

// D. Nilai akhir
$pdf->SetFont("dejavusans", "B", 11);
$pdf->Cell(0, 6, "D. Kesimpulan Nilai Akhir", 0, 1);
$pdf->SetFont("dejavusans", "", 11);
$pdf->Cell(80, 6, "Nilai Sikap (30%)", 1);
$pdf->Cell(30, 6, $rapor["sikap_nilai"], 1, 1, "C");
$pdf->Cell(80, 6, "Nilai Tugas (30%)", 1);
$pdf->Cell(30, 6, $rapor["tugas_nilai"], 1, 1, "C");
$pdf->Cell(80, 6, "Nilai Kompetensi TO (40%)", 1);
$pdf->Cell(30, 6, $rapor["kompetensi_to_nilai"], 1, 1, "C");
$pdf->Cell(80, 6, "Total Nilai", 1);
$pdf->Cell(30, 6, $rapor["total_nilai"], 1, 1, "C");
$pdf->Cell(80, 6, "Status", 1);
$pdf->Cell(30, 6, $rapor["status_lulus"], 1, 1, "C");
$pdf->Ln(3);

// E. Catatan Sensei
$pdf->SetFont("dejavusans", "B", 11);
$pdf->Cell(0, 6, "E. Catatan Sensei", 0, 1);
$pdf->SetFont("dejavusans", "", 11);
$pdf->MultiCell(0, 6, $rapor["catatan_sensei"], 1);
$pdf->Ln(8);

// Tanda tangan (layout sederhana)
$pdf->Cell(60, 6, "Sensei Utama", 0, 0, "C");
$pdf->Cell(60, 6, "Kepala Sekolah", 0, 0, "C");
$pdf->Cell(60, 6, "Sensei Pendamping", 0, 1, "C");
$pdf->Ln(15);

$pdf->Output("rapor_n5_{$nis}.pdf", "I");
exit;
