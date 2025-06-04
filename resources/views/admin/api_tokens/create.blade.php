@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Create API Token</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.api-tokens.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="token_name" class="form-label">Token Name</label>
                            <input type="text" name="token_name" id="token_name" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Token</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
