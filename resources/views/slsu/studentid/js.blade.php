<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    function measureInternetSpeed(callback) {
        var startTime, endTime;
        var image = new Image();
        var imageSize = 50000;

        image.onload = function() {
            endTime = new Date().getTime();
            var duration = (endTime - startTime) / 1000;
            var speed = imageSize / duration / 1024;
            callback(speed);
        };

        image.onerror = function() {
            callback(100);
        };

        startTime = new Date().getTime();
        image.src = "https://www.google.com/images/phd/px.gif?t=" + startTime;
    }

    function getLoadingDelay(speed) {
        if (speed > 500) return 500;
        if (speed > 200) return 1000;
        return 2000;
    }

    $('#search').on('input', function() {
        var searchQuery = $(this).val();

        $.ajax({
            url: "{{ route('student-list') }}",
            method: "GET",
            data: {
                search: searchQuery
            },
            beforeSend: function() {
                $("#loadingSpinner").show();
            },
            success: function(response) {
                $('#student-table').html(response);

                if (searchQuery) {
                    window.history.pushState(null, '', "{{ route('student-list') }}?search=" +
                        searchQuery);
                } else {
                    window.history.pushState(null, '', "{{ route('student-list') }}");
                }
            }
        });
    });

    $('#processButton').on('click', function(e) {
        e.preventDefault();

        var formData = new FormData($('#processIdForm')[0]);

        if (lastCroppedProfileSrc) {
            formData.append('croppedProfile', lastCroppedProfileSrc);
        }
        if (lastCroppedSignatureSrc) {
            formData.append('croppedSignature', lastCroppedSignatureSrc);
        }

        measureInternetSpeed(function(speed) {
            var delayTime = getLoadingDelay(speed);

            $("#loadingSpinner").fadeIn();

            setTimeout(() => {
                $.ajax({
                    url: "{{ route('update') }}",
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#loadingSpinner").show();
                    },
                    success: function(response) {
                        if (response.success) {
                            let redirectUrl =
                                "{{ route('print-preview', ['stuid' => '__STUDENT_NO__']) }}"
                                .replace('__STUDENT_NO__', response
                                    .encryptedStudentNo);
                            window.location.href = redirectUrl;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error: ", error);
                        Swal.fire({
                            icon: "error",
                            title: "Something went wrong",
                            text: xhr.responseText || error
                        });
                    },
                    complete: function() {
                        $("#loadingSpinner").fadeOut();
                    }
                });
            }, delayTime);
        });
    });

    $('#printButton').on('click', function(e) {
        e.preventDefault();

        var formData = new FormData($('#printForm')[0]);

        $.ajax({
            url: "{{ route('print') }}",
            method: "POST",
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Handle success response if needed
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: ", error);
                Swal.fire({
                    icon: "error",
                    title: "Something went wrong",
                    text: xhr.responseText || error
                });
            },
            complete: function() {
                $("#loadingSpinner").fadeOut();
            }
        });
    });
</script>
