    <?php

    require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
    require_once __DIR__ . '/../Helpers/ResponseHelper.php';

    /*
    |--------------------------------------------------------------------------
    | Only POST Allowed
    |--------------------------------------------------------------------------
    */

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        response_error(
            'Method not allowed',
            405
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Inputs
    |--------------------------------------------------------------------------
    */

    $identity = trim($_POST['identity'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $errors = [];

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (empty($identity)) {

        $errors['identity'] = 'Email or username is required';

    }

    if (empty($password)) {

        $errors['password'] = 'Password is required';

    }

    /*
    |--------------------------------------------------------------------------
    | Validation Response
    |--------------------------------------------------------------------------
    */

    if (!empty($errors)) {

        response_error(
            'Validation failed',
            422,
            $errors
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Check User Credentials
    |--------------------------------------------------------------------------
    */

    $isEmail = filter_var($identity, FILTER_VALIDATE_EMAIL) !== false;

    if ($isEmail) {

        $user = fetchOne(
            "SELECT id, name, email, role, password, status, avatar FROM users WHERE email = ? LIMIT 1",
            [$identity]
        );

    } else {

        $user = fetchOne(
            "SELECT id, name, email, role, password, status, avatar FROM users WHERE username = ? LIMIT 1",
            [$identity]
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Verify Password
    |--------------------------------------------------------------------------
    */

    if (!$user || !password_verify($password, $user['password'])) {

        response_error(
            'Invalid email/username or password',
            401
        );
    }

    if (($user['status'] ?? 'ACTIVE') !== 'ACTIVE') {

        response_error(
            'Your account is blocked. Please contact the administrator.',
            401
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Set Session
    |--------------------------------------------------------------------------
    */

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar' => $user['avatar'] ?? null
    ];

    /*
    |--------------------------------------------------------------------------
    | Success Response
    |--------------------------------------------------------------------------
    */

    response_send([
        'success' => true,
        'status' => 200,
        'message' => 'Login successful',
        'data' => [],
        'errors' => [],
        'redirect' => null
    ], 200);
