<x-app-layout>
    <div class="container">
        <div class="row">
            <div class="col-6 mx-auto">
                <form class="d-grid gap-2" method="POST" action="{{ route('files.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="title">Masukkan Judul: </label>
                        <input type="text" name="title" id="title" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="file">File</label>
                        <input id="file" type="file" name="file" />
                    </div>

                    <div class="form-group ">
                        <button type="submit" class="btn btn-primary float-end">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            // Get a reference to the file input element
            const inputElement = document.querySelector('input[id="file"]');
            // Create a FilePond instance
            const pond = FilePond.create(inputElement);
            FilePond.setOptions({
                server: {
                    process: "{{ route('upload') }}",
                    revert: "{{ route('revert') }}",
                    headers: {
                        'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                    },
                },
            });
        </script>
    @endsection
</x-app-layout>