<?php
   // The following PHP code will run, when the user click on the register button
    if(isset($_REQUEST['regUsername']))
    {
        // connect to DB
        $dbhandle = mysqli_connect("localhost", "root", "", "lab2");
        if (! $dbhandle)
        {
            echo "<span style='color:red'>Database Error</span>";
            exit;
        }

        $sql = "INSERT INTO user(username, password) VALUES(?, ?)";
        // note: the '?' is where you can bind a param to this SQL
    
        $username = $_REQUEST['regUsername'];
        $password = $_REQUEST['regPassword'];

        $hashed_password = crypt($password, 'lab2');

        $preparedStmt = mysqli_prepare($dbhandle, $sql);
        $preparedStmt->bind_param("ss", $username, $hashed_password);

        if ( ! $preparedStmt->execute() )
        {
            echo mysqli_error($dbhandle);
            echo "<br /><span style='color:red'>stmt did not execute!</span>";
        }
        else
        {
            echo "<span style='color:green'>Register Successfully. Now you can login!</span>";
        }
        exit;
    }

    // The following PHP code will run, when the user click on the login button
    if(isset($_REQUEST['logUsername']))
    {
        // connect to DB
        $dbhandle = mysqli_connect("localhost", "root", "", "lab2");
        if (! $dbhandle)
        {
            echo "<span style='color:red'>Database Error</span>";
            exit;
        }

        $sql = "SELECT password from user Where username like ?";
        // note: the '?' is where you can bind a param to this SQL
    
        $username = $_REQUEST['logUsername'];
        $password = $_REQUEST['logPassword'];
        
        $preparedStmt = mysqli_prepare($dbhandle, $sql);
        $preparedStmt->bind_param("s", $username);

        if($preparedStmt->execute())
        {
            /* store result */
            $preparedStmt->store_result();

            if($preparedStmt->num_rows>0)
            {
                // every row will now bind its column values to local vars
                $preparedStmt->bind_result($dbpassword);
                while ($preparedStmt->fetch())  // iterate through each row
						// assign to the $row array
                {
                    $hashed_password = crypt($password, 'lab2');
                    if (hash_equals($dbpassword, $hashed_password)) {
                        if(strtolower($username)=="administrator")
                        {
                            echo "Admin verified";
                        }
                        else
                        {
                            echo "Password verified!";
                        }
                    }
                    else
                    {
                        echo "<br /><span style='color:red'>Your password is incorrect!</span>";
                    }
                }
            }
            else
            {
                echo "<br /><span style='color:red'>Your username is incorrect!</span>";
            }
        }
        else
        {
            echo mysqli_error($dbhandle);
            echo "<br /><span style='color:red'>1111stmt did not execute!</span>";
        }
        exit;
    }

   // The following PHP code will run, when the user click on the login button
   // and the user type is adminstrator
    if(isset($_GET['userType'])){
        $dbhandle = mysqli_connect("localhost", "root", "", "lab2");
        if(! $dbhandle) // connection failed
        {
            exit();
        }

        $sql = "SELECT * from user ORDER BY username ASC";
        $result = mysqli_query($dbhandle, $sql); // $result is the returned result set
                            // as an object
        
        $data = array();
        while ($row = mysqli_fetch_assoc($result))  // iterate through each row
                            // assign to the $row array
        {
            $data[]=$row;
        }
        mysqli_close($dbhandle);	// disconnect from DB

        echo json_encode($data);
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 2</title>

    <style>
        #reg{
            width: 20%;
            float: left;
        }
        #login{
            width: 20%;
            float: left;
        }
			table, td, th  { border-style: solid; border-color: red;
					color: purple; }

    </style>
</head>
<body>
    <div id="divRegLogin">
        <fieldset id="reg">
            <legend>Register</legend>
            <form id="frmReg">
                Username: <br />
                <input type="text" id="regUsername" required="required" /> <br />
                Password: <br />
                <input type="password" id="regPassword" required="required" /> <br /> <br /> 
                <input type="submit" value="Register" /> <br/>

                <div id="divMsg"></div>
            </form>
        </fieldset>
        <fieldset id="login">
            <legend>Login</legend>
            <form id="frmLogin">
                Username: <br />
                <input type="text" id="logUsername" required="required" /> <br />
                Password: <br />
                <input type="password" id="logPassword" required="required" /> <br /> <br /> 
                <input type="submit" value="Login" /><br/>

                <div id="divMsgLog"></div>
            </form>
        </fieldset>
    </div>
    <div id="divWelcome">
        <span style="cursor:pointer; color:blue;" class="logout">Logout</span> <br/ > <br/ >
        <h2>Welcome, <span id="username"></span>!</h2>

        Following buttons are used to change the background color of the current page <br />  <br />
        <button class="changeColor">Azure</button>
        <button class="changeColor">Cyan</button>
        <button class="changeColor">White</button>
    </div>

    <div id="divAdmin">
        <span style="cursor:pointer; color:blue;" class="logout">Logout</span> <br/ > <br/ >
        <h2>Welcome, <span id="admin"></span>!</h2>

        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User name</th>
                    <th>Passowrd</th>
                </tr>
            </thead>
            <tbody id="tblUsers">
            <tbody>
        </table>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js "></script>
    <script>
        $(document).ready(function(){
            //on page load hide the welcome page
            $('#divWelcome').hide();
            $('#divAdmin').hide();

            // when user click on the register button
            $('#frmReg').submit(function(e){
                $('#divMsgLog').html('');
                e.preventDefault();
                 $.ajax({
                    method: 'POST',
                    data: {regUsername: $('#regUsername').val(), regPassword: $('#regPassword').val()},
                    success: function(msg) {
                        $('#regUsername').val('');
                        $('#regPassword').val('');
                        $('#divMsg').html(msg);
                    },
                    error: function( jqXhr, textStatus, errorThrown ){
                        alert( errorThrown );
                    }
                    
                });
                // to prevent refreshing the whole page page
                return false;
            });

            // when user click on the login button
            $('#frmLogin').submit(function(e){
                $('#divMsg').html('');
                e.preventDefault();
                 $.ajax({
                    method: 'POST',
                    data: {logUsername: $('#logUsername').val(), logPassword: $('#logPassword').val()},
                    success: function(msg) {
                        if(msg == 'Password verified!')
                        {
                            $('#username').html($('#logUsername').val());
                            $('#logUsername').val('');
                            $('#logPassword').val('');
                            $('#divRegLogin').hide();
                            $('#divWelcome').show();
                            $('#divMsgLog').html('');
                        }
                        else if(msg == 'Admin verified')
                        {
                            $('#admin').html($('#logUsername').val());
                            $('#logUsername').val('');
                            $('#logPassword').val('');
                            $('#divRegLogin').hide();
                            $('#divAdmin').show();
                            $('#divMsgLog').html('');
                            $.ajax({
                                method: 'GET',
                                data: {userType: 'admin'},
                                dataType: 'json',
                                contentType: 'application/json',
                                success: function(msg) {
                                    let tble = '';
                                    for(let i=0;i<msg.length;i++)
                                    {
                                        tble += `<tr>
                                                    <td>${msg[i].userid}</td>
                                                    <td>${msg[i].username}</td>
                                                    <td>${msg[i].password}</td>
                                                </tr>`;
                                    }
                                    $('#tblUsers').html(tble);
                                },
                                error: function( jqXhr, textStatus, errorThrown ){
                                    alert( errorThrown );
                                }
                                
                            });
                        }
                        else
                        {
                            $('#divMsgLog').html(msg);
                        }
                        
                    },
                    error: function( jqXhr, textStatus, errorThrown ){
                        alert( errorThrown );
                    }
                    
                });
                // to prevent refreshing the whole page page
                return false;
            });

            // when user click on the logout button.
            $('.logout').click(function(){
                $('#divRegLogin').show();
                $('#divWelcome').hide();
                $('#divAdmin').hide();
                $('#username').html('');
            });

            // When user click on any button to change the color
            $('.changeColor').click(function(){
                $('body').css("background-color", $(this).html());
            });
        });

        </script>
</body>
</html>