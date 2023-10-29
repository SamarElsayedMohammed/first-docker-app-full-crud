<?php ob_start();
session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=h, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Document</title>
</head>

<body>
    <div class="container">
        <? if (isset($_SESSION["success"])) { ?>

            <div class='alert alert-success m-4' role='alert'>
                <?= $_SESSION["success"]
                    ?>
            </div>
            <? unset($_SESSION["success"]);
        } ?>
        <? if (isset($_SESSION["danger"])) { ?>

            <div class='alert alert-danger m-4' role='alert'>
                <?= $_SESSION["danger"]
                    ?>
            </div>
            <? unset($_SESSION["danger"]);
        } ?>
        <? if (isset($_SESSION["warning"])) { ?>

            <div class='alert alert-warning m-4' role='alert'>
                <?= $_SESSION["warning"]
                    ?>
            </div>
            <? unset($_SESSION["warning"]);
        } ?>
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

        if (!($result2 = mysqli_query($conn, 'SHOW DATABASES')))
            printf("Error: %s\n", mysqli_error($conn));

        $sql = 'SELECT * FROM users';
        if ($result = $conn->query($sql)) {
            while ($data = $result->fetch_object()) {
                $users[] = $data;
            }
        }

        ?>
        <?
        $name = $password = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == "Add") {
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
                // echo "<pre>";
                // print_r($_POST);
                // echo "</pre>";
                $sql = "INSERT INTO users (username, password) VALUES (?, ?)";

                if ($stmt = mysqli_prepare($conn, $sql)) {
                    // Bind variables to the prepared statement as parameters
        
                    // Set parameters
                    $param_name = $name;

                    $param_password = $password;
                    mysqli_stmt_bind_param($stmt, "ss", $param_name, $param_password);

                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {


                        $_SESSION["success"] = "user Add successfuly";
                        header("Location: index.php");
                        exit();
                        // echo "<script>location.reload();</script>";
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";

                    }
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }

            // Close connection
            // mysqli_close($conn);
        
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == "delete") {

            $param_id = trim($_POST["id"]);
            // Prepare a delete statement
            $sql = "DELETE FROM users WHERE id = ?";

            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "i", $param_id);

                // Set parameters
        
                if (mysqli_stmt_execute($stmt)) {
                    echo " <div class='alert alert-success m-4' role='alert'>
                    user deleted succesfuly
                    </div>";
                    $_SESSION["danger"] = "user deleted successfuly";
                    header("Location: index.php");
                    exit();
                    // echo "<script>location.reload();</script>";
                } else {
                    echo "Oops! Something went wrong. Please try again later.";

                }
            }
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == "update") {

            // Your PHP code here
            $data = array(
                'id' => $_POST['id'],
            );

            // Convert the data to a query string
            $queryString = http_build_query($data);

            // Redirect to the new location with the data
            header("Location: update.php?" . $queryString);
            exit();
        }
        ?>
        <h3 class="mt-5 text-center">Users Data</h3>
        <div class="row shadow p-3 mb-5 bg-white rounded">
            <div class="m-4 col-5 shadow-sm  p-3 bg-light rounded">
                <form form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                    <button type="submit" name="action" value="Add" class="btn btn-primary mt-3">Submit</button>
                </form>
            </div>
            <div class="m-3 col-6 shadow-sm p-3 bg-light rounded">
                <table class="table clo-6">
                    <thead>
                        <tr>
                            <th scope="col">name</th>
                            <th scope="col">password</th>
                            <th scope="col">actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $user) {
                            echo "<tr>";
                            echo "<td>" . $user->username . "</td><td> " . $user->password . "</td>";
                            echo "<td><div class='row'><div class='col-3'><form form action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . " method='post'>
                            <input type='hidden' name='id' value='" . $user->id . "'/>
                            <button class='m-1 btn btn-danger btn-sm' name='action' value='delete'><i class='fa-solid fa-trash'></i></button></form></div>";
                            echo "<div class='col-3'><form action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . " method='post'>
                            <input type='hidden' name='id' value='" . $user->id . "'/>
                            <button class='m-1 btn btn-warning btn-sm' name='action' value='update'><i class='fa-solid fa-pen-to-square'></i></button></form></div></div></td>";
                            echo "</tr>";

                        }
                        ?>


                    </tbody>
                </table>
            </div>


        </div>
        <h3 class='text-center mt-3'>Databases</h3>

        <div class="d-flex  justify-content-center align-items-center">
            <div class="col-12 w-75 p-3 shadow p-3 mb-5 bg-white rounded">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">DataBase Name</th>

                        </tr>
                    </thead>
                    <tbody>
                        <? $count = 1;
                        while ($row = mysqli_fetch_row($result2)) {

                            echo "<tr>";
                            echo "<td>" . $count . "</td><td> " . $row[0] . "</td>";
                            echo "</tr>";
                            $count++;
                        }


                        $result2->free_result();
                        ?>


                    </tbody>
                </table>
            </div>
        </div>


        <!-- Content here -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/fontawesome.min.js"
        crossorigin="anonymous"></script>

</body>

</html>