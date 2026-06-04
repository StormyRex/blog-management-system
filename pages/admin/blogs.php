<?php

$pageTitle = 'Blogs | Admin';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

require_once __DIR__ . '/../../api/Helpers/PermissionHelper.php';

$canUpdateBlog = current_user_has_permission('BLOG_UPDATE');
$canDeleteBlog = current_user_has_permission('BLOG_DELETE');

ob_start();

?>

<link
    rel="stylesheet"
    href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
>

<div class="mb-5 mt-2">
    <span class="hero-pill mb-2">Management</span>
    <h1 class="hero-title text-start mb-1" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">Blog Management</h1>
    <p class="text-muted mb-0" style="font-size: 0.95rem;">Moderate and manage all published and draft articles.</p>
</div>

<div class="admin-card">

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <input
                type="text"
                class="form-control"
                id="blogSearchInput"
                placeholder="Search title or creator"
            >

        </div>

        <div class="col-md-3">

            <select
                class="form-select"
                id="blogVisibilityFilter"
            >

                <option value="">
                    All Visibility
                </option>

                <option value="PUBLIC">
                    PUBLIC
                </option>

                <option value="PRIVATE">
                    PRIVATE
                </option>

            </select>

        </div>

        <div class="col-md-3">

            <select
                class="form-select"
                id="blogStatusFilter"
            >

                <option value="">
                    All Statuses
                </option>

                <option value="ACTIVE">
                    ACTIVE
                </option>

                <option value="RESTRICTED">
                    RESTRICTED
                </option>

                <option value="DELETED">
                    DELETED
                </option>

            </select>

        </div>

        <div class="col-md-2">

            <button
                class="btn-outline w-100 justify-content-center py-2"
                id="resetBlogFiltersBtn"
            >
                Reset
            </button>

        </div>

    </div>

    <div class="table-responsive">

        <table
            class="table align-middle mb-0 table-hover"
            id="blogsTable"
        >

            <thead>

                <tr>

                    <th>Thumbnail</th>

                    <th>Title</th>

                    <th>Creator</th>

                    <th>Category</th>

                    <th>Visibility</th>

                    <th>Status</th>

                    <th>Created</th>

                </tr>

            </thead>

            <tbody id="blogsTableBody">

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted py-4"
                    >
                        Loading blogs...
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<div
    class="modal fade"
    id="blogStatusConfirmModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Confirm Blog Status Change
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <div class="modal-body">

                Are you sure you want to update
                this blog status?

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-dark"
                    id="confirmBlogStatusChangeBtn"
                >
                    Confirm
                </button>

            </div>

        </div>

    </div>

</div>

<?php

$content = ob_get_clean();

$scripts = '

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>

const BASE_URL =
    "' . $baseUrl . '";

const CAN_UPDATE_BLOG =
    ' . ($canUpdateBlog ? 'true' : 'false') . ';

const CAN_DELETE_BLOG =
    ' . ($canDeleteBlog ? 'true' : 'false') . ';

const blogFilters = {

    search: "",

    visibility: "",

    status: ""
};

$(document).ready(function () {

    let blogSearchTimeout = null;

    let selectedBlogId = null;

    let selectedStatus = null;

    let previousStatus = null;

    let currentSelectElement = null;

    let deleteBlogId = null;

    let currentModalAction = null;

    const blogStatusModal =
        new bootstrap.Modal(
            document.getElementById(
                "blogStatusConfirmModal"
            )
        );

    function loadBlogs(filters = {}) {

        $.ajax({

            url: BASE_URL + "/api/admin/blogs",

            type: "GET",

            data: {

                search: filters.search || "",

                visibility: filters.visibility || "",

                status: filters.status || ""
            },

            dataType: "json",

            success: function (response) {

                if (!response.success) {
                    return;
                }

                if ($.fn.DataTable.isDataTable("#blogsTable")) {

                    $("#blogsTable").DataTable().destroy();
                }

                let rows = "";

                if (response.data.length === 0) {

                    rows = `
                        <tr>
                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                No blogs found.
                            </td>
                        </tr>
                    `;

                } else {

                    response.data.forEach(function (blog) {

                        let thumbnailHtml = `
                            <div
                                class="bg-light border rounded d-flex align-items-center justify-content-center"
                                style="
                                    width: 70px;
                                    height: 40px;
                                "
                            >
                                <i class="bi bi-image text-muted" style="font-size: 0.9rem;"></i>
                            </div>
                        `;

                        if (
                            blog.thumbnail &&
                            blog.thumbnail.url
                        ) {

                            if (
                                blog.thumbnail.type === "IMAGE"
                            ) {

                                thumbnailHtml = `
                                    <img
                                        src="${blog.thumbnail.url}"
                                        class="rounded border shadow-sm"
                                        style="
                                            width: 70px;
                                            height: 40px;
                                            object-fit: cover;
                                        "
                                    >
                                `;

                            } else {

                                thumbnailHtml = `
                                    <video
                                        class="rounded border shadow-sm"
                                        style="
                                            width: 70px;
                                            height: 40px;
                                            object-fit: cover;
                                        "
                                    >
                                        <source
                                            src="${blog.thumbnail.url}"
                                        >
                                    </video>
                                `;
                            }
                        }

                        rows += `
                            <tr>

                                <td>
                                    ${thumbnailHtml}
                                </td>

                                <td>
                                    <a href="${BASE_URL}/blogs/details?id=${blog.id}" target="_blank" class="fw-semibold text-dark">${blog.title}</a>
                                </td>

                                <td>
                                    ${blog.creator_name}
                                </td>

                                <td>
                                    ${blog.category}
                                </td>

                                <td>

                                    ${
                                        blog.visibility === "PUBLIC"
                                        ? `
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                                PUBLIC
                                            </span>
                                        `
                                        : `
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                                PRIVATE
                                            </span>
                                        `
                                    }

                                </td>

                                <td>

                                    ${
                                        CAN_UPDATE_BLOG
                                        ? `
                                            <select
                                                class="
                                                    form-select
                                                    form-select-sm
                                                    blog-status-select
                                                     ${
                                                         blog.status === "ACTIVE"
                                                         ? "border-success text-success"
                                                         : blog.status === "RESTRICTED"
                                                         ? "border-warning text-warning"
                                                         : blog.status === "DELETED"
                                                         ? "border-danger text-danger"
                                                         : "border-secondary text-secondary"
                                                     }
                                                 "
                                                 data-blog-id="${blog.id}"
                                                 data-current-status="${blog.status}"
                                             >

                                                 <option
                                                     value="ACTIVE"
                                                     ${
                                                         blog.status === "ACTIVE"
                                                         ? "selected"
                                                         : ""
                                                     }
                                                 >
                                                     ACTIVE
                                                 </option>

                                                 <option
                                                     value="RESTRICTED"
                                                     ${
                                                         blog.status === "RESTRICTED"
                                                         ? "selected"
                                                         : ""
                                                     }
                                                 >
                                                     RESTRICTED
                                                 </option>

                                                 ${
                                                     CAN_DELETE_BLOG
                                                     ? `
                                                         <option
                                                             value="DELETED"
                                                             ${
                                                                 blog.status === "DELETED"
                                                                 ? "selected"
                                                                 : ""
                                                             }
                                                             class="text-danger"
                                                             style="font-weight: 600;"
                                                         >
                                                             DELETED
                                                         </option>
                                                     `
                                                     : ""
                                                 }

                                             </select>
                                         `
                                         : `
                                             <span
                                                 class="badge ${
                                                     blog.status === "ACTIVE"
                                                     ? "bg-success"
                                                     : blog.status === "RESTRICTED"
                                                     ? "bg-warning text-dark"
                                                     : blog.status === "DELETED"
                                                     ? "bg-danger"
                                                     : "bg-secondary"
                                                 }"
                                             >
                                                ${blog.status}
                                            </span>
                                        `
                                    }

                                </td>

                                <td>
                                    ${blog.created_at}
                                </td>

                            </tr>
                        `;
                    });
                }

                $("#blogsTableBody").html(rows);

                if (response.data.length > 0) {
                    $("#blogsTable").DataTable({

                        pageLength: 10,

                        ordering: false,

                        info: true,

                        searching: false,

                        lengthChange: false
                    });
                }
            },

            error: function () {

                if ($.fn.DataTable.isDataTable("#blogsTable")) {

                    $("#blogsTable").DataTable().destroy();
                }

                $("#blogsTableBody").html(`
                    <tr>
                        <td
                            colspan="7"
                            class="text-center text-danger py-4"
                        >
                            Failed to load blogs.
                        </td>
                    </tr>
                `);
            }
        });
    }

    loadBlogs(blogFilters);

    $("#blogSearchInput").on(
        "keyup",
        function () {

            clearTimeout(blogSearchTimeout);

            const searchValue =
                $(this).val().trim();

            blogSearchTimeout = setTimeout(
                function () {

                    blogFilters.search =
                        searchValue;

                    loadBlogs(blogFilters);

                },
                400
            );
        }
    );

    $("#blogVisibilityFilter").on(
        "change",
        function () {

            blogFilters.visibility =
                $(this).val();

            loadBlogs(blogFilters);
        }
    );

    $("#blogStatusFilter").on(
        "change",
        function () {

            blogFilters.status =
                $(this).val();

            loadBlogs(blogFilters);
        }
    );

    $("#resetBlogFiltersBtn").on(
        "click",
        function () {

            blogFilters.search = "";
            blogFilters.visibility = "";
            blogFilters.status = "";

            $("#blogSearchInput").val("");
            $("#blogVisibilityFilter").val("");
            $("#blogStatusFilter").val("");

            loadBlogs(blogFilters);
        }
    );

    function updateBlogStatusSelectStyle(
        selectElement,
        status
    ) {

        selectElement.removeClass(
            "border-success text-success border-warning text-warning border-danger text-danger border-secondary text-secondary"
        );

        if (status === "ACTIVE") {

            selectElement.addClass(
                "border-success text-success"
            );

        } else if (status === "RESTRICTED") {

            selectElement.addClass(
                "border-warning text-warning"
            );

        } else if (status === "DELETED") {

            selectElement.addClass(
                "border-danger text-danger"
            );

        } else {

            selectElement.addClass(
                "border-secondary text-secondary"
            );
        }
    }

    $(document).on(
        "change",
        ".blog-status-select",
        function () {

            currentSelectElement = $(this);

            selectedBlogId =
                $(this).data("blog-id");

            previousStatus =
                $(this).data("current-status");

            selectedStatus =
                $(this).val();

            if (selectedStatus === previousStatus) {
                return;
            }

            if (selectedStatus === "DELETED") {

                deleteBlogId = selectedBlogId;

                currentModalAction = "delete";

                $("#blogStatusConfirmModal .modal-title")
                    .text("Confirm Blog Delete");

                $("#blogStatusConfirmModal .modal-body")
                    .text("Are you sure you want to delete this blog?");

                $("#confirmBlogStatusChangeBtn")
                    .text("Delete")
                    .removeClass("btn-danger btn-dark")
                    .addClass("btn-danger");

            } else {

                currentModalAction = "status";

                $("#blogStatusConfirmModal .modal-title")
                    .text("Confirm Blog Status Change");

                $("#blogStatusConfirmModal .modal-body")
                    .text("Are you sure you want to update this blog status?");

                $("#confirmBlogStatusChangeBtn")
                    .text("Confirm")
                    .removeClass("btn-danger btn-dark")
                    .addClass("btn-dark");
            }

            blogStatusModal.show();
        }
    );

    $("#confirmBlogStatusChangeBtn").on(
        "click",
        function () {

            if (currentModalAction === "status") {

                $.ajax({

                    url:
                        BASE_URL +
                        "/api/admin/blog/status",

                    type: "PUT",

                    contentType: "application/json",

                    data: JSON.stringify({

                        blogId: selectedBlogId,

                        status: selectedStatus
                    }),

                    dataType: "json",

                    success: function (response) {

                        if (response.success) {

                            currentSelectElement.data(
                                "current-status",
                                selectedStatus
                            );

                            updateBlogStatusSelectStyle(
                                currentSelectElement,
                                selectedStatus
                            );

                            previousStatus = selectedStatus;

                            blogStatusModal.hide();

                            loadBlogs(blogFilters);

                        } else {

                            currentSelectElement.val(
                                previousStatus
                            );

                            alert(response.message);
                        }
                    },

                    error: function () {

                        currentSelectElement.val(
                            previousStatus
                        );

                        alert(
                            "Failed to update blog status."
                        );
                    }
                });

            } else if (currentModalAction === "delete") {

                $.ajax({

                    url:
                        BASE_URL +
                        "/api/admin/blog/delete",

                    type: "PUT",

                    contentType: "application/json",

                    data: JSON.stringify({

                        blogId: deleteBlogId
                    }),

                    dataType: "json",

                    success: function (response) {

                        if (response.success) {

                            if (currentSelectElement) {
                                currentSelectElement.data(
                                    "current-status",
                                    selectedStatus
                                );
                                updateBlogStatusSelectStyle(
                                    currentSelectElement,
                                    selectedStatus
                                );
                            }
                            previousStatus = selectedStatus;

                            blogStatusModal.hide();

                            loadBlogs(blogFilters);

                        } else {

                            alert(response.message);
                        }
                    },

                    error: function () {

                        alert(
                            "Failed to delete blog."
                        );
                    }
                });
            }
        }
    );

    $("#blogStatusConfirmModal").on(
        "hidden.bs.modal",
        function () {

            if (
                currentSelectElement &&
                selectedStatus !== previousStatus
            ) {

                currentSelectElement.val(
                    previousStatus
                );
            }

            if (currentSelectElement) {

                updateBlogStatusSelectStyle(
                    currentSelectElement,
                    previousStatus
                );
            }
        }
    );
});

</script>
';

require_once __DIR__ . '/../../layouts/AdminLayout.php';