<!DOCTYPE html>
<html>

<head>
    <title>Uploads Listing</title>

    <meta charset="UTF-8">

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
    >

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
</head>

<body>

    <h2>Uploads Listing</h2>

    <table
        id="uploadsTable"
        class="display"
        style="width:100%"
    >

        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Public ID</th>
                <th>Filename</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        </tbody>

    </table>

    <script>

        let uploadsTable;

        function loadUploads()
        {
            $.ajax({

                url: '/blog-management-system/api/uploads',

                type: 'GET',

                success: function(response)
                {
                    if (!response.success) {

                        alert(response.message);

                        return;
                    }

                    if ($.fn.DataTable.isDataTable('#uploadsTable')) {

                        uploadsTable.destroy();
                    }

                    let rows = '';

                    response.data.uploads.forEach(function(upload) {

                        rows += `
                            <tr id="upload-row-${upload.id}">

                                <td>
                                    ${upload.id}
                                </td>

                                <td>
                                    <img
                                        src="${upload.url}"
                                        width="120"
                                        style="border-radius:8px"
                                    >
                                </td>

                                <td>
                                    ${upload.public_id}
                                </td>

                                <td>
                                    ${upload.filename}
                                </td>

                                <td>
                                    <button
                                        class="delete-upload"
                                        data-id="${upload.id}"
                                        style="
                                            background:red;
                                            color:white;
                                            border:none;
                                            padding:8px 12px;
                                            cursor:pointer;
                                            border-radius:5px;
                                        "
                                    >
                                        Delete
                                    </button>
                                </td>

                            </tr>
                        `;
                    });

                    $('#uploadsTable tbody').html(rows);

                    uploadsTable = $('#uploadsTable').DataTable();
                },

                error: function(xhr)
                {
                    console.log(xhr.responseText);
                }
            });
        }

        loadUploads();

        $(document).on(
            'click',
            '.delete-upload',
            function()
        {
            const uploadId = $(this).data('id');

            if (!confirm('Are you sure you want to delete this upload?')) {
                return;
            }

            $.ajax({

                url: '/blog-management-system/api/upload/delete',

                type: 'POST',

                data: {
                    id: uploadId
                },

                success: function(response)
                {
                    if (!response.success) {

                        alert(response.message);

                        return;
                    }

                    alert(response.message);

                    uploadsTable
                        .row(
                            $(`#upload-row-${uploadId}`)
                        )
                        .remove()
                        .draw();
                },

                error: function(xhr)
                {
                    console.log(xhr.responseText);
                }
            });
        });

    </script>

</body>

</html>
