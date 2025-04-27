<div>
    <h5>Ваши файлы</h5>
    <div v-if="files.length === 0" class="alert alert-info">
        Нет загруженных файлов.
    </div>
    <div v-else>
        <div v-for="file in files" :key="file.id" class="file-card card">
            <div class="card-body">
                <h6 class="card-title">@{{ file.original_name }}</h6>
                <p class="card-text">Size: @{{ formatSize(file.size) }}</p>
                <button @click="$emit('generate-link', file.id)" class="btn btn-sm btn-success">
                    Создать ссылку для обмена
                </button>
                
                <div v-if="file.share_links.length > 0" class="links-list">
                    <h6>Ссылка для обмена:</h6>
                    <ul>
                        <li v-for="link in file.share_links" :key="link.id">
                            <span>@{{ link.token }} - @{{ link.used ? 'Used' : 'Active' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
export default {
    props: ['files'],
    methods: {
        formatSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat(bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        }
    }
}
</script>