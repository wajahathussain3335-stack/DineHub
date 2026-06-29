<?php
include 'config.php';

$msg = "";
$msg_type = "";

// Agar user pehle se logged in hai, toh usey seedha uske page par bhejo
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') { header("Location: admin_dashboard.php"); exit(); }
    if ($_SESSION['role'] == 'vendor') { header("Location: vendor_dashboard.php"); exit(); }
    if ($_SESSION['role'] == 'customer') { header("Location: index.php"); exit(); }
}

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Database me user check karna
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Password check karna (Hashed password ke sath match karna)
        if (password_verify($password, $user['password'])) {
            // Session variables set karna
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Role ke mutabiq redirect karna
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] == 'vendor') {
                header("Location: vendor_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $msg = "Ghalat password! Dobara koshish karein.";
            $msg_type = "error";
        }
    } else {
        $msg = "Is email par koi account nahi mila!";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineHub - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-orange-600 mb-2">DineHub</h2>
        <p class="text-center text-gray-500 mb-6">Welcome back! Please login to your account</p>

        <?php if($msg != ""): ?>
            <div class="p-3 rounded mb-4 text-sm font-semibold bg-red-100 text-red-800">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
            </div>

            <button type="submit" name="login" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                Log In
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Don't have an account? <a href="register.php" class="font-medium text-orange-600 hover:text-orange-500">Register here</a>
        </p>
    </div>

</body>
</html>