<?php
// returns the connection to the database.
function get_connection() {
    // init variables.
    $svr = "localhost";
    $un  = "soft";
    $pwd = "";
    $db  = "test_db";

    // create a new connection with variables above.
    $con = new mysqli($svr, $un, $pwd, $db);

    // check connection, if not print error message.
    if ( $con->connect_error ) {
        die("Connection Failed :" . $con->connect_error);
    }

//    echo "Successfully Connected!";
    return $con;
}

// log in function for users who have already registered.
function log_in($_username, $_pwd) {
    // error count variable.
    $err = 0;
    // get a connection.
    $con = get_connection();

    // initialise the variables with their parameters.
    // sanitise the username for security purposes.
    $username = check_input($_username);
    $pwd = $_pwd;

    // check variables aren't null, or an empty string.
    if ( empty($username) ) {
        echo "Please enter a username!";
        $err += 1;
    }

    if ( empty($pwd) ) {
        $err += 1;
        echo "Please enter your password!";
    }

    // when no errors have been detected.
    if ( $err == 0 ) {
        // prepared statement.
        $st = "SELECT * FROM reg_users WHERE username='$username'";
        // query database.
        $res = mysqli_query($con, $st);
        // if there is a match, or a row is returned.
        if ( mysqli_num_rows($res) == 1 ){
            $user = mysqli_fetch_assoc($res);
            $pwd_check = $user['pwd'];
            // verify the password with the user input against
            // the password in the database.
            if ( password_verify($pwd, $pwd_check) ) {
                // set session variable username to user's username.
                $_SESSION['username'] = $username;
                // direct them to the welcome page.
                header('location: welcome.php');
            } else {
                echo "Wrong username and/or password!";
            }
        } else {
            echo "Wrong username and/or password!";
        }
    } else {
        // error message.
        echo "You must enter a username and password!";
    }
    // close statement and connection.
//            $st->close();
    $con->close();
}

function register($_username, $_email, $_pwd) {
    // get a connection.
    $con = get_connection();

    // prepared statement.
    $st = $con->prepare("INSERT INTO reg_users (username, email, pwd)
                        VALUES (?, ?, ?)");

    // check the forms values aren't null or empty strings.
    if ( !empty( $_username ) ) {
        $username = check_input($_username);
    }

    if ( !empty( $_email ) ) {
        $email = check_input($_email);
    }

    if ( !empty( $_pwd ) ) {
        $pwd = password_hash($_pwd, PASSWORD_DEFAULT);
    }

    // prepared statement checking if the users details already exist
    // in the database.
    $user_check = "SELECT * FROM reg_users WHERE username='$username' OR email='$email' LIMIT 1";
    $res = mysqli_query($con, $user_check);
    $user = mysqli_fetch_assoc($res);

    // if the user has already registered.
    if ( $user ) {
        if ( $user["username"] == $username ) {
//            echo "Username already exists!";
        }

        if ( $user["email"] == $email ) {
//            echo "Email address already exists!";
        }

    } else {
        // if they don't exist in the database, add them.
        $st->bind_param('sss', $username, $email, $pwd);
        $st->execute();
//        echo "<br>";
//        echo "Record successfully added!";

        // set session variable username.
        $_SESSION["username"] = $username;
        // redirect to welcome page.
        header('location: welcome.php');
    }
    // close prepared statement and database connection.
    $st->close();
    $con ->close();
}

// sanitise the input.
function check_input($inp) {
    // extraneous whitespace etc.
    $inp = trim($inp);
    // remove slashes from the string.
    $inp = stripslashes($inp);
    // remove characters like the <> used as html tags.
    $inp = htmlspecialchars($inp);
    // return the cleaned up input.
    return $inp;
}