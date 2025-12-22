-- Tabel rapor utama (AUTO-CALCULATION)
CREATE TABLE rapor_n5 (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) NOT NULL,
    student_name VARCHAR(100),
    kelas VARCHAR(20) DEFAULT 'Hoki',
    tanggal DATE NOT NULL DEFAULT CURDATE(),
    kehadiran INT DEFAULT 0 CHECK (kehadiran BETWEEN 0 AND 100),
    keaktifan INT DEFAULT 0 CHECK (keaktifan BETWEEN 0 AND 100),
    tugas_keseluruhan INT DEFAULT 0 CHECK (tugas_keseluruhan BETWEEN 0 AND 100),
    
    -- Nilai TO (auto-fill dari to_results)
    bunpou_goi DECIMAL(5,2) DEFAULT 0,
    kanji DECIMAL(5,2) DEFAULT 0,
    dokkai DECIMAL(5,2) DEFAULT 0,
    choukai DECIMAL(5,2) DEFAULT 0,
    kaiwa DECIMAL(5,2) DEFAULT 0,
    
    -- AUTO-CALCULATION (MySQL 8.0+)
    sikap_nilai DECIMAL(5,2) GENERATED ALWAYS AS ((kehadiran + keaktifan) / 2) STORED,
    tugas_nilai DECIMAL(5,2) GENERATED ALWAYS AS (tugas_keseluruhan) STORED,
    kompetensi_to_nilai DECIMAL(5,2) GENERATED ALWAYS AS ((bunpou_goi + kanji + dokkai + choukai + kaiwa) / 5) STORED,
    total_nilai INT GENERATED ALWAYS AS (ROUND(((kehadiran + keaktifan)/2 * 0.3) + (tugas_keseluruhan * 0.3) + (((bunpou_goi + kanji + dokkai + choukai + kaiwa)/5) * 0.4))) STORED,
    status_lulus ENUM('LULUS', 'TIDAK LULUS') GENERATED ALWAYS AS (CASE WHEN total_nilai >= 75 THEN 'LULUS' ELSE 'TIDAK LULUS' END) STORED,
    
    catatan_sensei TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_student (student_id),
    INDEX idx_kelas (kelas)
);

-- VIEW auto-fill TO scores
CREATE VIEW v_rapor_to_scores AS
SELECT 
    tr.student_id,
    tr.exam_id,
    ROUND(AVG(CASE WHEN tq.category = 'bunpou' THEN tr.score * 100 END), 2) as bunpou_goi,
    ROUND(AVG(CASE WHEN tq.category = 'kanji' THEN tr.score * 100 END), 2) as kanji,
    ROUND(AVG(CASE WHEN tq.category = 'dokkai' THEN tr.score * 100 END), 2) as dokkai,
    ROUND(AVG(CASE WHEN tq.category = 'choukai' THEN tr.score * 100 END), 2) as choukai,
    ROUND(AVG(CASE WHEN tq.category = 'kaiwa' THEN tr.score * 100 END), 2) as kaiwa
FROM to_results tr
JOIN to_questions tq ON tr.question_id = tq.id
GROUP BY tr.student_id, tr.exam_id;
