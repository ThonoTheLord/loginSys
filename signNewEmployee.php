<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styleLogin.css">
</head>

<body>
    <form action="includes/addNewEmployee.inc.php" class="wrapper" method="post">
        <div>
        <h1>
            Add New Employee
        </h1>
        <div class="mb-3">
            <label for="exampleInputName" class="form-label">Employee Full name</label>
            <input type="text"  name="name" placeholder="Full name" class="form-control" id="exampleInputFullName" aria-describedby="nameHelp">
        </div>
        <div class="mb-3">
            <label for="exampleInputEmail" class="form-label">Employee Email Address</label>
            <input type="email"  name="email" placeholder="Company Email" class="form-control" id="exampleInputEmail" aria-describedby="emailHelp">
        </div>
        <div class="mb-3">
            <label for="exampleInputuID" class="form-label">Unique Company ID</label>
            <input type="text"  name="uid" placeholder="Unique Co. ID" class="form-control" id="exampleInputUId" aria-describedby="nameUId">
        </div>
        <div class="mb-3">
            <label for="exampleInputPwd" class="form-label">Password</label>
            <input type="password"  name="pwd" placeholder="Password" class="form-control" id="exampleInputPassword" aria-describedby="passwordHelp">
        </div>
        <div class="mb-3">
            <label for="exampleInputPwdRepeat" class="form-label">Repeat Password</label>
            <input type="password"  name="pwdrepeat" placeholder="Repeat Password" class="form-control" id="exampleInputPassword" aria-describedby="passwordHelp">
        </div>
        <button type="submit" class="btn btn-primary">Add Employee</button>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>