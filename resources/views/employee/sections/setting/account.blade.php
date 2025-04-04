@extends('employee.layouts.app')

@section('panel-header')
<div>
    <h1 class="panel-title">Account Information</h1>
    <ul class="breadcrumb">
        <li><a href="{{route('employee.view.dashboard')}}">Team Member</a></li>
        <li><i data-feather="chevron-right"></i></li>
        <li><a href="{{route('employee.view.setting')}}">Setting</a></li>
        <li><i data-feather="chevron-right"></i></li>
        <li><a href="{{route('employee.view.account.setting')}}">Account Information</a></li>
    </ul>
</div>
@endsection

@section('panel-body')
<form action="{{route('employee.handle.account.information.update')}}" method="POST" enctype="multipart/form-data">
@csrf
<figure class="panel-card">
    <div class="panel-card-header">
        <div>
            <h1 class="panel-card-title">Update Information</h1>
            <p class="panel-card-description">Please fill the required fields</p>
        </div>
    </div>
    <div class="panel-card-body">
        <div class="grid md:grid-cols-4 sm:grid-cols-1 md:gap-7 sm:gap-5">

            {{-- Profile --}}
            <div class="md:col-span-4 sm:col-span-1">
                <div class="flex md:flex-row sm:flex-col items-center md:space-x-5">
                    <img src="{{ is_null(auth()->user('employee')->profile) ? asset('employee/images/default-profile.png') : asset('storage/'.auth()->user('employee')->profile) }}" id="profile" alt="profile" class="h-24 w-24 rounded-full border bg-white" />
                    <div class="input-group">
                        <label for="profile" class="input-label">Profile</label>
                        <input type="file" accept="image/jpeg, image/jpg, image/png" class="input-box-sm" name="profile" onchange="handleProfilePreview(event)" >
                        @error('profile')<span class="input-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- Name --}}
            <div class="input-group">   
                <label for="name" class="input-label">Your name</label>
                <input type="text" name="name" value="{{old('name',auth()->user('employee')->name)}}" class="input-box-md @error('name') input-invalid @enderror" placeholder="Enter name" required>
                @error('name')<span class="input-error">{{ $message }}</span>@enderror
            </div>

            {{-- Email --}}
            <div class="flex flex-col">
                <label for="email" class="input-label">Email address</label>
                <input type="email" name="email" value="{{old('email',auth()->user('employee')->email)}}" class="input-box-md @error('email') input-invalid @enderror" placeholder="Enter email" required>
                @error('email')<span class="input-error">{{ $message }}</span>@enderror
            </div>

            {{-- Phone --}}
            <div class="flex flex-col">
                <label for="phone" class="input-label">Phone</label>
                <input type="tel" name="phone" value="{{old('phone',auth()->user('employee')->phone)}}" class="input-box-md @error('phone') input-invalid @enderror" placeholder="Enter phone" required>
                @error('phone')<span class="input-error">{{ $message }}</span>@enderror
            </div>

            {{-- Account Password --}}
            <div class="flex flex-col">
                <label for="account_password" class="input-label">Account password</label>
                <input type="password" name="account_password" class="input-box-md @error('account_password') input-invalid @enderror" placeholder="Enter password" required>
                @error('account_password')<span class="input-error">{{ $message }}</span>@enderror
            </div>

        </div>
    </div>
    <div class="panel-card-footer">
        <button type="submit" class="btn-primary-md md:w-fit sm:w-full">Save Changes</button>
    </div>
</figure>
</form>

<form action="{{route('employee.handle.account.password.update')}}" method="POST">
@csrf
<figure class="panel-card">
    <div class="panel-card-header">
        <div>
            <h1 class="panel-card-title">Update Password</h1>
            <p class="panel-card-description">Please fill the required fields</p>
        </div>
    </div>
    <div class="panel-card-body">
        <div class="grid md:grid-cols-4 sm:grid-cols-1 md:gap-7 sm:gap-5">

            {{-- Current password --}}
            <div class="input-group">   
                <label for="current_password" class="input-label">Current password</label>
                <input type="password" name="current_password" class="input-box-md @error('current_password') input-invalid @enderror" placeholder="Enter password" required>
                @error('current_password')<span class="input-error">{{ $message }}</span>@enderror
            </div>

            {{-- New password --}}
            <div class="input-group">   
                <label for="password" class="input-label">New password</label>
                <input type="password" name="password" class="input-box-md @error('password') input-invalid @enderror" placeholder="Enter password" required>
                @error('password')<span class="input-error">{{ $message }}</span>@enderror
            </div>

            {{-- Confirm password --}}
            <div class="input-group">   
                <label for="password_confirmation" class="input-label">Confirm password</label>
                <input type="password" name="password_confirmation" class="input-box-md @error('password_confirmation') input-invalid @enderror" placeholder="Repeat password" required>
                @error('password_confirmation')<span class="input-error">{{ $message }}</span>@enderror
            </div>

        </div>
    </div>
    <div class="panel-card-footer">
        <button type="submit" class="btn-primary-md md:w-fit sm:w-full">Save Changes</button>
    </div>
</figure>
</form>
@endsection

@section('panel-script')
<script>
    document.getElementById('setting-tab').classList.add('active');

    const handleProfilePreview = (event) => {
        if (event.target.files.length == 0) {
            document.getElementById('profile').src = "{{ is_null(auth()->user('employee')->profile) ? asset('employee/images/default-profile.png') : asset('storage/'.auth()->user('employee')->profile) }}";
        }
        else {
            document.getElementById('profile').src = URL.createObjectURL(event.target.files[0])
        }
    }
</script>
@endsection