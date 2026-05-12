<!DOCTYPE html>
<html>

<head>
    <title>Uploads Listing</title>

    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f9;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        .page {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            padding: 24px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        h2 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        #uploadForm {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin: 16px 0 24px;
        }

        #imageInput {
            padding: 6px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
        }

        #insertButton {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        #insertButton:hover {
            background: #1d4ed8;
        }

        .hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: -8px;
            margin-bottom: 16px;
        }

        table.dataTable thead th {
            background: #f3f4f6;
        }

        table.dataTable tbody td {
            vertical-align: middle;
        }

        .media-col {
            width: 300px;
        }

        .media-cell {
            min-width: 280px;
        }

        .media-preview {
            display: inline-block;
            max-width: 260px;
        }

        .media-preview img,
        .media-preview video {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
    </style>

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
    >

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
</head>

<body>

    <div class="page">
        <h2>Uploads Listing</h2>

        <form id="uploadForm" enctype="multipart/form-data">
            <input
                type="file"
                id="imageInput"
                name="image"
                accept="image/*,video/mp4,video/webm,video/quicktime"
            >
            <button type="submit" id="insertButton">
                Insert
            </button>
        </form>
        <div class="hint">
            Allowed: JPG, JPEG, PNG, WEBP, GIF, BMP, TIF, TIFF. Max 20MB.
        </div>

        <table
            id="uploadsTable"
            class="display"
            style="width:100%"
        >

        <thead>
            <tr>
                <th>ID</th>
                <th class="media-col">Media</th>
                <th>Public ID</th>
                <th>Filename</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        </tbody>

        </table>
    </div>

    <script>

        let uploadsTable;

        function loadUploads()
        {
            $.ajax({

                url: 'api/uploads',

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

                        const preview = upload.type === 'VIDEO'
                            ? `
                                <div class="media-preview">
                                    <video width="260" height="160" controls>
                                        <source src="${upload.url}">
                                    </video>
                                </div>
                            `
                            : `
                                <div class="media-preview">
                                    <img
                                        src="${upload.url}"
                                        width="160"
                                        alt="${upload.filename}"
                                    >
                                </div>
                            `;

                        rows += `
                            <tr id="upload-row-${upload.id}">

                                <td>
                                    ${upload.id}
                                </td>

                                <td class="media-cell">
                                    ${preview}
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

        $('#uploadForm').on('submit', function(event)
        {
            event.preventDefault();

            const fileInput = $('#imageInput')[0];

            if (!fileInput || !fileInput.files.length) {
                alert('Please choose an image to upload.');
                return;
            }

            const formData = new FormData();
            formData.append('image', fileInput.files[0]);

            $.ajax({

                url: 'api/upload',

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function(response)
                {
                    if (!response.success) {
                        alert(response.message);
                        return;
                    }

                    $('#imageInput').val('');
                    loadUploads();
                },

                error: function(xhr)
                {
                    console.log(xhr.responseText);
                }
            });
        });

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

                url: 'api/upload/delete',

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
