<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหางานพาร์ทไทม์</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="header-search">
        <h2>ค้นหางานพาร์ทไทม์</h2>
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
            fetch('get_jobs.php')
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
    </script>

</body>
</html>