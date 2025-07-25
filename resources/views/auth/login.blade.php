
@extends('admin.layouts.auth')
@section('css')
    <style>
        .input-error {
            color: red;
            font-weight: 500;
        }
    </style>
@endsection
@section('auth-card')
    {{-- Login Card (Start) --}}
    <div class="fix-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-6 d-flex align-items-center justify-content-center p-4">

                           <img src="https://i.ibb.co/b58D3N04/Pngtree-a-colorful-3d-infographic-featuring-20547594.png"
                                alt="Image"
                                class="w-100"
                            >


                </div>
                <div class="col-lg-5 col-md-6">
                    <div class="card mb-0 h-auto">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <a href="{{ route('admin.view.login') }}"><img class="logo-auth" src="{{ asset('admin_new/images/logo-full.png') }}"
                                        alt=""></a>
                            </div>
                            @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('success'))
    <script>
        // If you're using Toastr.js
        toastr.success("{{ session('success') }}");
    </script>

    {{-- Or if you're using Bootstrap toast/alert --}}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

                            <h4 class="text-center mb-4">Sign in your account</h4>
                            <div class="basic-form">
                                <form method="POST" action="{{ url('login') }}" class="form-valide-with-icon needs-validation" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label class="text-label form-label required" for="email">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"> <i class="fa fa-user"></i> </span>
                                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" id="email" placeholder="Enter Email.." required>
                                            <div class="invalid-feedback">
                                                @error('email')
                                                 <span class="input-error">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-label form-label required" for="dlab-password">Password</label>
                                        <div class="input-group transparent-append">
                                            <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
                                            <input type="password" name="password" class="form-control @error('password') input-invalid @enderror" id="dlab-password" placeholder="Enter password..." required>
                                            <span class="input-group-text show-pass">
                                                <i class="fa fa-eye-slash"></i>
                                                <i class="fa fa-eye"></i>
                                            </span>
                                            <div class="invalid-feedback">
                                                @error('password')
                                                    <span class="input-error">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn me-2 btn-primary">Sign In</button>
                                    <div class="text-center mt-3">
                    <p>Don't have an account? <a href="{{ url('register') }}">Register</a></p>
                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Login Card (End) --}}
@endsection
