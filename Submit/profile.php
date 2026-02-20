<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/Assets/mainstyle/global.css">
</head>
<body>

<div class="profile-container">
    <h1>My Profile</h1>

    <form action="#" method="POST">

        <label for="firstname">First Name:</label>
        <input type="text" id="firstname" name="firstname" value="FirstName" required>

        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" value="LastName" required>


        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" value="1234567890" required>

        <label for="email">Email (Cannot be changed):</label>
        <input type="email" id="email" name="email" value="test@example.com" readonly>

        <button type="submit">Save Changes</button>

    </form>
</div>

</body>
</html>
