<x-app-layout>
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                @if(session('success'))
                <div class="alert bg-success" role="alert">
                    {{ session("success") }}
                </div>
                @endif @if(session('info'))
                <div class="alert bg-info" role="alert">
                    {{ session("info") }}
                </div>
                @endif @if(session('warning'))
                <div class="alert bg-warning" role="alert">
                    {{ session("warning") }}
                </div>
                @endif @if(session('danger'))
                <div class="alert bg-danger" role="alert">
                    {{ session("danger") }}
                </div>
                @endif
                <form class="d-grid gap-2" method="POST" action="{{ route('files.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="title">Masukkan Judul: </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control">
                        @error('title')
                        <span class="font-medium text-sm text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="file">File</label>
                        <input id="file" type="file" name="file" />
                        @error('file')
                        <span class="font-medium text-sm text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button id="submit" type="submit" class="btn btn-primary float-end">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('scripts')
    <script>
        FilePond.registerPlugin(FilePondPluginImagePreview);
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.create(document.querySelector('input[id="file"]'), {
            acceptedFileTypes: ['image/png', 'image/jpg', 'image/jpeg', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.oasis.opendocument.spreadsheet', 'video/mp4', 'video/avi', 'video/mov'],
        });
        FilePond.setOptions({
            server: {
                process: "{{ route('upload') }}",
                revert: "{{ route('revert') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            onaddfilestart(file) {
                document.getElementById('submit').setAttribute('disabled', true);
            },
            onprocessfile(file) {
                document.getElementById('submit').removeAttribute('disabled')
            },
            onremovefile(error, file) {
                document.getElementById('submit').setAttribute('disabled', true);
            }
        });
    </script>
    @endsection
</x-app-layout>
