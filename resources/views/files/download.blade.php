@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Скачать файл: {{ $file->original_name ?? 'Неизвестный файл' }}</div>
                
                <div class="card-body">
                    @if(isset($error_message))
                        <div class="alert alert-danger">
                            {{ $error_message }}
                        </div>
                    @else
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('files.download.process', $token) }}" id="downloadForm">
                            @csrf
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль:</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Скачать файл</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('downloadForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: new URLSearchParams(new FormData(this))
            })
            .then(response => {
                if (response.ok) {
                    return response.blob();
                }
                return response.json().then(err => { throw err; });
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = '{{ $file->original_name ?? "file" }}';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                alert(error.message || 'Ошибка при скачивании');
            });
        });
    }
});
</script>
@endsection