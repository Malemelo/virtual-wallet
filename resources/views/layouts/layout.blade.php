<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <link href="{{ asset('images/favicon.png') }}" rel="icon" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ env('APP_NAME') }}</title>

    <!-- Web Fonts
============================================= -->
    <link rel='stylesheet'
        href='https://fonts.googleapis.com/css?family=Rubik:300,300i,400,400i,500,500i,700,700i,900,900i'
        type='text/css'>

    <!-- Stylesheet
============================================= -->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/font-awesome/css/all.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap-select/css/bootstrap-select.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/currency-flags/css/currency-flags.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/stylesheet.css') }}" />
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-flexdatalist/2.2.4/jquery.flexdatalist.min.css" />
</head>

<body>

    <!-- Preloader -->
    <div id="preloader">
        <div data-loader="dual-ring"></div>
    </div>
    <!-- Preloader End -->

    <!-- Document Wrapper
============================================= -->
    <div id="main-wrapper">

        @include('layouts.partials.header')

        @yield('content')

        <a id="back-to-top" data-toggle="tooltip" title="Back to Top" href="javascript:void(0)"><i
                class="fa fa-chevron-up"></i></a>

        <!-- Script -->
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('js/theme.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-flexdatalist/2.2.4/jquery.flexdatalist.min.js">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
        <script>
            @if(\Request::is('transactions/deposit'))

            function makePayment(amount) {
                FlutterwaveCheckout({
                    public_key: "{{ env('RAVE_TEST_PUBLIC_KEY') }}",
                    tx_ref: "{{ 'VW-'.mt_rand() }}",
                    amount: amount,
                    currency: "NGN",
                    redirect_url: "{{ url('transactions/status') }}",
                    customer: {
                        email: "{{ Auth::user()->email }}",
                        phone_number: "{{ Auth::user()->phone }}",
                        name: "{{ Auth::user()->first_name.' '.Auth::user()->last_name }}",
                    },
                    customizations: {
                        title: "{{ env('APP_NAME') }}",
                        description: "Wallet Deposit",
                        logo: "{{ asset('images/logo.png') }}",
                    },
                });
            }
            @endif

            $('document').ready(function () {
                @if(Session::has('success'))
                setTimeout(function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: "{{ Session::get('success') }}",
                        timer: 100000
                    }).then((value) => {}).catch(swal.noop)
                }, 3000);
                @endif

                @if(Session::has('fail'))
                setTimeout(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: "{{ Session::get('fail') }}",
                        timer: 50000
                    }).then((value) => {}).catch(swal.noop)
                }, 3000);
                @endif

                $('#deposit-amount').change(function () {
                    const amount = $('#deposit-amount').val();
                    if (amount >= 1000) {
                        $('#deposit-confirmation').html(amount +
                            " NGN");
                        $('#payment-btn').removeAttr('disabled');

                    } else {
                        $('#deposit-confirmation').html('');
                        $('#payment-btn').attr('disabled', 'disabled');
                    }
                });

                $('#payment-btn').click(function (e) {
                    e.preventDefault();

                    const amount = $('#deposit-amount').val();

                    makePayment(amount);
                });

                $('.flexdatalist').flexdatalist({
                    selectionRequired: true,
                    minLength: 1
                });

                $('#recipient-email').change(function () {
                    const recipient_email = $('#recipient-email').val();
                    const amount = $('#amount').val();
                    if (recipient_email == '' || amount < 1000) {

                        $('#transfer-confirm-btn').attr('disabled', 'disabled');
                    }
                });

                $('#transfer-amount').change(function () {
                    const amount = $('#transfer-amount').val();
                    if (amount >= 1000) {

                        $('#transfer-confirm-btn').removeAttr('disabled');

                    } else {


                        $('#transfer-confirm-btn').attr('disabled', 'disabled');

                    }
                });

                $('#confirm-transfer-btn').click(function (e) {

                    e.preventDefault();

                    const recipient = $('#recipient').val();
                    const recipient_uuid = $('#recipient_uuid').val();
                    const amount = $('#amount').val();
                    const narration = $('#narration').val();

                    console.log(amount + " " + narration);

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ url("transactions/transfer-money") }}',
                        type: 'POST',
                        data: {
                            recipient: recipient,
                            recipient_uuid: recipient_uuid,
                            amount: amount,
                            narration: narration
                        },
                        beforeSend: function () {
                            Swal.fire({
                                title: 'Processing Transfer',
                                onBeforeOpen: () => {
                                    Swal.showLoading()
                                },
                            });
                        },
                        success: function (data) {
                            //stuff
                            Swal.close();

                            if (data.status == 1) {
                                setTimeout(function () {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: data.msg,
                                        timer: 100000
                                    }).then((value) => {}).catch(swal.noop)
                                }, 1000);

                                setTimeout(function () {
                                    window.location = '{{ url("/dashboard") }}';
                                }, 5000);

                            } else {
                                Swal.close()
                                setTimeout(function () {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops!',
                                        text: data.msg,
                                        timer: 50000
                                    }).then((value) => {}).catch(swal.noop)
                                }, 1000);

                                setTimeout(function () {
                                    window.location =
                                        '{{ url("/transactions/transfer") }}';
                                }, 5000);
                            }
                        },
                        error: function (xhr, status, error) {
                            //other stuff
                            Swal.close();
                            console.log(xhr.responseJSON.message);
                            setTimeout(function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error ' + xhr.status,
                                    text: xhr.responseJSON.message,
                                    timer: 50000
                                }).then((value) => {}).catch(swal.noop)
                            }, 1000);
                        }
                    });
                });

            });

        </script>
</body>

</html>
