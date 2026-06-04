<?php

$pageTitle = 'Contact Messages | Admin';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

ob_start();

?>

<link
    rel="stylesheet"
    href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
>

<div class="mb-5 mt-2">
    <span class="hero-pill mb-2">Support</span>
    <h1 class="hero-title text-start mb-1" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">Contact Messages</h1>
    <p class="text-muted mb-0" style="font-size: 0.95rem;">View and respond to inquiries submitted by visitors.</p>
</div>

<div class="admin-card">

    <div class="row g-3 mb-4">

        <div class="col-md-5">

            <input
                type="text"
                class="form-control"
                id="contactSearchInput"
                placeholder="Search name, email or subject"
            >

        </div>

        <div class="col-md-2">

            <button
                class="btn-outline w-100 justify-content-center py-2"
                id="resetContactFiltersBtn"
            >
                Reset
            </button>

        </div>

    </div>

    <div class="table-responsive">

        <table
            class="table align-middle mb-0 table-hover"
            id="contactsTable"
        >

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Email</th>

                    <th>Subject</th>

                    <th>Received At</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody id="contactsTableBody">

                <tr>

                    <td
                        colspan="6"
                        class="text-center text-muted py-4"
                    >
                        Loading messages...
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<div
    class="modal fade"
    id="viewMessageModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    View Message
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <strong>From:</strong> <span id="modalContactName"></span> &lt;<span id="modalContactEmail"></span>&gt;
                </div>
                <div class="mb-3">
                    <strong>Subject:</strong> <span id="modalContactSubject"></span>
                </div>
                <div class="mb-3">
                    <strong>Received:</strong> <span id="modalContactDate"></span>
                </div>
                <hr>
                <div class="mb-3">
                    <strong>Message:</strong>
                    <div id="modalContactMessage" class="mt-2 p-3 bg-light rounded" style="white-space: pre-wrap;"></div>
                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Close
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

const contactFilters = {

    search: ""
};

$(document).ready(function () {

    let contactSearchTimeout = null;
    let messagesData = [];

    const viewMessageModal = new bootstrap.Modal(
        document.getElementById(
            "viewMessageModal"
        )
    );

    function escapeHtml(text) {
        return $("<div>").text(text || "").html();
    }

    function loadContacts(filters = {}) {

        $.ajax({

            url: BASE_URL + "/api/admin/contacts",

            type: "GET",

            data: {

                search: filters.search || ""

            },

            dataType: "json",

            success: function (response) {

                if (!response.success) {
                    return;
                }
                
                messagesData = response.data;

                let rows = "";

                if (messagesData.length === 0) {
                    rows = `
                        <tr>
                            <td
                                colspan="6"
                                class="text-center text-muted py-4"
                            >
                                No messages found.
                            </td>
                        </tr>
                    `;
                } else {
                    messagesData.forEach(function (msg, index) {
                        rows += `
                            <tr>
                                <td>${msg.id}</td>
                                <td>${escapeHtml(msg.name)}</td>
                                <td>${escapeHtml(msg.email)}</td>
                                <td>${escapeHtml(msg.subject)}</td>
                                <td>${msg.created_at}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-dark view-message-btn" data-index="${index}">
                                        View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                if ($.fn.DataTable.isDataTable("#contactsTable")) {

                    $("#contactsTable").DataTable().destroy();
                }

                $("#contactsTableBody").html(rows);

                if (messagesData.length > 0) {
                    $("#contactsTable").DataTable({

                        pageLength: 10,

                        ordering: false,

                        info: true,

                        searching: false,

                        lengthChange: false
                    });
                }
            },

            error: function () {

                if ($.fn.DataTable.isDataTable("#contactsTable")) {

                    $("#contactsTable").DataTable().destroy();
                }

                $("#contactsTableBody").html(`
                    <tr>
                        <td
                            colspan="6"
                            class="text-center text-danger py-4"
                        >
                            Failed to load messages.
                        </td>
                    </tr>
                `);
            }
        });
    }

    loadContacts(contactFilters);

    $("#contactSearchInput").on(
        "keyup",
        function () {

            clearTimeout(contactSearchTimeout);

            const searchValue =
                $(this).val().trim();

            contactSearchTimeout = setTimeout(
                function () {

                    contactFilters.search =
                        searchValue;

                    loadContacts(contactFilters);

                },
                400
            );
        }
    );

    $("#resetContactFiltersBtn").on(
        "click",
        function () {

            contactFilters.search = "";
            $("#contactSearchInput").val("");

            loadContacts(contactFilters);
        }
    );
    
    $(document).on("click", ".view-message-btn", function() {
        const index = $(this).data("index");
        const msg = messagesData[index];
        
        if (msg) {
            $("#modalContactName").text(msg.name);
            $("#modalContactEmail").text(msg.email);
            $("#modalContactSubject").text(msg.subject);
            $("#modalContactDate").text(msg.created_at);
            $("#modalContactMessage").text(msg.message);
            
            viewMessageModal.show();
        }
    });

});

</script>
';

require_once __DIR__ . '/../../layouts/AdminLayout.php';
