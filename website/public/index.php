<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        $project_root = __DIR__ . "/..";
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('html_errors', 1);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        include $project_root . "/src/components/layout.php";

        //load environment variables
        include $project_root . "/src/envParser.php";
        LoadEnv();
    ?>
    <title>Todos API</title>
</head>
<body>
    <div class="min-h-screen flex flex-col justify-between w-full">
        <?php
            include $project_root . "/src/components/nav.php";
        ?>

        <main class="flex items-center flex-col md:mt-8 index">
            <div class="max-w-screen-lg bg-white shadow-lg rounded-lg mx-auto text-center py-12 px-4 mt-4 border">
                <h1 class="title1">
                    Todos Simple API
                </h1>
                <p class="mt-8">
                    <strong>a simple API to test and prototype todo front end applications</strong>
                </p>
                <div class="mt-8 md:px-32 md:text-center text-left">
                    Create, read, update, and delete todos all from an easy-to-use API.  Includes user account functionality.  Just provide an
                    email, enter the 6 digit code we send you, and your api key is shown right away.  Full documentation provided using Swagger and
                    OpenApi 3.0.  Sign up today!
                </div>
                <div class="mt-8 flex justify-center">
                    <div class="inline-flex rounded-md">
                        <a href="/get-key" class="btn">
                            Get&nbsp;Started!
                        </a>
                    </div>
                </div>
            </div>

            <div class="max-w-screen-lg w-full bg-white shadow-lg rounded-lg mx-auto px-3 py-12 px-4 mt-4 border">
                <h2 class="text-1xl leading-9 tracking-tight text-theme-green sm:text-2xl sm:leading-10">
                    <a href="#GettingStarted">Getting Started</a>
                </h2>
                <p class="mt-4">
                    There are two ways to use the API: Via general non-user general todos CRUD api operations, or for a little more complexity,
                    and to give developers an opportunity to play around with bearer token authentication, via registering users, and then tying
                    all CRUD todo operations to users specifically. There is currently a limit of 100 general todos, a limit of 25 todos per
                    user account, and a limit of 10 users per apikey.
                </p>
                <h3 class="mt-8">
                    <h3><a href="#APIKeyOnly">Generic Todos</a></h3>
                    <p class="mt-4">
                        Once you have registered for your own api key, it's easy to get started.  Here's the supported methods:
                    </p>
<pre>
Retrieve all todos: GET /api/todos?apikey=xxxxxxxxxxxxxx
Retrieve specific todo: GET /api/todos/{id}?apikey=xxxxxxxxxxxxxx
Create new todo: POST /api/todos?apikey=xxxxxxxxxxxxxx
Update an existing todo: PUT /api/todos/{id}?apikey=xxxxxxxxxxxxxx
Delete an existing todo: DELETE /api/todos/{id}?apikey=xxxxxxxxxxxxxx
Delete all todos: DELETE /api/todos?apikey=xxxxxxxxxxxxxx</pre>
                    <p class="mt-4">
                        Below is what the standard body in a POST or PUT should look like.  The meta tag and completed flags are optional.
                        The meta property, if used, must be in JSON format, not exceeding 256 bytes.  Meta can be a useful
                        field to store additional data for the todo.
                    </p>
<pre>
{
    "description": "Buy Milk",
    "completed": false,
    "meta":
        {
            "assigned_to": "bob",
            "priority": "low",
            "notes": "really need to get this completed"
        }
}</pre>
                </h3>
                <div class="mt-8">
                    <h3><a href="#UserAccounts">User Accounts</a></h3>
                    <div class="mt-4">
                        To get started users will be required to register with email and password, the POST should include:
<pre>
{
    "name": "Joe Smith",
    "email": "joe@smith.com",
    "password": "12345678"
}</pre>
                        If successful, the following response will contain the token:
<pre>
{
    "name": "John Smith",
    "email": "joe@smith.com",
    "enabled": true,
    "token": "14a6be7a-d00b-44e6-a0f1-c0ebd6171936",
    "admin": false,
    "id": 1
}</pre>
                        Every subsequent call must now include the Authorization header with the bearer token:
<pre>Authorization: Bearer 14a6be7a-d00b-44e6-a0f1-c0ebd6171936</pre>
                        The calls to the users based todos api are very similar to the generic todos api, the url now
                    just needs to include the /users/{user_id} prefix, like this:
<pre>https://todos.simpleapi.dev/api/users/3/todos?apikey=xxxxxxxxxxxxxxxxxxxxx</pre>
                    </div>
                </div>

                <div class="mt-4">
                    <strong>Your own auth service (optional):</strong> if you already have an auth service, or use the
                    <a class="link" href="https://auth.simpleapi.dev">SimpleAPI Auth API</a>, set it as the auth URL of this apikey:
<p>Open the <a class="link" href="https://keys.simpleapi.dev/manage">key manager</a>, log in with an emailed code (no password), and enter the auth URL for that key, for example <code>https://auth.simpleapi.dev/api/users/me?apikey=your-auth-key</code>. Clear the box to switch back.</p>
                    This API then calls your auth URL with <code>Authorization: Bearer &lt;token&gt;</code> and expects <code>200</code> with
                    <code>{"id", "name", "email", "email_verified"}</code> for a valid user, or <code>401</code>. The first time a user is seen a matching user is created here, so
                    ownership of todos keeps working. Register, login, logout and password reset on this API then answer <code>409</code>.
                    The URL must be https and public, and the answer is cached for a minute.
                </div>
                <div class="mt-4">
                    <h3>Forgot a Password</h3>
                    <p>Send <code>POST /api/users/forgot</code> with the user's email and a 6 digit code is emailed (valid for 15 minutes, usable once).
                    Then <code>POST /api/users/reset</code> with the email, code and new password sets it and signs the user out everywhere.</p>
                </div>
                <div class="mt-4">
                    <h3>Admin User Account</h3>
                    <p>Registering with the email used to register the apikey makes the admin account. That one answers <code>202</code> and emails a 6 digit code; send it with the email to
                    <code>users/register/confirm</code> and the account is created as an admin and logged in. Admin rights include listing users and deleting one or all users.
                    A normal registration returns "admin": false; the confirmed developer account returns "admin": true:</p>
<pre>
{
    "name": "Happy Dev",
    "email": "happydev@devological.me",
    "enabled": true,
    "token": "b3fb015d-5ab3-4b8e-9d30-48fe8d060321",
    "admin": true,
    "id": 2
}</pre>
                    <p>Admin users can include the query parameter <pre>?admin=true</pre> when calling the delete all users method that will
                        also remove their account and log them out.  Register and confirm again to become an admin again.</p>
                </div>
                <div class="mt-4">
                    <h3>Bugs, Issues, Requests, Feedback</h3>
                    <p class="inline">Chances are things will come up, be it bugs or ideas.  We welcome your feedback.  Please post your
                        comments <a class="link" href="https://github.com/simple-api-dev/todos/issues">here</a> on our Github page.</p>
                </div>
            </div>
        </main>

        <div>
            <?php
                include $project_root ."/src/components/footer.php";
            ?>
        </div>
    </div>
</body>
</html>