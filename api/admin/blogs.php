    <?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
    require_once __DIR__ . '/../Helpers/ResponseHelper.php';

    if (
        empty($_SESSION['user']) ||
        ($_SESSION['user']['role'] ?? '') !== 'ADMIN'
    ) {

        response_send([
            'success' => false,
            'status' => 401,
            'message' => 'Unauthorized',
            'errors' => [],
            'data' => [],
            'redirect' => null
        ]);
    }

    $search = trim($_GET['search'] ?? '');
$visibility = trim($_GET['visibility'] ?? '');
$status = trim($_GET['status'] ?? '');

$query = "
    SELECT
        blogs.id,
        blogs.title,
        blogs.slug,
        blogs.category,
        blogs.visibility,
        blogs.status,
        blogs.created_at,
        users.name AS creator_name
    FROM blogs
    INNER JOIN users
        ON users.id = blogs.user_id
";

$where = [
    "blogs.status != 'DELETED'"
];

$params = [];

if ($search !== '') {

    $where[] = "
        (
            blogs.title LIKE ?
            OR users.name LIKE ?
        )
    ";

    $searchTerm = '%' . $search . '%';

    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($visibility !== '') {

    $where[] = "blogs.visibility = ?";

    $params[] = $visibility;
}

if ($status !== '') {

    $where[] = "blogs.status = ?";

    $params[] = $status;
}

if (!empty($where)) {

    $query .= "
        WHERE
        " . implode(' AND ', $where);
}

$query .= "
    ORDER BY blogs.created_at DESC
";

$blogs = fetchAll(
    $query,
    $params
);

    $blogIds = array_column($blogs, 'id');

    $thumbnailMap = getBlogThumbnailMap(
        $blogIds
    );

    foreach ($blogs as &$blog) {

        $blog['thumbnail'] =
            $thumbnailMap[$blog['id']] ?? null;
    }

    response_send([
        'success' => true,
        'status' => 200,
        'message' => 'Blogs fetched successfully',
        'errors' => [],
        'data' => $blogs,
        'redirect' => null
    ]);