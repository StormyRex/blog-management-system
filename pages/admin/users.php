<?php

$pageTitle = 'Users | Admin';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

ob_start();

?>

<link
    rel="stylesheet"
    href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
>

<div class="mb-5 mt-2">
    <span class="hero-pill mb-2">Management</span>
    <h1 class="hero-title text-start mb-1" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">User Management</h1>
    <p class="text-muted mb-0" style="font-size: 0.95rem;">View all registered creators and administrators.</p>
</div>

<div class="admin-card">

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <input
                type="text"
                class="form-control"
                id="userSearchInput"
                placeholder="Search name, username or email"
            >

        </div>

        <div class="col-md-3">

            <select
                class="form-select"
                id="userRoleFilter"
            >

                <option value="">
                    All Roles
                </option>

                <option value="ADMIN">
                    ADMIN
                </option>

                <option value="USER">
                    USER
                </option>

            </select>

        </div>

        <div class="col-md-3">

            <select
                class="form-select"
                id="userStatusFilter"
            >

                <option value="">
                    All Statuses
                </option>

                <option value="ACTIVE">
                    ACTIVE
                </option>

                <option value="BLOCKED">
                    BLOCKED
                </option>

                <option value="DEACTIVATED">
                    DEACTIVATED
                </option>

            </select>

        </div>

        <div class="col-md-2">

            <button
                class="btn-outline w-100 justify-content-center py-2"
                id="resetUserFiltersBtn"
            >
                Reset
            </button>

        </div>

    </div>

    <div class="table-responsive">

        <table
            class="table align-middle mb-0 table-hover"
            id="usersTable"
        >

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Username</th>

                    <th>Email</th>

                    <th>Role</th>

                    <th>Status</th>

                    <th>Created</th>

                </tr>

            </thead>

            <tbody id="usersTableBody">

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted py-4"
                    >
                        Loading users...
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<div
    class="modal fade"
    id="statusConfirmModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Confirm Status Change
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <div class="modal-body">

                Are you sure you want to update
                this user's status?

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
                    id="confirmStatusChangeBtn"
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

const userFilters = {

    search: "",

    role: "",

    status: ""
};

$(document).ready(function () {

    let userSearchTimeout = null;

    let selectedUserId = null;

    let selectedStatus = null;

    let previousStatus = null;

    let currentSelectElement = null;

    let statusChangeConfirmed = false;

    const statusModal = new bootstrap.Modal(
        document.getElementById(
            "statusConfirmModal"
        )
    );

    function updateUserStatusSelectStyle(
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

        } else if (status === "BLOCKED") {

            selectElement.addClass(
                "border-warning text-warning"
            );

        } else if (status === "DEACTIVATED") {

            selectElement.addClass(
                "border-danger text-danger"
            );

        } else {

            selectElement.addClass(
                "border-secondary text-secondary"
            );
        }
    }

    function loadUsers(filters = {}) {

        $.ajax({

            url: BASE_URL + "/api/admin/users",

            type: "GET",

            data: {

                search: filters.search || "",

                role: filters.role || "",

                status: filters.status || ""
            },

            dataType: "json",

            success: function (response) {

                if (!response.success) {
                    return;
                }

                let rows = "";

                if (response.data.length === 0) {

                    rows = `
                        <tr>
                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                No users found.
                            </td>
                        </tr>
                    `;

                } else {

                    response.data.forEach(function (user) {

                        rows += `
                            <tr>

                                <td>${user.id}</td>

                                <td>${user.name}</td>

                                <td>${user.username}</td>

                                <td>${user.email}</td>

                                <td>${user.role}</td>

                                <td>

                                    ${
                                        user.role === "ADMIN"
                                        ? `
                                            <span class="badge bg-dark">
                                                Protected
                                            </span>
                                        `
                                        : `
                                            <select
                                                class="
                                                    form-select
                                                    form-select-sm
                                                    user-status-select
                                                    ${
                                                        user.status === "ACTIVE"
                                                        ? "border-success text-success"
                                                        : user.status === "BLOCKED"
                                                        ? "border-warning text-warning"
                                                        : user.status === "DEACTIVATED"
                                                        ? "border-danger text-danger"
                                                        : "border-secondary text-secondary"
                                                    }
                                                "
                                                data-user-id="${user.id}"
                                                data-current-status="${user.status}"
                                            >

                                                <option
                                                    value="ACTIVE"
                                                    ${
                                                        user.status === "ACTIVE"
                                                        ? "selected"
                                                        : ""
                                                    }
                                                >
                                                    ACTIVE
                                                </option>

                                                <option
                                                    value="BLOCKED"
                                                    ${
                                                        user.status === "BLOCKED"
                                                        ? "selected"
                                                        : ""
                                                    }
                                                >
                                                    BLOCKED
                                                </option>

                                                <option
                                                    value="DEACTIVATED"
                                                    ${
                                                        user.status === "DEACTIVATED"
                                                        ? "selected"
                                                        : ""
                                                    }
                                                >
                                                    DEACTIVATED
                                                </option>

                                            </select>
                                        `
                                    }

                                </td>

                                <td>${user.created_at}</td>

                            </tr>
                        `;
                    });
                }

                if ($.fn.DataTable.isDataTable("#usersTable")) {

                    $("#usersTable").DataTable().destroy();
                }

                $("#usersTableBody").html(rows);

                if (response.data.length > 0) {

                    $("#usersTable").DataTable({

                        pageLength: 10,

                        ordering: false,

                        info: true,

                        searching: false,

                        lengthChange: false
                    });
                }
            },

            error: function () {

                if ($.fn.DataTable.isDataTable("#usersTable")) {

                    $("#usersTable").DataTable().destroy();
                }

                $("#usersTableBody").html(`
                    <tr>
                        <td
                            colspan="7"
                            class="text-center text-danger py-4"
                        >
                            Failed to load users.
                        </td>
                    </tr>
                `);
            }
        });
    }

    loadUsers(userFilters);

    $("#userSearchInput").on(
        "keyup",
        function () {

            clearTimeout(userSearchTimeout);

            const searchValue =
                $(this).val().trim();

            userSearchTimeout = setTimeout(
                function () {

                    userFilters.search =
                        searchValue;

                    loadUsers(userFilters);

                },
                400
            );
        }
    );

    $("#userRoleFilter").on(
        "change",
        function () {

            userFilters.role =
                $(this).val();

            loadUsers(userFilters);
        }
    );

    $("#userStatusFilter").on(
        "change",
        function () {

            userFilters.status =
                $(this).val();

            loadUsers(userFilters);
        }
    );

    $("#resetUserFiltersBtn").on(
        "click",
        function () {

            userFilters.search = "";
            userFilters.role = "";
            userFilters.status = "";

            $("#userSearchInput").val("");
            $("#userRoleFilter").val("");
            $("#userStatusFilter").val("");

            loadUsers(userFilters);
        }
    );

    $(document).on(
        "change",
        ".user-status-select",
        function () {

            const $select = $(this);

            if ($select.data("busy")) {
                return;
            }

            currentSelectElement = $select;

            selectedUserId =
                $select.data("user-id");

            previousStatus =
                $select.data("current-status");

            selectedStatus =
                $select.val();

            if (selectedStatus === previousStatus) {
                return;
            }

            statusChangeConfirmed = false;

            statusModal.show();
        }
    );

    $("#confirmStatusChangeBtn").on(
        "click",
        function () {

            if (!currentSelectElement) {
                return;
            }

            currentSelectElement.data(
                "busy",
                true
            );

            $.ajax({

                url:
                    BASE_URL +
                    "/api/admin/user/status",

                type: "PUT",

                contentType: "application/json",

                data: JSON.stringify({

                    userId: selectedUserId,

                    status: selectedStatus
                }),

                dataType: "json",

                success: function (response) {

                    if (response.success) {

                        statusChangeConfirmed = true;

                        currentSelectElement.data(
                            "current-status",
                            selectedStatus
                        );

                        currentSelectElement.val(
                            selectedStatus
                        );

                        updateUserStatusSelectStyle(
                            currentSelectElement,
                            selectedStatus
                        );

                        previousStatus =
                            selectedStatus;

                        statusModal.hide();

                        loadUsers(userFilters);

                    } else {

                        currentSelectElement.val(
                            previousStatus
                        );

                        alert(response.message);
                    }

                    currentSelectElement.data(
                        "busy",
                        false
                    );
                },

                error: function () {

                    currentSelectElement.val(
                        previousStatus
                    );

                    currentSelectElement.data(
                        "busy",
                        false
                    );

                    alert(
                        "Failed to update user status."
                    );
                }
            });
        }
    );

    $("#statusConfirmModal").on(
        "hidden.bs.modal",
        function () {

            if (
                currentSelectElement &&
                !statusChangeConfirmed
            ) {

                currentSelectElement.val(
                    previousStatus
                );
            }

            if (currentSelectElement) {

                updateUserStatusSelectStyle(
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