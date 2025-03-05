<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"
    integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    $(document).ready(function() {
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
                url: "{{ route('employee-list') }}",
                method: "GET",
                data: {
                    search: searchQuery
                },
                success: function(response) {
                    $('#employee-table').html(response);

                    if (searchQuery) {
                        window.history.pushState(null, '',
                            "{{ route('employee-list') }}?search=" + searchQuery);
                    } else {
                        window.history.pushState(null, '', "{{ route('employee-list') }}");
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

            var selectedPosition = $('#position').val() || 'Default Position';

            $(".error-message").remove();

            measureInternetSpeed(function(speed) {
                var delayTime = getLoadingDelay(speed);

                $("#loadingSpinner").fadeIn();

                setTimeout(() => {
                    $.ajax({
                        url: "{{ route('update-employee') }}",
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
                                    "{{ route('emp_print-preview', ['emid' => '__EMPLOYEE_NO__']) }}"
                                    .replace('__EMPLOYEE_NO__', response
                                        .encryptedEmployeeID) +
                                    "&position=" + encodeURIComponent(
                                        selectedPosition);

                                window.location.href = redirectUrl;
                            }
                        },
                        error: function(xhr, status, error) {
                            $("#loadingSpinner").fadeOut();

                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                $.each(errors, function(key, messages) {
                                    let inputField = $(
                                        `[name="${key}"]`);
                                    inputField.after(
                                        `<span class="error-message text-danger">${messages[0]}</span>`
                                    );
                                });

                                swal({
                                    icon: "warning",
                                    title: "Validation Error",
                                    text: "Please check the highlighted fields and try again."
                                });

                            } else {
                                swal({
                                    icon: "error",
                                    title: "Something went wrong",
                                    text: xhr.responseText || error
                                });
                            }
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

            var formData = $('#printForm').serialize();

            swal({
                title: "Processing...",
                text: "Generating the print file. Please wait.",
                content: {
                    element: "div",
                    attributes: {
                        innerHTML: '<div style="text-align: center;"><i class="fa fa-spinner fa-spin" style="font-size:24px;"></i></div>'
                    }
                },
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false
            });

            setTimeout(function() {
                $.ajax({
                    url: "{{ route('emp_print') }}",
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            swal.close();

                            let iframe = document.createElement('iframe');
                            iframe.style.position = 'absolute';
                            iframe.style.width = '0px';
                            iframe.style.height = '0px';
                            iframe.style.border = 'none';
                            iframe.src = response.file_url;

                            document.body.appendChild(iframe);

                            iframe.onload = function() {
                                swal({
                                    title: "Ready to Print?",
                                    text: "Do you want to proceed with printing?",
                                    icon: "info",
                                    buttons: ["Cancel", "Yes, Print"]
                                }).then((willPrint) => {
                                    if (willPrint) {
                                        let printWindow = iframe
                                            .contentWindow;
                                        printWindow.focus();

                                        let beforePrint = function() {
                                            console.log(
                                                "Printing started..."
                                            );
                                        };

                                        let afterPrint = function() {
                                            swal("Printing Canceled",
                                                "You canceled the printing process.",
                                                "warning");
                                        };

                                        if ('matchMedia' in
                                            printWindow) {
                                            let mediaQueryList =
                                                printWindow
                                                .matchMedia('print');
                                            mediaQueryList
                                                .addEventListener(
                                                    'change',
                                                    function(mql) {
                                                        if (!mql
                                                            .matches) {
                                                            afterPrint
                                                                ();
                                                        }
                                                    });
                                        }

                                        printWindow.onafterprint =
                                            afterPrint;
                                        printWindow.onbeforeprint =
                                            beforePrint;

                                        printWindow.print();
                                    } else {
                                        iframe.remove();
                                        swal("Printing Canceled",
                                            "You canceled the printing process.",
                                            "warning");
                                    }
                                });
                            };
                        }
                    },
                    error: function(xhr, status, error) {
                        swal({
                            icon: "error",
                            title: "Something went wrong",
                            text: xhr.responseJSON ? xhr.responseJSON
                                .error : "An unexpected error occurred"
                        });
                    }
                });
            }, 3000);
        });
    });
</script>
