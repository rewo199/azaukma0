<?php
session_start();
// ทำลายข้อมูลใน Session ทั้งหมด (ลืมไปเลยว่าใครเคยล็อกอิน)
session_destroy();

// เตะกลับไปหน้าเข้าสู่ระบบ
header("Location: login.php");
exit();
?>