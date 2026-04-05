<?php
// เปิดใช้งาน Session เพื่อเช็กว่าใครกำลังล็อกอินอยู่
session_start();

// 1. ตรวจสอบว่าล็อกอินเข้ามาหรือยัง?
// ถ้ายังไม่มี session user_id แปลว่าแอบเข้าผ่าน URL ตรงๆ ให้เตะกลับไปหน้า Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // สั่งหยุดการทำงานทันที
}

// 2. ถ้าล็อกอินแล้ว ให้เช็กว่าเป็นใคร (Role)
$role = $_SESSION['role'];

if ($role === 'student') {
    // ถ้านักศึกษา ให้เด้งไปหน้าหางาน
    header("Location: dashboard_student.php");
    exit();

} elseif ($role === 'employer') {
    // ถ้าผู้ประกอบการ ให้เด้งไปหน้าประกาศงาน
    header("Location: dashboard_employer.php");
    exit();

} else {
    // เผื่ออนาคตทำระบบ Admin
    echo "<h1>เกิดข้อผิดพลาด: ไม่พบสิทธิ์การเข้าใช้งาน</h1>";
    echo "<a href='logout.php'>คลิกเพื่อออกจากระบบ</a>";
}
?>