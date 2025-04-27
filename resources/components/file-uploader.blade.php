<div class="mb-4">
    <h5>Загрузить файл</h5>
    <form @submit.prevent="uploadFile" enctype="multipart/form-data">
        <div class="input-group">
            <input type="file" class="form-control" ref="fileInput" required>
            <button class="btn btn-primary" type="submit">Загрузить</button>
        </div>
    </form>
</div>

<script>
export default {
    emits: ['file-uploaded'],
    methods: {
        async uploadFile() {
            const file = this.$refs.fileInput.files[0];
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                await axios.post('/api/files', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                this.$emit('file-uploaded');
                this.$refs.fileInput.value = '';
            } catch (error) {
                console.error(error);
            }
        }
    }
}
</script>