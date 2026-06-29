<?php
include 'config.php';

$msg = "";
$msg_type = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Password ko secure (hash) karna
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check karna ke email pehle se exist toh nahi karti
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($check_email) > 0) {
        $msg = "Yeh email pehle se registered hai!";
        $msg_type = "error";
    } else {
        // User insert karna
        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
        if (mysqli_query($conn, $query)) {
            $msg = "Account kamyabi se ban gaya! Ab aap login kar sakte hain.";
            $msg_type = "success";
        } else {
            $msg = "Kuch ghalat ho gaya, dobara koshish karein.";
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineHub - Register</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-orange-600 mb-2">DineHub</h2>
        <p class="text-center text-gray-500 mb-6">Create your multi-restaurant account</p>

        <!-- Notification Alerts -->
        <?php if($msg != ""): ?>
            <div class="p-3 rounded mb-4 text-sm font-semibold <?php echo ($msg_type == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Account Type</label>
                <select name="role" class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                    <option value="customer">Customer (Order Food)</option>
                    <option value="vendor">Restaurant Owner (Sell Food)</option>
                </select>
            </div>

            <button type="submit" name="register" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                Register Account
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account? <a href="login.php" class="font-medium text-orange-600 hover:text-orange-500">Log in</a>
        </p>
    </div>

</body>
</html>