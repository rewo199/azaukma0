<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหางานพาร์ทไทม์</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>
<body>

    <div class="tabs" style="max-width: 600px; margin: 0 auto 20px auto;">
        <button class="tab active" onclick="switchTab('feed')" id="tab-feed">🔍 ค้นหางาน</button>
        <button class="tab" onclick="switchTab('status')" id="tab-status">📋 สถานะของฉัน</button>
    </div>

    <div id="job-feed-container">
        </div>

    <div id="my-status-container" style="display: none; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h3 style="margin-bottom: 15px;">ประวัติการสมัครงานของฉัน</h3>
        <div id="status-list">
            <p style="text-align: center;">กำลังโหลดข้อมูล...</p>
        </div>
    </div>
    <div id="job-feed-container">
        <p style="text-align: center;">กำลังโหลดข้อมูลงาน...</p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetchJobs();
        });

        function fetchJobs() {
            // วิ่งไปขอข้อมูลจาก Backend
        fetch('backend/get_jobs.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('job-feed-container');
                    container.innerHTML = ''; // ล้างข้อความ "กำลังโหลด" ออก

                    if (data.length === 0) {
                        container.innerHTML = '<p>ยังไม่มีประกาศงานในขณะนี้</p>';
                        return;
                    }

                    // วนลูปสร้างการ์ดตามจำนวนข้อมูลที่ได้มา
                    data.forEach(job => {
                        let avatarText = job.employer_name.substring(0, 2);

                        const cardHTML = `
                        <div class="job-card">
                            <div class="job-avatar">${avatarText}</div>
                            <div class="job-details">
                                <div class="job-header">
                                    <h3 class="job-title">${job.title}</h3>
                                    <span class="bookmark-icon">🔖</span>
                                </div>
                                <p class="company-name">${job.employer_name}</p>
                                
                                <p class="location-distance">📍 มทส. 1.5 กม.</p>
                                
                                <div class="job-tags">
                                    <span class="tag tag-wage">${job.wage}</span>
                                    <span class="tag tag-type">Part-time</span>
                                </div>
                                <p class="post-time">🕒 อัปเดตล่าสุด</p>
                            </div>
                        </div>
                        `;
                        // ยัดการ์ดลงไปในกล่อง
                        container.innerHTML += cardHTML;
                    });
                })
                .catch(error => {
                    console.error('Error fetching jobs:', error);
                    document.getElementById('job-feed-container').innerHTML = '<p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>';
                });
        }
        function switchTab(tabName) {
            // สลับสีปุ่ม
            document.getElementById('tab-feed').classList.remove('active');
            document.getElementById('tab-status').classList.remove('active');
            document.getElementById('tab-' + tabName).classList.add('active');

            // สลับการแสดงผลหน้าจอ
            if (tabName === 'feed') {
                document.getElementById('job-feed-container').style.display = 'block';
                document.getElementById('my-status-container').style.display = 'none';
                fetchJobs(); // โหลดงานใหม่
            } else {
                document.getElementById('job-feed-container').style.display = 'none';
                document.getElementById('my-status-container').style.display = 'block';
                fetchMyStatus(); // โหลดสถานะ
            }
        }

        // -----------------------------------------
        // ฟังก์ชันดึงประวัติการสมัคร (ของแท็บที่ 2)
        // -----------------------------------------
        function fetchMyStatus() {
            fetch('backend/get_my_status.php')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('status-list');
                    list.innerHTML = ''; 

                    if (data.length === 0) {
                        list.innerHTML = '<p style="text-align:center; color:#888;">คุณยังไม่เคยสมัครงานเลย ลองไปหาดูสิ!</p>';
                        return;
                    }

                    data.forEach(app => {
                        // กำหนดสีและข้อความตามสถานะ
                        let statusUI = '';
                        if (app.status === 'pending') {
                            statusUI = '<span style="background:#ffc107; color:#000; padding:4px 10px; border-radius:15px; font-size:12px;">รอดำเนินการ</span>';
                        } else if (app.status === 'accepted') {
                            statusUI = '<span style="background:#28a745; color:#fff; padding:4px 10px; border-radius:15px; font-size:12px;">🎉 รับเข้าทำงานแล้ว!</span>';
                        } else {
                            statusUI = '<span style="background:#dc3545; color:#fff; padding:4px 10px; border-radius:15px; font-size:12px;">ไม่ผ่านการคัดเลือก</span>';
                        }

                        list.innerHTML += `
                            <div class="card" style="margin-bottom: 10px; padding: 15px; border-left: 5px solid ${app.status === 'accepted' ? '#28a745' : (app.status === 'pending' ? '#ffc107' : '#dc3545')}">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h4 style="margin: 0 0 5px 0;">${app.title}</h4>
                                        <p style="margin: 0; font-size: 13px; color: #666;">ร้าน: ${app.employer_name} | ${app.wage}</p>
                                    </div>
                                    <div>
                                        ${statusUI}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                })
                .catch(error => console.error("Error:", error));
        }
    </script>

</body>
</html>