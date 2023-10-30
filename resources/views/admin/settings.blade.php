@extends('admin.layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Settings</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            @include('admin.message')
            <form action="" method="POST" id="settingsForm" name="settingsForm">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input value="{{ $user->name }}" type="text" name="name" id="name"
                                        placeholder="Enter Your Name" class="form-control">
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email">Email</label>
                                    <input value="{{ $user->email }}" type="email" name="email" id="email"
                                        placeholder="Enter Your Email" class="form-control">
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Update Settings</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                </div>
            </form>
        </div>
    </section>
@endsection
@section('customJs')
    <script>
        $("#settingsForm").submit(function(event) {
            event.preventDefault();
            $("#submit").prop('disabled', true)
            $.ajax({
                url: '{{ route('admin.processChangeSettings') }}',
                type: 'post',
                data: $(this).serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("#submit").prop('disabled', false)
                    if (response.status == true) {
                        window.location.href = "{{ route('admin.dashboard') }}";
                    } else {
                        var errors = response.errors;
                        if (errors.name) {
                            $("#name").addClass('is-invalid').siblings("p").html(errors.name).addClass(
                                'invalid-feedback');
                        } else {
                            $("#name").removeClass('is-invalid').siblings("p").html('').removeClass(
                                'invalid-feedback');
                        }
                        if (errors.email) {
                            $("#email").addClass('is-invalid').siblings("p").html(errors.email)
                                .addClass('invalid-feedback');
                        } else {
                            $("#email").removeClass('is-invalid').siblings("p").html('').removeClass(
                                'invalid-feedback');
                        }
                    }

                },
                error: function(jqXHR, exception) {
                    console.log("Something went wrong")
                }
            });
        });
    </script>
@endsection
