<?php
session_start();
require '../db.php'; // Ensure correct path to your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Input Sanitization Function ---
    function dataFilter($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // --- Capture and sanitize form inputs ---
    $name = dataFilter($_POST['name']);
    $mobile = dataFilter($_POST['mobile']);
    $user = dataFilter($_POST['uname']);
    $email = dataFilter($_POST['email']);
    $pass = password_hash($_POST['pass'], PASSWORD_BCRYPT);
    $hash = md5(rand(0, 1000));
    $category = dataFilter($_POST['category']); // '1' or '2'
    $addr = dataFilter($_POST['addr']);

    // --- Store basic info in session ---
    $_SESSION['Email'] = $email;
    $_SESSION['Name'] = $name;
    $_SESSION['Password'] = $pass;
    $_SESSION['Username'] = $user;
    $_SESSION['Mobile'] = $mobile;
    $_SESSION['Category'] = $category;
    $_SESSION['Hash'] = $hash;
    $_SESSION['Addr'] = $addr;
    $_SESSION['Rating'] = 0;

    // --- Mobile validation ---
    if (!ctype_digit($mobile) || strlen($mobile) != 10) {
        $_SESSION['message'] = "Invalid Mobile Number!";
        header("Location: error.php");
        exit();
    }

    // --- Convert category to integer just to be safe ---
    $category = (int)$category;

    // --- Farmer registration ---
    if ($category === 1) {
        $result = mysqli_query($conn, "SELECT * FROM farmer WHERE femail='$email'");

        if ($result && $result->num_rows > 0) {
            $_SESSION['message'] = "User with this email already exists!";
            header("Location: error.php");
            exit();
        } else {
            $sql = "INSERT INTO farmer (fname, fusername, fpassword, fhash, fmobile, femail, faddress)
                    VALUES ('$name','$user','$pass','$hash','$mobile','$email','$addr')";

            if (mysqli_query($conn, $sql)) {
                $_SESSION['Active'] = 0;
                $_SESSION['logged_in'] = true;
                $_SESSION['picStatus'] = 0;
                $_SESSION['picExt'] = 'png';

                $userResult = mysqli_query($conn, "SELECT * FROM farmer WHERE fusername='$user'");
                $User = $userResult->fetch_assoc();
                $_SESSION['id'] = $User['fid'];

                // --- Profile picture setup ---
                if ($_SESSION['picStatus'] == 0) {
                    $_SESSION['picId'] = 0;
                    $_SESSION['picName'] = "profile0.png";
                } else {
                    $_SESSION['picId'] = $_SESSION['id'];
                    $_SESSION['picName'] = "profile" . $_SESSION['picId'] . "." . $_SESSION['picExt'];
                }

                // --- Verification message ---
                $_SESSION['message'] = "Confirmation link has been sent to $email. Please verify your account.";

                $to = $email;
                $subject = "Account Verification (ArtCircle.com)";
                $message_body = "
                Hello $user,

                Thank you for signing up!

                Please click this link to activate your account:
                http://localhost/AgroCulture/Login/verify.php?email=$email&hash=$hash";

                // mail($to, $subject, $message_body); // Uncomment if mail() configured

                header("Location: profile.php");
                exit();
            } else {
                $_SESSION['message'] = "Registration failed!";
                header("Location: error.php");
                exit();
            }
        }
    }

    // --- Buyer registration ---
    elseif ($category === 2) {
        $result = mysqli_query($conn, "SELECT * FROM buyer WHERE bemail='$email'");

        if ($result && $result->num_rows > 0) {
            $_SESSION['message'] = "User with this email already exists!";
            header("Location: error.php");
            exit();
        } else {
            $sql = "INSERT INTO buyer (bname, busername, bpassword, bhash, bmobile, bemail, baddress)
                    VALUES ('$name','$user','$pass','$hash','$mobile','$email','$addr')";

            if (mysqli_query($conn, $sql)) {
                $_SESSION['Active'] = 0;
                $_SESSION['logged_in'] = true;
                $_SESSION['picStatus'] = 0;
                $_SESSION['picExt'] = 'png';

                $userResult = mysqli_query($conn, "SELECT * FROM buyer WHERE busername='$user'");
                $User = $userResult->fetch_assoc();
                $_SESSION['id'] = $User['bid'];

                // --- Profile picture setup ---
                if ($_SESSION['picStatus'] == 0) {
                    $_SESSION['picId'] = 0;
                    $_SESSION['picName'] = "profile0.png";
                } else {
                    $_SESSION['picId'] = $_SESSION['id'];
                    $_SESSION['picName'] = "profile" . $_SESSION['picId'] . "." . $_SESSION['picExt'];
                }

                // --- Verification message ---
                $_SESSION['message'] = "Confirmation link has been sent to $email. Please verify your account.";

                $to = $email;
                $subject = "Account Verification (ArtCircle.com)";
                $message_body = "
                Hello $user,

                Thank you for signing up!

                Please click this link to activate your account:
                http://localhost/AgroCulture/Login/verify.php?email=$email&hash=$hash";

                // mail($to, $subject, $message_body); // Uncomment if mail() configured

                header("Location: profile.php");
                exit();
            } else {
                $_SESSION['message'] = "Registration not successful!";
                header("Location: error.php");
                exit();
            }
        }
    }

    // --- Invalid category ---
    else {
        $_SESSION['message'] = "Invalid category selected!";
        header("Location: error.php");
        exit();
    }
}
?>
