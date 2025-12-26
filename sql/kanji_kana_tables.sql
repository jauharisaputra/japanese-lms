CREATE TABLE IF NOT EXISTS kana_chars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  script ENUM("hiragana","katakana") NOT NULL,
  char_symbol VARCHAR(5) NOT NULL,          -- あ / ア
  romaji VARCHAR(20) NOT NULL,              -- a
  stroke_svg_path VARCHAR(255) DEFAULT NULL,-- path SVG lokal
  description VARCHAR(255) DEFAULT NULL,    -- catatan singkat
  examples TEXT DEFAULT NULL                 -- JSON contoh kata
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kanji_chars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  level ENUM("N5","N4") DEFAULT "N5",
  kanji CHAR(1) NOT NULL,                   -- 日
  onyomi VARCHAR(100) DEFAULT NULL,
  kunyomi VARCHAR(100) DEFAULT NULL,
  romaji VARCHAR(100) DEFAULT NULL,         -- hi / nichi / jitsu
  meaning_id VARCHAR(255) DEFAULT NULL,     -- arti Indonesia
  stroke_svg_path VARCHAR(255) DEFAULT NULL,-- path SVG lokal
  examples TEXT DEFAULT NULL                -- JSON contoh kata/kalimat
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
