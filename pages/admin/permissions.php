<?php

$pageTitle = 'Permissions | Admin';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

ob_start();

?>

<link
    rel="stylesheet"
    href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="h3 mb-1">
            Permission Management
        </h1>

        <p class="text-muted mb-0">
            Manage user permissions.
        </p>

    </div>

</div>

<div class="card border-0 shadow-sm">

    <div class="card-body">

        <div class="table-responsive">

            <table
                class="table align-middle mb-0"
                id="permissionsTable"
            >

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Username</th>

                        <th>Email</th>

                        <th>Create Blog</th>

                        <th>Update Blog</th>

                        <th>Delete Blog</th>

                    </tr>

                </thead>

                <tbody id="permissionsTableBody">

                    <tr>

                        <td
                            colspan="7"
                            class="text-center text-muted py-4"
                        >
                            Loading permissions...
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<div
    class="modal fade"
    id="permissionConfirmModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Confirm Permission Update
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <div class="modal-body">

                Are you sure you want to update
                this permission?

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
                    id="confirmPermissionUpdateBtn"
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

$(document).ready(function () {

    let selectedCheckbox = null;

    let selectedUserId = null;

    let selectedPermissionId = null;

    let selectedEnabled = false;

    let previousCheckedState = false;

    let permissionUpdateSucceeded = false;

    let permissionUpdateInProgress = false;

    const permissionModal =
        new bootstrap.Modal(
            document.getElementById(
                "permissionConfirmModal"
            )
        );

    function loadPermissions() {

        $.ajax({

            url:
                BASE_URL +
                "/api/admin/permissions",

            type: "GET",

            dataType: "json",

            success: function (response) {

                if (!response.success) {
                    return;
                }

                const users =
                    response.data.users;

                const permissions =
                    response.data.permissions;

                const userPermissions =
                    response.data.userPermissions;

                let rows = "";

                if (users.length === 0) {

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

                    users.forEach(function (user) {

                        function hasPermission(
                            permissionCode
                        ) {

                            const permission =
                                permissions.find(
                                    p =>
                                        p.code ===
                                        permissionCode
                                );

                            if (!permission) {
                                return false;
                            }

                            return userPermissions.some(
                                up =>
                                    parseInt(
                                        up.user_id
                                    ) ===
                                        parseInt(
                                            user.id
                                        ) &&
                                    parseInt(
                                        up.permission_id
                                    ) ===
                                        parseInt(
                                            permission.id
                                        )
                            );
                        }

                        function getPermissionId(
                            permissionCode
                        ) {

                            const permission =
                                permissions.find(
                                    p =>
                                        p.code ===
                                        permissionCode
                                );

                            return permission
                                ? permission.id
                                : "";
                        }

                        rows += `
                            <tr>

                                <td>
                                    ${user.id}
                                </td>

                                <td>
                                    ${user.name}
                                </td>

                                <td>
                                    ${user.username}
                                </td>

                                <td>
                                    ${user.email}
                                </td>

                                <td class="text-center">

                                    <input
                                        type="checkbox"
                                        class="
                                            form-check-input
                                            permission-checkbox
                                        "
                                        data-user-id="${user.id}"
                                        data-permission-id="${getPermissionId("BLOG_CREATE")}"
                                        data-current-checked="${hasPermission("BLOG_CREATE") ? 1 : 0}"
                                        ${
                                            hasPermission("BLOG_CREATE")
                                            ? "checked"
                                            : ""
                                        }
                                    >

                                </td>

                                <td class="text-center">

                                    <input
                                        type="checkbox"
                                        class="
                                            form-check-input
                                            permission-checkbox
                                        "
                                        data-user-id="${user.id}"
                                        data-permission-id="${getPermissionId("BLOG_UPDATE")}"
                                        data-current-checked="${hasPermission("BLOG_UPDATE") ? 1 : 0}"
                                        ${
                                            hasPermission("BLOG_UPDATE")
                                            ? "checked"
                                            : ""
                                        }
                                    >

                                </td>

                                <td class="text-center">

                                    <input
                                        type="checkbox"
                                        class="
                                            form-check-input
                                            permission-checkbox
                                        "
                                        data-user-id="${user.id}"
                                        data-permission-id="${getPermissionId("BLOG_DELETE")}"
                                        data-current-checked="${hasPermission("BLOG_DELETE") ? 1 : 0}"
                                        ${
                                            hasPermission("BLOG_DELETE")
                                            ? "checked"
                                            : ""
                                        }
                                    >

                                </td>

                            </tr>
                        `;
                    });
                }

                $("#permissionsTableBody").html(
                    rows
                );

                if (
                    $.fn.DataTable.isDataTable(
                        "#permissionsTable"
                    )
                ) {

                    $("#permissionsTable")
                        .DataTable()
                        .destroy();
                }

                $("#permissionsTable").DataTable({

                    pageLength: 10,

                    ordering: false,

                    info: true,

                    searching: true,

                    lengthChange: false
                });
            },

            error: function () {

                $("#permissionsTableBody").html(`
                    <tr>
                        <td
                            colspan="7"
                            class="text-center text-danger py-4"
                        >
                            Failed to load permissions.
                        </td>
                    </tr>
                `);
            }
        });
    }

    loadPermissions();

    $(document).on(
        "change",
        ".permission-checkbox",
        function () {

            const $checkbox = $(this);

            const committedState =
                String(
                    $checkbox.data(
                        "current-checked"
                    )
                ) === "1";

            if (
                $checkbox.data("busy") ||
                permissionUpdateInProgress ||
                $checkbox.data("pending")
            ) {

                $checkbox.prop(
                    "checked",
                    committedState
                );

                return;
            }

            selectedCheckbox = $checkbox;

            permissionUpdateSucceeded = false;

            selectedUserId = $checkbox.data("user-id");

            selectedPermissionId = $checkbox.data(
                    "permission-id"
                );

            selectedEnabled = $checkbox.is(":checked");

            previousCheckedState = committedState;

            if (selectedEnabled === committedState) {
                return;
            }

            selectedCheckbox.data(
                "pending",
                true
            );

            permissionModal.show();
        }
    );

    $("#confirmPermissionUpdateBtn").on(
        "click",
        function () {

            if (
                !selectedCheckbox ||
                permissionUpdateInProgress
            ) {
                return;
            }

            permissionUpdateInProgress = true;

            $(this).prop(
                "disabled",
                true
            );

            selectedCheckbox.prop(
                "disabled",
                true
            );

            selectedCheckbox.data(
                "busy",
                true
            );

            $.ajax({

                url:
                    BASE_URL +
                    "/api/admin/permissions-update",

                type: "PUT",

                contentType: "application/json",

                data: JSON.stringify({

                    userId:
                        selectedUserId,

                    permissionId:
                        selectedPermissionId,

                    enabled:
                        selectedEnabled
                }),

                dataType: "json",

                success: function (response) {

                    const message =
                        response.message ||
                        "Permission updated successfully.";

                    if (response.success) {

                        permissionUpdateSucceeded = true;

                        selectedCheckbox.prop(
                            "checked",
                            selectedEnabled
                        );

                        selectedCheckbox.data(
                            "current-checked",
                            selectedEnabled ? 1 : 0
                        );

                        selectedCheckbox.data(
                            "pending",
                            false
                        );

                        selectedCheckbox.data(
                            "busy",
                            false
                        );

                        selectedCheckbox.prop(
                            "disabled",
                            false
                        );

                        $(
                            "#confirmPermissionUpdateBtn"
                        ).prop(
                            "disabled",
                            false
                        );

                        permissionModal.hide();

                        console.log(message);

                        loadPermissions();

                    } else {

                        selectedCheckbox.prop(
                            "checked",
                            previousCheckedState
                        );

                        selectedCheckbox.data(
                            "pending",
                            false
                        );

                        selectedCheckbox.data(
                            "busy",
                            false
                        );

                        selectedCheckbox.prop(
                            "disabled",
                            false
                        );

                        $(
                            "#confirmPermissionUpdateBtn"
                        ).prop(
                            "disabled",
                            false
                        );

                        permissionUpdateInProgress = false;

                        console.log(message);
                    }
                },

                error: function () {

                    selectedCheckbox.prop(
                        "checked",
                        previousCheckedState
                    );

                    selectedCheckbox.data(
                        "pending",
                        false
                    );

                    selectedCheckbox.data(
                        "busy",
                        false
                    );

                    selectedCheckbox.prop(
                        "disabled",
                        false
                    );

                    $(
                        "#confirmPermissionUpdateBtn"
                    ).prop(
                        "disabled",
                        false
                    );

                    permissionUpdateInProgress = false;

                    console.log(
                        "Failed to update permission."
                    );
                }
            });
        }
    );

    $("#permissionConfirmModal").on(
        "hidden.bs.modal",
        function () {

            if (
                selectedCheckbox &&
                !permissionUpdateSucceeded
            ) {

                selectedCheckbox.prop(
                    "checked",
                    previousCheckedState
                );
            }

            if (selectedCheckbox) {

                selectedCheckbox.data(
                    "pending",
                    false
                );

                selectedCheckbox.data(
                    "busy",
                    false
                );

                selectedCheckbox.prop(
                    "disabled",
                    false
                );
            }

            $(
                "#confirmPermissionUpdateBtn"
            ).prop(
                "disabled",
                false
            );

            permissionUpdateInProgress = false;

            permissionUpdateSucceeded = false;

            selectedCheckbox = null;
        }
    );
});

</script>
';

require_once __DIR__ . '/../../layouts/AdminLayout.php';