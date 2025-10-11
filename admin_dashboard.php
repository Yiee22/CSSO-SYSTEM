<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$currentDay = date("j");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - CSSO</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Segoe UI", Arial, sans-serif;
    }

    body {
        background: #ffffff;
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: 240px;
        background: #0a1931;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 0;
        position: fixed;
        height: 100%;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 22px;
        letter-spacing: 1px;
    }

    .nav-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 0 20px;
    }

    .nav-links a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        text-decoration: none;
        padding: 10px;
        border-radius: 6px;
        transition: 0.3s;
    }

    .nav-links a:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .logout {
        text-align: center;
        padding: 10px;
        border-top: 1px solid rgba(255,255,255,0.2);
        position: relative;
    }

    .logout a {
        color: #ff7675;
        text-decoration: none;
        font-weight: bold;
    }

    .logout a:hover {
        color: #ff4d4d;
    }

    /* Logout Popup Box */
    .logout-popup {
        position: absolute;
        bottom: 45px;
        left: 25px;
        background: #fff;
        color: #0a1931;
        border: 2px solid #0a1931;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        padding: 15px;
        width: 190px;
        display: none;
        z-index: 100;
        animation: fadeIn 0.2s ease-in-out;
    }

  /* ===== Sidebar Logo Style ===== */
.logo-container {
    text-align: center;
    margin-bottom: 20px;
}

.logo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    animation: fadeInLogo 0.8s ease;
}

/* Optional smooth animation sa logo */
@keyframes fadeInLogo {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

    .logout-popup p {
        font-size: 14px;
        text-align: center;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .popup-buttons {
        display: flex;
        justify-content: space-between;
    }

    .popup-buttons button {
        border: none;
        padding: 6px 10px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    .yes-btn {
        background: #0a1931;
        color: #fff;
    }

    .no-btn {
        background: #ccc;
        color: #000;
    }

    /* Main content */
    .main-content {
        margin-left: 240px;
        padding: 25px 30px;
        width: calc(100% - 240px);
        background: #ffffff;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 4px solid skyblue;
        padding-bottom: 10px;
    }

    .header h1 {
        color: #0a1931;
        font-size: 26px;
        font-weight: bold;
    }

    .welcome {
        font-size: 16px;
        color: #333;
    }

    #datetime {
        color: #0a1931;
        font-weight: bold;
        font-size: 14px;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .card {
        color: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }

    .card:hover { transform: translateY(-5px); }

    .students { background: linear-gradient(135deg, #00c6ff, #0072ff); }
    .registration { background: linear-gradient(135deg, #43e97b, #38f9d7); }
    .attendance { background: linear-gradient(135deg, #ff9a9e, #fad0c4); }
    .events { background: linear-gradient(135deg, #f7971e, #ffd200); }
    .fines { background: linear-gradient(135deg, #a18cd1, #fbc2eb); }
    .payments { background: linear-gradient(135deg, #f5576c, #f093fb); }

    .card i { font-size: 26px; margin-bottom: 10px; }
    .card h3 { font-size: 18px; margin-bottom: 8px; }
    .card p { font-size: 14px; }

    .bottom-section {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 20px;
        margin-bottom: 40px;
    }

    .chart, .income, .recent {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 20px;
    }

    .chart h3, .income h3, .recent h3 {
        color: #0a1931;
        margin-bottom: 15px;
    }

    .bars {
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        height: 160px;
        margin-top: 20px;
    }

    .bar {
        width: 30px;
        background: linear-gradient(135deg, #0072ff, #00c6ff);
        border-radius: 5px 5px 0 0;
        text-align: center;
        color: #fff;
        font-size: 12px;
        padding-top: 5px;
    }

    .income p { font-size: 15px; margin-bottom: 8px; color: #333; }
    .income .green { color: #009900; font-weight: bold; }
    .income .blue { color: #0066ff; font-weight: bold; }

    .recent ul { list-style: none; }
    .recent li {
        padding: 8px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .course-tag {
        background: #0072ff;
        color: #fff;
        padding: 3px 8px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: bold;
    }

    .calendar-section {
        background: rgba(255,255,255,0.9);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .calendar-section h2 { color: #0a1931; margin-bottom: 15px; }

    .calendar { border: 1px solid #ccc; border-radius: 10px; padding: 10px; }
    table { width: 100%; border-collapse: collapse; text-align: center; }
    th { background: #0a1931; color: #fff; padding: 8px; }
    td { padding: 10px; border: 1px solid #eee; }
    td:hover { background: #f0f4ff; cursor: pointer; }
    .today { background: #ffd700 !important; color: #000; font-weight: bold; border-radius: 8px; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo-container">
        <img src="images/cssologo.png" alt="CSSO Logo" class="logo">
    </div>

    <div class="nav-links">
        <a href="#"><i class="fa fa-home"></i> <span>Dashboard</span></a>
        <a href="#"><i class="fa fa-users"></i> <span>Students</span></a>
        <a href="#"><i class="fa fa-id-card"></i> <span>Registration</span></a>
        <a href="#"><i class="fa fa-calendar-check"></i> <span>Attendance</span></a>
        <a href="#"><i class="fa fa-calendar-days"></i> <span>Events</span></a>
        <a href="#"><i class="fa fa-money-bill"></i> <span>Fines</span></a>
        <a href="#"><i class="fa fa-wallet"></i> <span>Payments</span></a>
         <a href="#"><i class="fa fa-cog"></i> <span>Manage Users</span></a>

    </div>

    <div class="logout">
        <a href="#" id="logoutBtn"><i class="fa fa-power-off"></i> Logout</a>
        <div class="logout-popup" id="logoutPopup">
            <p>Are you sure you want to logout?</p>
            <div class="popup-buttons">
                <button class="yes-btn" onclick="logoutYes()">Yes</button>
                <button class="no-btn" onclick="logoutNo()">No</button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h1>CSSO</h1>
        <div>
            <p class="welcome">Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></p>
            <p id="datetime"></p>
        </div>
    </div>

    <!-- Same content -->
    <div class="cards">
        <div class="card students">
            <i class="fa fa-users"></i>
            <h3>Students List</h3>
     
        </div>

        <div class="card registration">
            <i class="fa fa-id-card"></i>
            <h3>Registration</h3>
            <p>Handle student membership registration.</p>
        </div>

        <div class="card attendance">
            <i class="fa fa-calendar-check"></i>
            <h3>Attendance</h3>
            <p>Monitor student participation in events.</p>
        </div>

        <div class="card events">
            <i class="fa fa-calendar-days"></i>
            <h3>Events List</h3>
            <p>View and manage upcoming activities.</p>
        </div>

        <div class="card fines">
            <i class="fa fa-money-bill"></i>
            <h3>Fines List</h3>
            <p>Track student fines and penalties.</p>
        </div>

        <div class="card payments">
            <i class="fa fa-wallet"></i>
            <h3>Payments</h3>
            <p>Check and record payment transactions.</p>
        </div>
    </div>

    <div class="bottom-section">
        <div class="chart">
            <h3>Students Population</h3>
            <div class="bars">
                <div class="bar" style="height:60%">1st</div>
                <div class="bar" style="height:75%">2nd</div>
                <div class="bar" style="height:85%">3rd</div>
                <div class="bar" style="height:90%">4th</div>
            </div>
        </div>

        <div class="income">
            <h3>Income</h3>
            <p>Registration - <span class="green">₱5,000</span></p>
            <p>Fines Payments - <span class="green">₱2,300</span></p>
            <p><strong class="blue">Total - ₱7,300</strong></p>
        </div>

        <div class="recent">
            <h3>Recent Members</h3>
            <ul>
                <li>Juan Dela Cruz <span class="course-tag">BSIT</span></li>
                <li>Maria Santos <span class="course-tag">BSCS</span></li>
                <li>Jose Lopez <span class="course-tag">BSIT</span></li>
                <li>Ana Reyes <span class="course-tag">BSCS</span></li>
            </ul>
        </div>
    </div>

    <div class="calendar-section">
    <h2>Events Calendar</h2>
    <div class="calendar-header">
        <h3 id="calendar-month-year"></h3>
    </div>
    <div class="calendar">
        <table id="calendar-table">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody id="calendar-body"></tbody>
        </table>
    </div>
</div>

<script>
document.getElementById("logoutBtn").addEventListener("click", function(e) {
    e.preventDefault();
    const popup = document.getElementById("logoutPopup");
    popup.style.display = popup.style.display === "block" ? "none" : "block";
});

function logoutYes() {
    window.location.href = "login.php";
}

function logoutNo() {
    document.getElementById("logoutPopup").style.display = "none";
}

function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = now.toLocaleDateString('en-US', options);
    const formattedTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second:'2-digit' });
    document.getElementById("datetime").textContent = formattedDate + " | " + formattedTime;
}
setInterval(updateDateTime, 1000);
updateDateTime();

// === REAL-TIME CALENDAR (CLEAN VERSION) ===
function generateCalendar() {
    const now = new Date();
    const month = now.getMonth();
    const year = now.getFullYear();
    const today = new Date();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const monthNames = [
        "January","February","March","April","May","June",
        "July","August","September","October","November","December"
    ];

    document.getElementById("calendar-month-year").textContent = `${monthNames[month]} ${year}`;

    const calendarBody = document.getElementById("calendar-body");
    calendarBody.innerHTML = "";

    let date = 1;
    for (let i = 0; i < 6; i++) {
        let row = document.createElement("tr");

        for (let j = 0; j < 7; j++) {
            let cell = document.createElement("td");

            if (i === 0 && j < firstDay) {
                cell.textContent = "";
            } else if (date > daysInMonth) {
                break;
            } else {
                cell.textContent = date;

                // Highlight current system date (blue gradient)
                if (
                    date === today.getDate() &&
                    month === today.getMonth() &&
                    year === today.getFullYear()
                ) {
                    cell.style.background = "linear-gradient(135deg, #3b82f6, #60a5fa)";
                    cell.style.color = "#fff";
                    cell.style.fontWeight = "bold";
                    cell.style.borderRadius = "50%";
                    cell.style.boxShadow = "0 0 8px rgba(59,130,246,0.6)";
                }

                date++;
            }

            row.appendChild(cell);
        }
        calendarBody.appendChild(row);
    }
}

// === generate calendar on load ===
generateCalendar();

// === refresh every minute to update highlight if date changes ===
setInterval(generateCalendar, 60000);
</script>


</body>
</html>