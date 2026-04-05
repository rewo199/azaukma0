<?php
session_start();
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลจากโฟลเดอร์ backend
require 'backend/db_connect.php'; 

// 1. เช็กสิทธิ์: ถ้าไม่ใช่ผู้ประกอบการ ให้เตะกลับไปหน้าล็อกอิน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: index.php");
    exit();
}

$employer_id = $_SESSION['user_id'];
$alertMessage = "";

// 2. จัดการเมื่อร้านค้ากดปุ่ม "โพสต์งาน"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['postJobBtn'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $wage = $_POST['wage'];
    // พิกัดจำลองของ มทส. ไปก่อน
    $lat = 14.8818; 
    $lng = 102.0156;

    // เตรียมคำสั่ง SQL เพิ่มงานใหม่ลงตาราง Jobs
    $sql = "INSERT INTO Jobs (employer_id, title, description, wage, location_lat, location_lng, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'open')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdd", $employer_id, $title, $description, $wage, $lat, $lng);

    if ($stmt->execute()) {
        $alertMessage = "✅ โพสต์ประกาศรับสมัครงานสำเร็จ!";
    } else {
        $alertMessage = "❌ เกิดข้อผิดพลาด: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการร้านค้า - UinJob Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: block; background-color: #F0EFFF;">

    <div class="header-search">
        <h2>ยินดีต้อนรับ, ร้าน <?php echo $_SESSION['full_name']; ?></h2>
        <a href="logout.php" class="btn" style="background-color: #ff4d4d; width: auto; padding: 10px 20px; text-decoration: none;">ออกจากระบบ</a>
    </div>

    <div id="job-feed-container">
        
        <?php if(!empty($alertMessage)): ?>
            <p style="text-align: center; color: #28a745; font-weight: bold; background: #e8f5e9; padding: 10px; border-radius: 8px;"><?php echo $alertMessage; ?></p>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 20px;">
            <h3 style="margin-bottom: 15px; color: #333;">📝 สร้างประกาศรับสมัครงาน</h3>
            
            <form method="POST" action="">
                <p style="margin: 0 0 5px 0; font-size: 14px;">ตำแหน่งงาน</p>
                <input type="text" name="title" class="input" placeholder="เช่น พนักงานเสิร์ฟ, แคชเชียร์" required>
                
                <p style="margin: 10px 0 5px 0; font-size: 14px;">ค่าจ้าง / รายได้</p>
                <input type="text" name="wage" class="input" placeholder="เช่น 50 บาท/ชั่วโมง" required>
                
                <p style="margin: 10px 0 5px 0; font-size: 14px;">รายละเอียดงานและเวลาทำงาน</p>
                <textarea name="description" class="input" rows="4" placeholder="พิมพ์รายละเอียดงานที่นี่..." required style="resize: vertical;"></textarea>
                
                <button type="submit" name="postJobBtn" class="btn" style="margin-top: 15px;">โพสต์ประกาศงาน</button>
            </form>
        </div>
<div class="card" style="margin-bottom: 20px;">
            <h3 style="margin-bottom: 15px; color: #333;">💼 ประกาศงานของฉัน</h3>
            <div id="my-jobs-list">
                <p style="text-align: center; color: #888;">กำลังโหลดข้อมูล...</p>
            </div>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 15px; color: #333;">👥 นักศึกษาที่สมัครงานเข้ามา</h3>
            <div id="applicant-list">
                <p style="text-align: center; color: #888;">กำลังโหลดข้อมูล...</p>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetchApplications();
        });

        // ฟังก์ชันดึงข้อมูลคนสมัคร (ชี้ไปที่โฟลเดอร์ backend)
        function fetchApplications() {
            fetch('backend/get_applications.php')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('applicant-list');
                    list.innerHTML = ''; 

                    if (data.length === 0) {
                        list.innerHTML = '<p style="text-align:center; color: #888;">ยังไม่มีผู้สมัครในขณะนี้</p>';
                        return;
                    }

                    data.forEach(app => {
                        let statusText = app.status === 'pending' ? '🟡 รอดำเนินการ' : 
                                         app.status === 'accepted' ? '🟢 รับเข้าทำงานแล้ว' : '🔴 ปฏิเสธแล้ว';

                        let buttons = '';
                        if (app.status === 'pending') {
                            buttons = `
                                <div style="margin-top: 10px; display: flex; gap: 10px;">
                                    <button onclick="updateStatus(${app.app_id}, 'accepted')" style="background:#28a745; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold;">รับทำงาน</button>
                                    <button onclick="updateStatus(${app.app_id}, 'rejected')" style="background:#dc3545; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold;">ปฏิเสธ</button>
                                </div>
                            `;
                        }

                        list.innerHTML += `
                            <div style="border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 10px; background: #fafafa;">
                                <p style="margin: 0 0 5px 0; font-weight: 600; font-size: 16px; color: #222;">${app.student_name} <span style="font-size: 13px; font-weight:normal;">(${statusText})</span></p>
                                <p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">📌 สมัครตำแหน่ง: ${app.job_title}</p>
                                <p style="margin: 0; font-size: 14px; color: #666;">📞 เบอร์ติดต่อ: ${app.phone_number}</p>
                                ${buttons}
                            </div>
                        `;
                    });
                })
                .catch(error => {
                    console.error("Error fetching applications:", error);
                    document.getElementById('applicant-list').innerHTML = '<p style="text-align:center; color:red;">ไม่สามารถโหลดข้อมูลผู้สมัครได้</p>';
                });
        }

        // ฟังก์ชันอัปเดตสถานะ (ชี้ไปที่โฟลเดอร์ backend)
        function updateStatus(appId, newStatus) {
            if(confirm(newStatus === 'accepted' ? "รับนักศึกษาคนนี้เข้าทำงาน?" : "ปฏิเสธนักศึกษาคนนี้?")) {
                fetch('backend/update_application.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ app_id: appId, status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchApplications(); // รีเฟรชรายชื่อใหม่
                    } else {
                        alert("เกิดข้อผิดพลาด: " + data.message);
                    }
                });
            }
        }
        // ให้มันดึงข้อมูลงานทันทีที่เปิดหน้าเว็บ
        document.addEventListener("DOMContentLoaded", function() {
            fetchApplications(); // ของเดิม
            fetchMyJobs();       // เพิ่มอันนี้เข้าไป
        });

        // -----------------------------------------
        // ดึงรายการงานของตัวเองมาแสดง
        // -----------------------------------------
        function fetchMyJobs() {
            fetch('backend/get_my_jobs.php')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('my-jobs-list');
                    list.innerHTML = ''; 

                    if (data.length === 0) {
                        list.innerHTML = '<p style="text-align:center; color:#888;">คุณยังไม่ได้โพสต์งานใดๆ</p>';
                        return;
                    }

                    data.forEach(job => {
                        let isClosed = job.status === 'closed';
                        let statusUI = isClosed ? '<span style="color:#dc3545; font-size:13px;">(ปิดรับสมัครแล้ว)</span>' : '<span style="color:#28a745; font-size:13px;">(เปิดรับสมัคร)</span>';
                        
                        // ถ้าปิดไปแล้ว ให้ซ่อนปุ่ม
                        let closeBtn = isClosed ? '' : `<button onclick="closeJob(${job.job_id})" style="background:#6c757d; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">🗑️ ปิดรับสมัคร</button>`;

                        list.innerHTML += `
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 10px 0;">
                                <div>
                                    <p style="margin: 0; font-weight: bold;">${job.title} ${statusUI}</p>
                                    <p style="margin: 0; font-size: 12px; color: #666;">${job.wage}</p>
                                </div>
                                <div>
                                    ${closeBtn}
                                </div>
                            </div>
                        `;
                    });
                });
        }

        // -----------------------------------------
        // ฟังก์ชันกดยกเลิก/ปิดประกาศงาน
        // -----------------------------------------
        function closeJob(jobId) {
            if(confirm("คุณต้องการปิดรับสมัครงานนี้ใช่หรือไม่? (นักศึกษาจะไม่เห็นงานนี้อีก)")) {
                fetch('backend/close_job.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ job_id: jobId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchMyJobs(); // โหลดรายการงานใหม่ให้ปุ่มหายไป
                        alert("ปิดรับสมัครงานเรียบร้อยแล้ว!");
                    }
                });
            }
        }
    </script>
</body>
</html>