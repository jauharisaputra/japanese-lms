<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Kelola Siswa";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name, u.email, u.level,
           c.name AS class_name
    FROM users u
    LEFT JOIN classes c ON u.class_id = c.id
    WHERE u.role = 'student'
    ORDER BY u.level, c.name, u.full_name
");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Siswa</div>
    </div>

    <?php if (!$students): ?>
        <p>Belum ada siswa.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Email</th>
                <th>Level</th>
                <th>Kelas</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?php echo (int)$s["id"]; ?></td>
                    <td><?php echo htmlspecialchars($s["full_name"]); ?></td>
                    <td><?php echo htmlspecialchars($s["username"]); ?></td>
                    <td><?php echo htmlspecialchars($s["email"]); ?></td>
                    <td><?php echo htmlspecialchars($s["level"]); ?></td>
                    <td><?php echo htmlspecialchars($s["class_name"] ?? "-"); ?></td>
                    <td>
                        <a class="button secondary"
                           href="<?php echo BASE_URL; ?>teacher/student-edit.php?id=<?php echo (int)$s["id"]; ?>">Edit / Kelas</a>
                        <a class="button"
                           href="<?php echo BASE_URL; ?>teacher/student-delete.php?id=<?php echo (int)$s["id"]; ?>"
                           onclick="return confirm('Hapus siswa ini beserta data progres & kuisnya?');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
