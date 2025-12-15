<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Kelola Kelas";
require __DIR__ . "/../includes/header.php";

global $pdo;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST["name"] ?? "");
    $level = $_POST["level"] ?? "N5";
    $desc  = trim($_POST["description"] ?? "");

    if ($name === "") {
        $errors[] = "Nama kelas wajib diisi (misal: N5-Suginami).";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO classes (name, level, description) VALUES (?,?,?)");
        $stmt->execute([$name, $level, $desc]);
    }
}

$classes = $pdo->query("
    SELECT c.id, c.name, c.level, c.description,
           COUNT(u.id) AS student_count
    FROM classes c
    LEFT JOIN users u ON u.class_id = c.id AND u.role = 'student'
    GROUP BY c.id, c.name, c.level, c.description
    ORDER BY c.level, c.name
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Kelola Kelas</div>
    </div>

    <?php if ($errors): ?>
        <ul style="color:#c62828;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" style="margin-bottom:16px;">
        <p>
            <label>Nama kelas<br>
                <input type="text" name="name" placeholder="Contoh: N5-Suginami" required>
            </label>
        </p>
        <p>
            <label>Level<br>
                <select name="level">
                    <option value="N5">N5</option>
                    <option value="N4">N4</option>
                </select>
            </label>
        </p>
        <p>
            <label>Deskripsi (opsional)<br>
                <textarea name="description" rows="2" style="width:100%;"></textarea>
            </label>
        </p>
        <p>
            <button type="submit">Tambah kelas</button>
        </p>
    </form>

    <?php if (!$classes): ?>
        <p>Belum ada kelas.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama kelas</th>
                <th>Level</th>
                <th>Deskripsi</th>
                <th>Jumlah siswa</th>
            </tr>
            <?php foreach ($classes as $c): ?>
                <tr>
                    <td><?php echo (int)$c["id"]; ?></td>
                    <td><?php echo htmlspecialchars($c["name"]); ?></td>
                    <td><?php echo htmlspecialchars($c["level"]); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($c["description"] ?? "")); ?></td>
                    <td><?php echo (int)$c["student_count"]; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
