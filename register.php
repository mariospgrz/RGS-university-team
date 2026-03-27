<?php
require_once "./Include/header.php";
?>

<div class="register-box">
    <h2>Register</h2>

    <form action="#" method="post">
        <label>
            <input class="register_input" type="text" name="name" placeholder="Full Name" required>
            <input class="register_input" type="email" name="email" placeholder="Email Address" required>
            <input class="register_input" type="password" name="password" placeholder="Password" required>
            <input class="register_input" type="password" name="confirm_password" placeholder="Confirm Password" required>
        </label>

        <button class="register_button" type="submit">Register</button>
    </form>
</div>

</body>
</html>