<!-- dropzone -->
<div id="dropzone-uploads">
    Drop files here to upload...
</div>

<!-- previews -->
<div id="dropzone-uploads-previews"></div>

<!-- template -->
<div id="dropzone-uploads-template" class="d-none">
    <div class="dz-preview dz-file-preview"
         data-uploadable-class="{{ get_class($uploadable) }}"
         data-uploadable-id="{{ $uploadable->id }}"
    >
        <a class="dz-remove" data-dz-remove="">✖</a>

        <div class="dz-details">
            <div class="dz-details-image">
                <span><img class="img-fluid" data-dz-thumbnail></span>
            </div>

            <div class="dz-details-intent">
                <label>Intent</label>
                <select>
                    <option value=""></option>
                    @foreach(config('uploads.intents') as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="dz-details-filename">
                <strong>File</strong><span data-dz-name></span>
            </div>

            <div class="dz-details-size">
                <strong>Size</strong><span data-dz-size></span>
            </div>
        </div>

        <div class="dz-error-message">
            <span data-dz-errormessage></span>
        </div>

        <div class="dz-progress">
            <span class="dz-upload" data-dz-uploadprogress></span>
        </div>
    </div>
</div>

@push('js')
    <script>
        var uploads = {!! $uploadable->uploads->toJson() !!};

        var dropzone_uploads = new Dropzone('#dropzone-uploads', {
            url: '{{ route('upload.store') }}',
            // acceptedFiles: '.jpg, .jpeg, .png, .svg,',
            previewsContainer: '#dropzone-uploads-previews',
            previewTemplate: document.querySelector('#dropzone-uploads-template').innerHTML,
            thumbnailWidth: 130,
            thumbnailMethod: 'contain',
            headers: {
                "X-CSRF-TOKEN": '{{ csrf_token() }}',
            },

            // add the uploadable model and id to the form data
            sending: function (file, xhr, form_data)
            {
                form_data.append('uploadable_class', file.previewElement.dataset.uploadableClass);
                form_data.append('uploadable_id', file.previewElement.dataset.uploadableId);
            },

            // when file is removed from the list
            removedfile: function removedfile(file)
            {
                if (confirm('Delete ' + file.name + '?')) {
                    axios.delete(file.previewElement.dataset.updateUrl, {})
                        .then(function (response) {
                            // remove the preview element
                            if (file.previewElement != null && file.previewElement.parentNode != null) {
                                file.previewElement.parentNode.removeChild(file.previewElement);
                            }

                            oxy.$notify(response.data.notification);
                        })
                        .catch(function (error) {
                            console.log(error.response);
                            oxy.$notify(error.response.data.notification);
                        });
                }

                return this._updateMaxFilesReachedClass();
            },
        });

        // when error occurs
        dropzone_uploads.on('error', function (file, response)
        {
            oxy.$notify(response.notification);

            $(file.previewElement).find('.dz-error-message span').text(response.notification.text);
            $(file.previewElement).find('a, .dz-details-intent').hide();
        });

        // when a file was uploaded successfully
        dropzone_uploads.on('success', function (file, response)
        {
            file.previewElement.dataset.updateUrl = response.model.update_url;

            $(file.previewElement).find('select').on('change', changeIntent);

            oxy.$notify(response.notification)
        });

        // on page load if the uploadable has some uploads load them
        uploads.forEach(function (upload)
        {
            var file = {
                id: upload.id,
                name: upload.filename,
                size: upload.size,
                intent: upload.intent,

                // todo: generate actual thumbnails in the backend and swap the path
                thumbPath: upload.public_path
            };

            dropzone_uploads.emit('addedfile', file);
            dropzone_uploads.emit('thumbnail', file, file.thumbPath);
            dropzone_uploads.emit('complete', file);

            file.previewElement.dataset.updateUrl = upload.update_url;
            $(file.previewElement).find('select')[0].value = upload.intent;
        });
        
        // change intent
        $('.dz-details-intent select').on('change', changeIntent);

        function changeIntent() {
            let url = this.parentElement.parentElement.parentElement.dataset.updateUrl;

            axios.patch(url, {intent: this.value})
                .then(function (response) {
                    oxy.$notify(response.data.notification)
                })
                .catch(function (error) {
                    oxy.notify(error.response.data.message, 'error', 5000);
                })
        }
    </script>
@endpush