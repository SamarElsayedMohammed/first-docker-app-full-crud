<?php ob_start();
session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=h, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>Document</title>
</head>

<body>
    <div class="container">
        <?php
        //These are the defined authentication environment in the db service
        getenv('MYSQL_DBHOST') ? $db_host = getenv('MYSQL_DBHOST') : $db_host = "localhost";
        getenv('MYSQL_DBPORT') ? $db_port = getenv('MYSQL_DBPORT') : $db_port = "3306";
        getenv('MYSQL_DBUSER') ? $db_user = getenv('MYSQL_DBUSER') : $db_user = "root";
        getenv('MYSQL_DBPASS') ? $db_pass = getenv('MYSQL_DBPASS') : $db_pass = "secret";
        getenv('MYSQL_DBNAME') ? $db_name = getenv('MYSQL_DBNAME') : $db_name = "livewire_db";
        // $db_host = "db";
        if (strlen($db_name) === 0)
            $conn = new mysqli("$db_host:$db_port", $db_user, $db_pass);
        else
            $conn = new mysqli("$db_host:$db_port", $db_user, $db_pass, $db_name);


        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conn->connect_error)
            die("Connection failed: " . $conn->connect_error);
        $id = trim($_GET['id']);
        $name = $password = "";

        // Prepare a select statement
        $sql = "SELECT * FROM users WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $id);
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Retrieve individual field value
                    $name = $row["username"];
                    $password = $row["password"];
                } else {
                    // URL doesn't contain valid id parameter. Redirect to error page
                    header("location: index.php");
                    exit();
                }
            }
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate name
            $input_name = trim($_POST["name"]);
            if (empty($input_name)) {
                $name_err = "Please enter a name.";
            } elseif (!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
                $name_err = "Please enter a valid name.";
            } else {
                $name = $input_name;
            }
            // Validate password
            $input_password = trim($_POST["password"]);
            if (empty($input_password)) {
                $password_err = "Please enter a password.";
            } elseif (!filter_var($input_password, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
                $password_err = "Please enter a valid password.";
            } else {
                $password = $input_password;
            }
            // Check input errors before inserting in database
            if (empty($name_err) && empty($password_err)) {
                echo "update";

                $sql = "UPDATE users SET username=?, password=? WHERE id=?";

                if ($stmt = mysqli_prepare($conn, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "ssi", $param_name, $param_address, $param_id);

                    // Set parameters
                    $param_name = $name;
                    $param_address = $password;
                    $param_id = $id;

                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Records updated successfully. Redirect to landing page
                        $_SESSION["warning"] = "user update successfuly";
                        header("location: index.php");
                        exit();
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                }

            }
        }

        ?>
        <h3 class="mt-5 text-center">update Users Data</h3>
        <div class="row shadow p-3 mb-5 bg-white rounded">
            <div class="m-4 col-11 shadow-sm  p-3 bg-light rounded">
                <form action="<?php echo $_SERVER["PHP_SELF"] . "?id=" . trim($_GET['id']) ?>" method="post">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name"
                            class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $name; ?>">
                        <span class="invalid-feedback">
                            <?php echo $name_err; ?>
                        </span>
                    </div>
                    <div class="form-group mt-1">
                        <label>Password</label>
                        <input type="text" name="password"
                            class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $password; ?>">
                        <span class="invalid-feedback">
                            <?php echo $password_err; ?>
                        </span>
                    </div>
                    <button type="submit" name="action" value="update" class="btn btn-primary mt-3">Submit</button>
                </form>
            </div>
        </div>


        <!-- Content here -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

</body>

</html>