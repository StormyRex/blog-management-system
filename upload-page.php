<!DOCTYPE html>

<html>

<head>

    <title>Upload</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</head>

<body>

    <form id="uploadForm">

        <input
            type="file"
            name="image"
            required
        >

        <button type="submit">
            Upload
        </button>

    </form>

    <br>

    <div id="result"></div>

    <div id="uploads"></div>

    <script>

        function renderUploads(uploads) {

            $('#uploads').empty();

            if (!Array.isArray(uploads) || uploads.length === 0) {
                $('#uploads').html(
                    '<p>No uploads found.</p>'
                );
                return;
            }

            const html = uploads.map(function (upload) {
                const filename = upload.filename || '';
                const publicId = upload.public_id || '';
                const url = upload.url || '';

                return `
                    <div class="upload-card" data-id="${upload.id}">
                        <img
                            src="${url}"
                            width="200"
                        >
                        <p>Filename: ${filename}</p>
                        <p>Public ID: ${publicId}</p>
                        <button
                            class="delete-upload"
                            data-id="${upload.id}"
                        >
                            Delete
                        </button>
                    </div>
                `;
            }).join('');

            $('#uploads').html(html);
        }

        function loadUploads() {
            $.ajax({

                url: '/blog-management-system/api/uploads',

                type: 'GET',

                success: function (response) {

                    if (response && response.success) {
                        renderUploads(
                            response.data?.uploads
                        );
                        return;
                    }

                    $('#uploads').html(
                        '<p>Failed to load uploads.</p>'
                    );
                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                    $('#uploads').html(
                        '<p>Failed to load uploads.</p>'
                    );
                }

            });
        }

        $('#uploadForm').submit(function (e) {

            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({

                url: '/blog-management-system/api/upload',

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function (response) {

                    console.log(response);

                    if (!response || !response.data) {
                        $('#result').html(
                            `<p>${response?.message || 'Upload failed'}</p>`
                        );
                        return;
                    }

                    const message = response.message || 'Upload complete';
                    const dbError = response.data?.db_error
                        ? `<p>${response.data.db_error}</p>`
                        : '';

                    $('#result').html(

                        `
                        <p>${message}</p>
                        ${dbError}
                        `
                    );

                    loadUploads();
                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                }

            });

        });

        $(document).on(
            'click',
            '.delete-upload',
            function () {

                const id = $(this).data('id');

                if (!id) {
                    return;
                }

                if (!confirm('Delete this upload?')) {
                    return;
                }

                const button = $(this);

                $.ajax({

                    url: '/blog-management-system/api/upload/delete',

                    type: 'POST',

                    data: {
                        id: id
                    },

                    success: function (response) {

                        if (response && response.success) {
                            button
                                .closest('.upload-card')
                                .remove();
                            return;
                        }

                        alert(
                            response?.message
                            || 'Something went wrong'
                        );
                    },

                    error: function (xhr) {

                        console.log(xhr.responseText);
                        alert('Something went wrong');
                    }

                });

            }
        );

        loadUploads();

    </script>

</body>

</html>