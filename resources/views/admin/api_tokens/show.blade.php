@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">API Token for {{ $user->name }} ({{ $user->email }})</div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <strong>Token generated successfully!</strong><br>
                        <span class="text-danger">Copy and save this token now. You will not be able to see it again.</span>
                        <div class="mt-3">
                            <input type="text" class="form-control" value="{{ $token }}" readonly onclick="this.select();">
                        </div>
                    </div>
                    <a href="{{ route('admin.api-tokens.create') }}" class="btn btn-secondary mt-3">Create Another Token</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
