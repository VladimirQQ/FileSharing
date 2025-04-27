@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Форма загрузки файлов -->
            <div class="card mb-4">
                <div class="card-header">Загрузка файла</div>
                <div class="card-body">
                    <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input class="form-control" type="file" name="file" required>
                            <button type="submit" class="btn btn-primary">Загрузить</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Список загруженных файлов -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Мои файлы</span>
                </div>
                <div class="card-body p-0">
                    @if($files->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Имя файла</th>
                                    <th>Размер</th>
                                    <th>Дата загрузки</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($files as $file)
                                <tr data-file-id="{{ $file->id }}" class="file-row">
                                    <td>{{ $file->original_name }}</td>
                                    <td>{{ formatSize($file->size) }}</td>
                                    <td>{{ $file->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="d-flex">
                                        <!-- Кнопка генерации ссылки -->
                                        <button class="btn btn-sm btn-outline-primary generate-link-btn me-2"
                                            data-file-id="{{ $file->id }}"
                                            title="Сгенерировать ссылку">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        
                                        <!-- Кнопка скачивания -->
                                        <a href="{{ route('files.download', $file->id) }}"
                                            class="btn btn-sm btn-outline-success me-2"
                                            title="Скачать">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        
                                        <!-- Кнопка удаления -->
                                        <form action="{{ route('files.delete', $file->id) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить"
                                                onclick="return confirm('Вы уверены, что хотите удалить этот файл?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <!-- Строка со ссылками (изначально скрыта) -->
                                <tr class="share-links-row" id="links-{{ $file->id }}" style="display: none;">
                                    <td colspan="4">
                                        <div class="p-3 bg-light">
                                            <h6 class="mb-3">Сгенерированные ссылки:</h6>
                                            @if($file->shareLinks->count() > 0)
                                            <div class="list-group">
                                                @foreach($file->shareLinks as $link)
                                                <div class="list-group-item mb-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong>Ссылка:</strong>
                                                            <a href="{{ route('files.download.link', $link->token) }}"
                                                                target="_blank" class="d-block text-truncate" style="max-width: 300px;">
                                                                {{ route('files.download.link', $link->token) }}
                                                            </a>
                                                        </div>
                                                        <span class="badge bg-{{ $link->is_used ? 'danger' : 'success' }}">
                                                            {{ $link->is_used ? 'Использована' : 'Активна' }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>Пароль:</strong>
                                                            <span>{{ $link->is_used ? '********' : 'Доступен только при генерации' }}</span>
                                                        </div>
                                                        <small class="text-muted">
                                                            Создано: {{ $link->created_at->format('d.m.Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <div class="alert alert-info mb-0">Нет сгенерированных ссылок</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <p class="text-muted">Нет загруженных файлов</p>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            Загрузить первый файл
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно -->
<div class="modal fade" id="linkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Одноразовая ссылка для скачивания</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ссылка:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="generatedLinkInput" readonly>
                        <button class="btn btn-outline-secondary copy-link-btn" type="button">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Пароль:</label>
                    <input type="text" class="form-control" id="generatedPassword" readonly>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Сохраните эти данные! После закрытия окна пароль будет недоступен.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary copy-link-btn">
                    <i class="fas fa-copy me-2"></i>Копировать ссылку
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Подтверждение удаления с более красивым диалогом
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm('Вы уверены, что хотите удалить этот файл? Все связанные ссылки также будут удалены.')) {
            this.submit();
        }
    });
});
</script>
@endpush

@php
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units)-1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
@endphp