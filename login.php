<?php
session_start();

$conn = new mysqli("localhost", "root", "", "csso");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$success = false;
$loginSuccess = false;  // ✅ added missing
$redirectPage = "";     // ✅ added missing

// LOGIN PROCESS
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $usertype = $_POST['usertype'];

    $sql = "SELECT * FROM users WHERE username=? AND usertype=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $usertype);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['usertype'] = $row['usertype'];

            $loginSuccess = true;

            // ✅ Redirect based on role
            if ($row['usertype'] == "Governor" || $row['usertype'] == "Vice Governor") {
                $redirectPage = "admin/admin_dashboard.php";
            } else {
                $redirectPage = "user/user_dashboard.php";
            }
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "Username or usertype not found!";
    }
}

// SIGNUP PROCESS with limits
if (isset($_POST['signup'])) {
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $rawPassword = trim($_POST['password']);
    $usertype = $_POST['usertype'];

    // ✅ Role limits
    $limits = [
        'Governor'        => 1,
        'Vice Governor'   => 1,
        'Secretary'       => 1,
        'Auditor'         => 1,
        'Treasurer'       => 1,
        'Social Manager'  => 5,
        'Senator'         => 5
    ];

    if (!array_key_exists($usertype, $limits)) {
        $message = "Invalid user type selected!";
    } else {
        // Username duplicate check
        $checkUsername = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE username = ?");
        $checkUsername->bind_param("s", $username);
        $checkUsername->execute();
        $resUser = $checkUsername->get_result()->fetch_assoc();

        if ($resUser['total'] > 0) {
            $message = "Username already taken! Please choose another.";
        } else {
            // Role limit check
            $checkRole = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE usertype = ?");
            $checkRole->bind_param("s", $usertype);
            $checkRole->execute();
            $resRole = $checkRole->get_result()->fetch_assoc();

            $limit = $limits[$usertype];
            if ($resRole['total'] >= $limit) {
                 $message = "$usertype is already registered. Only one $usertype is allowed.";
            } else {
                if (strlen($rawPassword) < 8) {
                    $message = "Password must be at least 8 characters long!";
                } else {
                    $password = password_hash($rawPassword, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (first_name, last_name, username, password, usertype) VALUES (?,?,?,?,?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssss", $fname, $lname, $username, $password, $usertype);

                    if ($stmt->execute()) {
                        $success = true;
                    } else {
                        $message = "Error: " . $stmt->error;
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - CSSO</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f4f6fa;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .login-box {
        background: rgba(255, 255, 255, 0.5);
        width: 380px;
        padding: 30px 25px;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border: 2px solid rgba(0, 123, 255, 0.4);
        backdrop-filter: blur(15px);
        transition: all 0.4s ease;
    }
    h2 { text-align: center; margin-bottom: 20px; color: #000; }
    .input-group { position: relative; margin-bottom: 14px; }
    .input-group i {
        position: absolute; left: 10px; top: 50%;
        transform: translateY(-50%); color: #000; font-size: 14px;
    }
    input, select {
        width: 100%; padding: 10px 10px 10px 32px;
        border-radius: 6px; border: 1px solid rgba(0,0,0,0.2);
        outline: none; font-size: 13.5px; color: #000;
        background: rgba(255,255,255,0.8);
        transition: border-color 0.3s, background 0.3s;
        box-sizing: border-box;
    }
    input::placeholder, select { color: #555; }
    input:focus, select:focus { border-color: #007bff; background: rgba(255,255,255,0.9); }
    .btn-primary {
        width: 100%; padding: 11px;
        background: linear-gradient(135deg, #00c853, #64dd17);
        color: #fff; border: none; border-radius: 6px;
        font-size: 15px; cursor: pointer; margin-top: 6px;
        transition: 0.3s;
    }
    .btn-primary:hover { background: linear-gradient(135deg, #64dd17, #00c853); }
    .toggle-section { text-align: center; margin-top: 15px; color: #000; }
    .toggle-link { cursor: pointer; color: #000; font-weight: bold; }
    .separator { display: flex; align-items: center; justify-content: center; margin: 12px 0; color: #555; font-size: 13px; }
    .separator::before, .separator::after {
        content: ""; flex: 1; height: 1px; background: rgba(0,0,0,0.2); margin: 0 10px;
    }
    .row { display: flex; gap: 8px; }
    .row .input-group { flex: 1; }
    #signupForm { opacity: 0; height: 0; overflow: hidden; pointer-events: none; transition: all 0.4s ease; }
    #signupForm.active { opacity: 1; height: auto; pointer-events: auto; }
    #loginForm.hide { opacity: 0; height: 0; overflow: hidden; pointer-events: none; }

    /* ✅ Center Popup Styles */
    .popup {
        position: fixed;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        padding: 18px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.3);
        text-align: center;
        z-index: 9999;
        animation: fadeInOut 2s ease;
        font-weight: bold;
    }
    .popup.success { border: 2px solid #28a745; color: #28a745; }
    .popup.error { border: 2px solid #dc3545; color: #dc3545; }

    @keyframes fadeInOut {
        0% { opacity: 0; transform: translate(-50%, -60%); }
        10% { opacity: 1; transform: translate(-50%, -50%); }
        90% { opacity: 1; }
        100% { opacity: 0; transform: translate(-50%, -60%); }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-box">
    <h2 id="formTitle">Login</h2>

    <form method="POST" id="loginForm" autocomplete="off">
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input type="text" name="username" placeholder="Enter your username" required>
        </div>
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="input-group">
            <i class="fa fa-users"></i>
            <select name="usertype" required>
                <option value="" disabled selected>Select user type</option>
                <option value="Governor">Governor</option>
                <option value="Vice Governor">Vice Governor</option>
                <option value="Secretary">Secretary</option>
                <option value="Auditor">Auditor</option>
                <option value="Treasurer">Treasurer</option>
                <option value="Social Manager">Social Manager</option>
                <option value="Senator">Senator</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" name="login">Login</button>

        <div class="separator">OR</div>

        <div class="toggle-section">
            Don’t have an account?
            <span class="toggle-link" onclick="toggleForms('signup')">Sign up here</span>
        </div>
    </form>

    <form method="POST" id="signupForm" autocomplete="off">
        <div class="row">
            <div class="input-group"><i class="fa fa-user"></i><input type="text" name="first_name" placeholder="First name" required></div>
            <div class="input-group"><i class="fa fa-user"></i><input type="text" name="last_name" placeholder="Last name" required></div>
        </div>
        <div class="input-group"><i class="fa fa-at"></i><input type="text" name="username" placeholder="Choose a username" required></div>
        <div class="input-group"><i class="fa fa-lock"></i><input type="password" name="password" id="signupPassword" placeholder="Create a password" required></div>
        <div id="passwordError" class="error-message" style="display:none;"></div>
        <div class="input-group">
            <i class="fa fa-id-badge"></i>
            <select name="usertype" required>
                <option value="" disabled selected>Select user type</option>
                <option value="Governor">Governor</option>
                <option value="Vice Governor">Vice Governor</option>
                <option value="Secretary">Secretary</option>
                <option value="Auditor">Auditor</option>
                <option value="Treasurer">Treasurer</option>
                <option value="Social Manager">Social Manager</option>
                <option value="Senator">Senator</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" name="signup">Create Account</button>

        <div class="toggle-section">
            Already have an account?
            <span class="toggle-link" onclick="toggleForms('login')">Login here</span>
        </div>
    </form>
</div>


<?php if ($message != ""): ?>
<div class="popup error" id="errorPopup"><?php echo $message; ?></div>
<script>
setTimeout(() => {
    document.getElementById("errorPopup").style.display = "none";
}, 2000);
</script>
<?php endif; ?>

<?php if ($success): ?>
<div class="popup success" id="successPopup">Signup successful! You can now login.</div>
<script>
setTimeout(() => {
    document.getElementById("successPopup").style.display = "none";
    toggleForms('login');
}, 2000);
</script>
<?php endif; ?>

<?php if ($loginSuccess): ?>
<div class="popup success" id="loginPopup">Login successful!</div>
<script>
setTimeout(() => {
    document.getElementById("loginPopup").style.display = "none";
    window.location.href = "<?php echo $redirectPage; ?>";
}, 1500);
</script>
<?php endif; ?>

<script>
function toggleForms(form) {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const formTitle = document.getElementById('formTitle');

    document.querySelectorAll('input').forEach(i => i.value = '');
    document.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

    if (form === 'signup') {
        loginForm.classList.add('hide');
        signupForm.classList.add('active');
        formTitle.innerText = "Create Account";
    } else {
        loginForm.classList.remove('hide');
        signupForm.classList.remove('active');
        formTitle.innerText = "Login";
    }
}

document.getElementById("signupForm").addEventListener("submit", function(e) {
    let pass = document.getElementById("signupPassword");
    let err = document.getElementById("passwordError");
    if (pass.value.length < 8) {
        e.preventDefault();
        err.style.display = "block";
        err.textContent = "Password must be at least 8 characters.";
    } else {
        err.style.display = "none";
    }
});
</script>
</body>
</html>


















