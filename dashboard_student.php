<?php
session_start();
//  ถ้าคนที่เข้าหน้านี้ ไม่ใช่นักศึกษา ให้เตะกลับไปหน้า index เพื่อแยกทางใหม่
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>




    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้านักศึกษา - UinJob Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>ยินดีต้อนรับนักศึกษา: <?php echo $_SESSION['full_name']; ?></h1>
        <p>คุณพร้อมจะหางานพาร์ทไทม์หรือยัง?</p>
        
        <a href="logout.php" class="btn">ออกจากระบบ</a>
        <a href="sc.php" class="btn">ค้นหางาน</a>
    </div>
</body>
</html>